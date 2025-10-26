<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    // GET /api/documents  (sekdes|kepdes)
    public function index(Request $request)
    {
        $docs = Document::query()
            ->when($request->get('status'), fn($q,$v)=>$q->where('status',$v))
            ->when($request->get('tipe'),   fn($q,$v)=>$q->where('tipe',$v))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return DocumentResource::collection($docs)
            ->additional(['meta' => ['page' => $docs->currentPage(), 'total' => $docs->total()]]);
    }

    // GET /api/documents/{document}  (sekdes|kepdes)
    public function show(Document $document)
    {
        return new DocumentResource($document);
    }

    // POST /api/documents  (sekdes)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe'               => ['nullable', Rule::in(['peraturan_desa','keputusan_kepala_desa'])],
            'jenis_dokumen'      => ['nullable','string','max:150'],
            'nomor_dokumen'      => ['nullable','string','max:150'],
            'tanggal_ditetapkan' => ['nullable','date'],
            'tentang'            => ['required','string'],
            'uraian_singkat'     => ['nullable','string'],
            'tanggal_dilaporkan' => ['nullable','date'],
            'keterangan'         => ['nullable','string'],
            'file_upload'        => ['required','file','mimes:pdf','max:20480'],
        ]);

        $path = $request->file('file_upload')->store('documents', 'public');

        $doc = Document::create([
            'id_user'             => $request->user()->id,
            'tipe'                => $validated['tipe'] ?? null,
            'jenis_dokumen'       => $validated['jenis_dokumen'] ?? null,
            'nomor_dokumen'       => $validated['nomor_dokumen'] ?? null,
            'tanggal_ditetapkan'  => $validated['tanggal_ditetapkan'] ?? null,
            'tentang'             => $validated['tentang'],
            'uraian_singkat'      => $validated['uraian_singkat'] ?? null,
            'tanggal_dilaporkan'  => $validated['tanggal_dilaporkan'] ?? null,
            'keterangan'          => $validated['keterangan'] ?? null,
            'file_upload'         => $path,
            'status'              => 'Draft',
        ]);

        return (new DocumentResource($doc))->response()->setStatusCode(201);
    }

    // PUT /api/documents/{document}  (sekdes)  — hanya Draft/Ditolak
    public function update(Request $request, Document $document)
    {
        if (!in_array($document->status, ['Draft','Ditolak'])) {
            return response()->json(['message' => 'Hanya Draft/Ditolak yang bisa diubah.'], 422);
        }

        $validated = $request->validate([
            'tipe'               => ['nullable', Rule::in(['peraturan_desa','keputusan_kepala_desa'])],
            'jenis_dokumen'      => ['nullable','string','max:150'],
            'nomor_dokumen'      => ['nullable','string','max:150'],
            'tanggal_ditetapkan' => ['nullable','date'],
            'tentang'            => ['required','string'],
            'uraian_singkat'     => ['nullable','string'],
            'tanggal_dilaporkan' => ['nullable','date'],
            'keterangan'         => ['nullable','string'],
            'file_upload'        => ['nullable','file','mimes:pdf','max:20480'],
        ]);

        if ($request->hasFile('file_upload')) {
            $document->file_upload = $request->file('file_upload')->store('documents', 'public');
        }

        $document->fill([
            'tipe'               => $validated['tipe']               ?? $document->tipe,
            'jenis_dokumen'      => $validated['jenis_dokumen']      ?? $document->jenis_dokumen,
            'nomor_dokumen'      => $validated['nomor_dokumen']      ?? $document->nomor_dokumen,
            'tanggal_ditetapkan' => $validated['tanggal_ditetapkan'] ?? $document->tanggal_ditetapkan,
            'tentang'            => $validated['tentang']            ?? $document->tentang,
            'uraian_singkat'     => $validated['uraian_singkat']     ?? $document->uraian_singkat,
            'tanggal_dilaporkan' => $validated['tanggal_dilaporkan'] ?? $document->tanggal_dilaporkan,
            'keterangan'         => $validated['keterangan']         ?? $document->keterangan,
        ])->save();

        return new DocumentResource($document);
    }

    // POST /api/documents/{document}/approve  (kepdes)
    public function approve(Request $request, Document $document)
    {
        if ($document->status === 'Arsip') {
            return response()->json(['message' => 'Dokumen arsip tidak bisa disetujui.'], 422);
        }
        $document->update(['status' => 'Disetujui']);
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
        return response()->json(['message' => 'Dokumen ditolak.']);
    }

    // POST /api/documents/{document}/publish  (sekdes) — status harus Disetujui
    public function publish(Request $request, Document $document)
    {
        if ($document->status !== 'Disetujui') {
            return response()->json(['message' => 'Hanya dokumen Disetujui yang dapat dipublish.'], 422);
        }

        $validated = $request->validate([
            'tanggal_diundangkan' => ['required','date'],
            'nomor_diundangkan'   => ['required','string','max:150'],
            
        ]);

        $document->update($validated);
        return response()->json(['message' => 'Dokumen diundangkan.']);
    }

    // GET /api/documents/{document}/download  (sekdes|kepdes)
    public function download(Request $request, Document $document)
    {
        if (!$document->file_upload) {
            return response()->json(['message' => 'File not found'], 404);
        }
        return Storage::disk('public')->download($document->file_upload);
    }

    // ===== PUBLIK (tanpa auth) =====

    // GET /api/public/documents/{document}
    public function showPublic(Document $document)
    {
        if (!in_array($document->status, ['Disetujui','Arsip'])) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return new DocumentResource($document);
    }

    // GET /api/public/documents/{document}/download
    public function downloadPublic(Document $document)
    {
        if (!in_array($document->status, ['Disetujui','Arsip']) || !$document->file_upload) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return Storage::disk('public')->download($document->file_upload);
    }

    // GET /api/laporan?tahun=YYYY  (sekdes|kepdes) — JSON rekap
    public function laporan(Request $request)
    {
        $tahun = (int) ($request->get('tahun') ?? now()->year);

        $data = Document::query()
            ->whereYear('tanggal_diundangkan', $tahun)
            ->whereIn('status', ['Disetujui','Arsip'])
            ->orderBy('tanggal_diundangkan')
            ->get();

        return DocumentResource::collection($data)->additional([
            'meta' => ['tahun' => $tahun, 'count' => $data->count()]
        ]);
    }
}
