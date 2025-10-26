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
            ->when($request->get('tipe'),   fn($q, $v) => $q->where('tipe', $v))
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
            'tipe'               => ['nullable', Rule::in(['peraturan_desa', 'keputusan_kepala_desa'])],
            'jenis_dokumen'      => ['nullable', 'string', 'max:150'],
            'tentang'            => ['required', 'string'],
            'uraian_singkat'     => ['nullable', 'string'],
            'keterangan'         => ['nullable', 'string'],
            'file_upload'        => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $path = $request->file('file_upload')->store('documents', 'public');

        $doc = Document::create([
            'id_user' => $request->user()?->id ?? Auth::id(),
            'tipe' => $validated['tipe'] ?? 'peraturan_desa',
            'jenis_dokumen' => $validated['jenis_dokumen'] ?? null,
            'nomor_dokumen' => Document::generateNomorDokumen($validated['tipe'] ?? 'peraturan_desa'),
            'tentang' => $validated['tentang'],
            'uraian_singkat' => $validated['uraian_singkat'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'file_upload' => $path,
            'status' => 'Draft',
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
            'tipe'               => ['nullable', Rule::in(['peraturan_desa', 'keputusan_kepala_desa'])],
            'jenis_dokumen'      => ['nullable', 'string', 'max:150'],
            'tentang'            => ['required', 'string'],
            'uraian_singkat'     => ['nullable', 'string'],
            'keterangan'         => ['nullable', 'string'],
            'file_upload'        => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        if ($request->hasFile('file_upload')) {
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

        $document->update([
            'status' => 'Disetujui',
            'nomor_dokumen' => Document::generateNomorDokumen($document->tipe),
            'tanggal_ditetapkan' => now(),
        ]);

        $document->storeActivity('Dokumen disetujui oleh Kepala Desa');
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

        if ($document->tipe === 'peraturan_desa') {
            $document->update([
                'nomor_diundangkan' => Document::generateNomorDiundangkan('peraturan_desa'),
                'tanggal_diundangkan' => now(),
            ]);
            $document->storeActivity('Peraturan Desa diundangkan oleh Sekretaris Desa');
        }

        if ($document->tipe === 'keputusan_kepala_desa') {
            $document->update([
                'nomor_dan_tanggal_dilaporkan' => Document::generateNomorDiundangkan('keputusan_kepala_desa'),
            ]);
            $document->storeActivity('Keputusan Kepala Desa dilaporkan oleh Sekretaris Desa');
        }

        $document->update(['status' => 'Publish']);

        return response()->json(['message' => 'Dokumen berhasil dipublish.']);
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
        if (!in_array($document->status, ['Disetujui', 'Arsip'])) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return new DocumentResource($document);
    }

    // GET /api/public/documents/{document}/download
    public function downloadPublic(Document $document)
    {
        if (!in_array($document->status, ['Disetujui', 'Arsip']) || !$document->file_upload) {
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
            ->whereIn('status', ['Disetujui', 'Arsip'])
            ->orderBy('tanggal_diundangkan')
            ->get();

        return DocumentResource::collection($data)->additional([
            'meta' => ['tahun' => $tahun, 'count' => $data->count()]
        ]);
    }
}
