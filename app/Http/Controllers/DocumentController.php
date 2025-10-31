<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    use LogsActivity;

    // GET /api/documents  (sekdes|kepdes)
    public function index(Request $request)
    {
        $docs = Document::query()
            ->when($request->get('status'), fn($q, $v) => $q->where('status', $v))
            ->when($request->get('jenis_dokumen'),   fn($q, $v) => $q->where('jenis_dokumen', $v))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return DocumentResource::collection($docs)
            ->additional(['meta' => ['page' => $docs->currentPage(), 'total' => $docs->total()]]);
    }

    // GET /api/documents/{document}  (sekdes|kepdes)
    public function show(Document $document)
    {
        $document = Document::with('user')->find($document->id);

        if (!$document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        return new DocumentResource($document);
    }

    // POST /api/documents  (sekdes)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_dokumen'        => ['nullable', Rule::in(['peraturan_desa','peraturan_kepala_desa', 'peraturan_bersama_kepala_desa' ])],
            'nomor_ditetapkan'     => ['required','string','max:150'],  
            'tanggal_ditetapkan'   => ['required','date'],            
            'tentang'              => ['required', 'string'],
            'keterangan'           => ['nullable', 'string'],
            'file_upload'          => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $path = $request->file('file_upload')->store('documents', 'public');

        $doc = Document::create([
            'id_user'             => $request->user()?->id ?? Auth::id(),
            'jenis_dokumen'       => $validated['jenis_dokumen'] ?? 'peraturan_desa',
            'nomor_ditetapkan'    => $validated['nomor_ditetapkan'],      
            'tanggal_ditetapkan'  => $validated['tanggal_ditetapkan'],   
            'tentang'             => $validated['tentang'],
            // 'uraian_singkat'      => $validated['uraian_singkat'] ?? null,
            'keterangan'          => $validated['keterangan'] ?? null,
            'file_upload'         => $path,
            'status'              => 'Draft',
        ]);

        $doc->logActivity('dibuat oleh ' . ($request->user()?->name ?? 'Sistem'));

        return (new DocumentResource($doc))->response()->setStatusCode(201);
    }


    // PUT /api/documents/{document}  (sekdes)  — hanya Draft/Ditolak
    public function update(Request $request, Document $document)
    {
        if (!in_array($document->status, ['Draft', 'Ditolak'])) {
            return response()->json(['message' => 'Hanya Draft/Ditolak yang bisa diubah.'], 422);
        }

        $validated = $request->validate([
            'jenis_dokumen'        => ['nullable', Rule::in(['peraturan_desa', 'peraturan_kepala_desa', 'peraturan_bersama_kepala_desa'])],
            'nomor_ditetapkan'     => ['sometimes','string','max:150'],  
            'tanggal_ditetapkan'   => ['sometimes','date'],               
            'tentang'              => ['sometimes', 'string'],
            'keterangan'           => ['nullable', 'string'],
            'file_upload'          => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        if ($request->hasFile('file_upload')) {
            // hapus file lama jika ada
            if ($document->file_upload && Storage::disk('public')->exists($document->file_upload)) {
                Storage::disk('public')->delete($document->file_upload);
            }
            $validated['file_upload'] = $request->file('file_upload')->store('documents', 'public');
        }

        $document->update($validated);

        $document->logActivity('diperbarui oleh ' . ($request->user()?->name ?? 'Sistem'));

        return new DocumentResource($document);
    }



    // DELETE /api/documents/{document}  (sekdes) — hanya Draft/Ditolak
    public function destroy(Document $document)
    {
        if (!in_array($document->status, ['Draft', 'Ditolak'])) {
            return response()->json(['message' => 'Hanya Draft/Ditolak yang bisa dihapus.'], 422);
        }

        if ($document->file_upload && Storage::disk('public')->exists($document->file_upload)) {
            Storage::disk('public')->delete($document->file_upload);
        }

        $document->delete();
        return response()->json(['message' => 'Dokumen dihapus.']);
    }

    // PUT /api/documents/{document}/approve  (kepdes)
    public function approve(Document $document)
    {
        if ($document->status !== 'Draft') {
            return response()->json(['message' => 'Hanya Draft yang bisa disetujui.'], 422);
        }

        // Pastikan field krusial sudah ada (kalau kamu buat required di store, cek ini opsional)
        if (empty($document->nomor_ditetapkan) || empty($document->tanggal_ditetapkan)) {
            return response()->json([
                'message' => 'Nomor ditetapkan dan tanggal ditetapkan harus diisi sebelum persetujuan.'
            ], 422);
        }

        $document->update([
            'status' => 'Disetujui',
        ]);

        $document->logActivity('Dokumen disetujui oleh Kepala Desa');
        return response()->json(['message' => 'Dokumen disetujui.']);
    }



    // POST /api/documents/{document}/reject  (kepdes)
    public function reject(Request $request, Document $document)
    {
        if ($document->status === 'Arsip') {
            return response()->json(['message' => 'Dokumen arsip tidak bisa ditolak.'], 422);
        }
        $request->validate(['catatan' => 'nullable|string']);
        $document->update(['status' => 'Ditolak', 'keterangan' => $request->get('catatan')]);
        $document->storeActivity('Dokumen ditolak oleh kepala desa');
        return response()->json(['message' => 'Dokumen ditolak.']);
    }

    // PUT /api/documents/{document}/publish  (sekdes) — status harus Disetujui
    public function publish(Document $document)
    {
        if ($document->status !== 'Disetujui') {
            return response()->json(['message' => 'Hanya dokumen Disetujui yang bisa dipublish.'], 422);
        }

        // Tentukan nomor_diundangkan (INT) berikutnya per (jenis_dokumen, tahun)
        $nextNumber = \App\Models\Document::nextNomorDiundangkan($document->jenis_dokumen);

        $document->update([
            'nomor_diundangkan'   => $nextNumber,      // INT sesuai skema
            'tanggal_diundangkan' => now(),            // tanggal publish
            'status'              => 'Publish',        // enum sudah mengizinkan Publish
        ]);

        // Catat activity yang informatif
        $label = $document->jenis_dokumen === 'peraturan_desa'
            ? 'Lembaran Desa'
            : 'Berita Desa'; // peraturan_kepala_desa / peraturan_bersama_kepala_desa

        $document->logActivity("Diundangkan ke {$label} oleh Sekretaris Desa");

        // (opsional) kirim juga nomor diundangkan display agar FE langsung bisa pakai
        return response()->json([
            'message' => 'Dokumen berhasil dipublish.',
            'data'    => [
                'id'                        => $document->id,
                'jenis_dokumen'             => $document->jenis_dokumen,
                'nomor_diundangkan'         => $document->nomor_diundangkan,             // integer murni
                'nomor_diundangkan_display' => $document->nomor_diundangkan_display,     // e.g. LD/2025/005
                'tanggal_diundangkan'       => $document->tanggal_diundangkan?->toDateString(),
                'status'                    => $document->status,
            ]
        ]);
    }

    // GET /api/documents/{document}/download  (sekdes|kepdes)
    public function download(Request $request, Document $document)
    {
        if (!$document->file_upload) {
            return response()->json(['message' => 'File not found'], 404);
        }
        $path = storage_path('app/public/' . $document->file_upload);
        return response()->download($path);
    }

    // ===== PUBLIK (tanpa auth) =====

    // GET /api/public/documents/{document}
    public function showPublic(Document $document)
    {
        if (!in_array($document->status, ['Publish', 'Arsip'])) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return new DocumentResource($document);
    }

    // GET /api/public/documents/{document}/download
    public function downloadPublic(Document $document)
    {
        if (!in_array($document->status, ['Publish', 'Arsip']) || !$document->file_upload) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $path = storage_path('app/public/' . $document->file_upload);
        return response()->download($path);
    }

    // GET /api/laporan?tahun=YYYY  (sekdes|kepdes) — JSON rekap
    public function laporan(Request $request)
    {
        $tahun = (int) ($request->get('tahun') ?? now()->year);

        $data = Document::query()
            ->whereYear('tanggal_diundangkan', $tahun)
            ->whereIn('status', ['Publish', 'Arsip'])
            ->orderBy('tanggal_diundangkan')
            ->get();

        return DocumentResource::collection($data)->additional([
            'meta' => ['tahun' => $tahun, 'count' => $data->count()]
        ]);
    }
}