<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArchiveResource;
use App\Models\Archive;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArsipController extends Controller
{
    // GET /api/archives  (sekdes|kepdes)
    public function index(Request $request)
    {
        $list = Archive::with('document')
            ->latest('tanggal_arsip')
            ->paginate($request->integer('per_page', 15));

        return ArchiveResource::collection($list)
            ->additional(['meta' => ['page' => $list->currentPage(), 'total' => $list->total()]]);
    }

    // GET /api/archives/{archive}  (sekdes|kepdes)
    public function show(Archive $archive)
    {
        return new ArchiveResource($archive->load('document'));
    }

    // POST /api/archives  (sekdes) — hanya dokumen Disetujui
    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'user_id'       => ['required', 'uuid', 'exists:users,id'],
            'id_dokumen'    => ['required','uuid','exists:documents,id'],
            'nomor_arsip'   => ['nullable','string','max:150'],
            'tanggal_arsip' => ['required','date'],
            'keterangan'    => ['nullable','string'],
        ]);

        $doc = Document::findOrFail($validated['id_dokumen']);

        if ($doc->status !== 'Disetujui') {
            Log::info('Cek status gagal: '.$doc->status);
            return response()->json(['message' => 'Hanya dokumen Disetujui yang bisa diarsipkan.'], 422);
        }   

        $arsip = Archive::create([
            'id_dokumen'    => $doc->id,
            'user_id'       => $request->user()->id,
            'nomor_arsip'   => $validated['nomor_arsip'] ?? null,
            'tanggal_arsip' => $validated['tanggal_arsip'],
            'keterangan'    => $validated['keterangan'] ?? null,
        ]);

        $doc->update(['status' => 'Arsip']);

        return (new ArchiveResource($arsip->load('document')))->response()->setStatusCode(201);
    }
}
