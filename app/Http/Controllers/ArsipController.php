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
            ->when($request->get('search'), function ($q, $v) {
                $q->where(function ($query) use ($v) {
                    $query->where('keterangan', 'LIKE', "%{$v}%")
                        ->orWhereHas('document', function ($docQuery) use ($v) {
                            $docQuery->where('tentang', 'LIKE', "%{$v}%")
                                ->orWhere('nomor_ditetapkan', 'LIKE', "%{$v}%")
                                ->orWhere('jenis_dokumen', 'LIKE', "%{$v}%");
                        });
                });
            })
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
            'id_dokumen' => ['required', 'uuid', 'exists:documents,id'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $doc = Document::findOrFail($validated['id_dokumen']);
        $oldStatus = $doc->status; // simpan sebelum berubah (Disetujui)

        try {
            $arsip = $doc->arsipkan(
                $request->user()->id,
                now(),
                $validated['keterangan'] ?? null
            );

            // Refresh instance biar statusnya benar-benar terbaru dari DB
            $doc->refresh();

            // Kirim event: dari Disetujui, Arsip
            event(new DocumentStatusChanged($doc, $oldStatus, $doc->status));

            return (new ArchiveResource($arsip->load('document')))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
