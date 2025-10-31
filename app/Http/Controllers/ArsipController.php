<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArchiveResource;
use App\Models\Archive;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\LogsActivity;
use App\Events\DocumentStatusChanged;

class ArsipController extends Controller
{
    use LogsActivity;

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
            'id_dokumen'    => ['required', 'uuid', 'exists:documents,id'],
            'keterangan'    => ['nullable', 'string'],
        ]);

        $doc = Document::findOrFail($validated['id_dokumen']);

        try {
            $arsip = $doc->arsipkan(
                $request->user()->id,
                now(), // $validated['tanggal_arsip'] ,
                $validated['keterangan'] ?? null
            );

            event(new DocumentStatusChanged($doc, 'Diarsipkan', $doc->status));

            return (new ArchiveResource($arsip->load('document')))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
