<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use App\Events\DocumentStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;


class DocumentController extends Controller
{
    use LogsActivity;

    // GET /api/documents  (sekdes|kepdes)
    // add filter berdasarkan status, jenis_dokumen, search, tahun/tanggal start-end dan multi jenis, dan search buat jenis_dokumen
    public function index(Request $request)
    {
        $docs = Document::query()
            // 🔹 Filter status (bisa string atau array)
            ->when($request->filled('status'), function ($q) use ($request) {
                $statuses = (array) $request->get('status');
                $q->whereIn('status', $statuses);
            })

            // 🔹 Filter jenis_dokumen (bisa string atau array)
            ->when($request->filled('jenis_dokumen'), function ($q) use ($request) {
                $types = (array) $request->get('jenis_dokumen');
                $q->whereIn('jenis_dokumen', $types);
            })

            // 🔹 Filter pencarian umum (tentang, nomor, keterangan)
            // add for jenis_dokumen
            ->when($request->get('search'), function ($q, $v) {
                $q->where(function ($query) use ($v) {
                    $query->where('tentang', 'LIKE', "%{$v}%")
                        ->orWhere('nomor_ditetapkan', 'LIKE', "%{$v}%")
                        ->orWhere('nomor_diundangkan', 'LIKE', "%{$v}%")
                        ->orWhere('keterangan', 'LIKE', "%{$v}%")
                        ->orWhere('jenis_dokumen', 'LIKE', "%{$v}%");
                });
            })

            // 🔹 Filter tahun (berdasarkan tanggal_ditetapkan)
            ->when($request->filled('tahun'), function ($q) use ($request) {
                $q->whereYear('tanggal_ditetapkan', (int) $request->get('tahun'));
            })

            // 🔹 Filter rentang tanggal (start_date - end_date)
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $start = $request->get('start_date');
                $end   = $request->get('end_date');
                $q->whereBetween('tanggal_ditetapkan', [$start, $end]);
            })

            ->latest()
            ->paginate($request->integer('per_page', 15));

        return DocumentResource::collection($docs)
            ->additional([
                'meta' => [
                    'page' => $docs->currentPage(),
                    'total' => $docs->total(),
                    'filters' => [
                        'status'        => $request->get('status'),
                        'jenis_dokumen' => $request->get('jenis_dokumen'),
                        'tahun'         => $request->get('tahun'),
                        'start_date'    => $request->get('start_date'),
                        'end_date'      => $request->get('end_date'),
                        'search'        => $request->get('search'),
                    ]
                ]
            ]);
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
            'jenis_dokumen'        => ['required', Rule::in(['peraturan_desa', 'peraturan_kepala_desa', 'peraturan_bersama_kepala_desa'])],
            'nomor_ditetapkan'     => ['nullable', 'string', 'max:150'],
            'tanggal_ditetapkan'   => ['required', 'date'],
            'tentang'              => ['required', 'string'],
            'keterangan'           => ['nullable', 'string'],
            'file_upload'          => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $path = $request->file('file_upload')->store('documents', 'public');

        $doc = Document::create([
            'id_user'             => $request->user()?->id ?? Auth::id(),
            'jenis_dokumen'       => $validated['jenis_dokumen'] ?? 'peraturan_desa',
            'nomor_ditetapkan'    => $validated['nomor_ditetapkan'] ?? null,
            'tanggal_ditetapkan'  => $validated['tanggal_ditetapkan'],
            'tentang'             => $validated['tentang'],
            'keterangan'          => $validated['keterangan'] ?? null,
            'file_upload'         => $path,
            'status'              => 'Draft',
        ]);

        $doc->logActivity('dibuat oleh ' . ($request->user()?->name ?? 'Sistem'));

        //baru
        event(new DocumentStatusChanged($doc, null, 'Draft'));

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
            'nomor_ditetapkan'     => ['sometimes', 'string', 'max:150'],
            'tanggal_ditetapkan'   => ['sometimes', 'date'],
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

        $oldStatus = $document->status;

        // Jika sebelumnya Ditolak → ubah ke Draft lagi (artinya diajukan ulang)
        if ($oldStatus === 'Ditolak') {
            $validated['status'] = 'Draft';
        }

        $document->update($validated);

        $document->logActivity('diperbarui oleh ' . ($request->user()?->name ?? 'Sistem'));

        //baru
        if ($oldStatus === 'Ditolak' && $document->status === 'Draft') {
            event(new DocumentStatusChanged($document, 'Ditolak', 'Draft'));
        }

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

        if (empty($document->nomor_ditetapkan) || empty($document->tanggal_ditetapkan)) {
            return response()->json([
                'message' => 'Nomor ditetapkan dan tanggal ditetapkan harus diisi sebelum persetujuan.'
            ], 422);
        }

        return DB::transaction(function () use ($document) {
            $tahun = now()->year;

            // 🔒 Kunci logis per (jenis_dokumen, tahun) — aman untuk Postgres (INT4)
            // hashtext(?) => INT4; tahun => INT4
            DB::select('SELECT pg_advisory_xact_lock(hashtext(?), ?)', [
                $document->jenis_dokumen,
                (int) $tahun,
            ]);

            // Hitung nomor berikutnya untuk (jenis_dokumen, tahun) yang sama
            $max = DB::table('documents')
                ->where('jenis_dokumen', $document->jenis_dokumen)
                ->whereYear('tanggal_diundangkan', $tahun)
                ->max('nomor_diundangkan');

            $nextNumber = (int) ($max ?? 0) + 1;

            // Set nomor & tanggal diundangkan saat approve
            $document->update([
                'nomor_diundangkan'   => $nextNumber,
                'tanggal_diundangkan' => now(),
                'status'              => 'Disetujui',
            ]);

            $label = $document->jenis_dokumen === 'peraturan_desa' ? 'Lembaran Desa' : 'Berita Desa';
            $document->logActivity("Disetujui & diundangkan ke {$label} oleh Kepala Desa");

            return response()->json([
                'message' => 'Dokumen disetujui & diundangkan.',
                'data'    => [
                    'id'                        => $document->id,
                    'jenis_dokumen'             => $document->jenis_dokumen,
                    'nomor_diundangkan'         => $document->nomor_diundangkan,
                    'nomor_diundangkan_display' => $document->nomor_diundangkan_display, // e.g. LD/2025/005
                    'tanggal_diundangkan'       => $document->tanggal_diundangkan?->toDateString(),
                    'status'                    => $document->status, // Disetujui
                ]
            ]);
        });
    }




    // POST /api/documents/{document}/reject  (kepdes)
    public function reject(Request $request, Document $document)
    {
        if ($document->status === 'Arsip') {
            return response()->json(['message' => 'Dokumen arsip tidak bisa ditolak.'], 422);
        }
        $request->validate(['catatan' => 'nullable|string']);
        $document->update(['status' => 'Ditolak', 'keterangan' => $request->get('catatan')]);
        $document->logActivity('Dokumen ditolak oleh Kepala Desa');
        //baru
        event(new DocumentStatusChanged($document, $document->status, 'Ditolak'));

        return response()->json(['message' => 'Dokumen ditolak.']);
    }

    // PUT /api/documents/{document}/publish  (sekdes) — status harus Disetujui
    public function publish(Document $document)
    {
        return response()->json([
            'message' => 'Endpoint publish dinonaktifkan: dokumen akan otomatis diundangkan saat disetujui.'
        ], 410); // 410 Gone
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
        // by = week|month|year hanya untuk bantu pilih rentang cepat
        $by = strtolower($request->query('by', 'month'));
        if (!in_array($by, ['week', 'month', 'year'], true)) {
            return response()->json(['message' => 'Param "by" harus week|month|year'], 422);
        }

        // rentang default setahun penuh (berdasar "tahun")
        $year  = (int) ($request->query('tahun', now()->year));
        $start = $request->query('start')
            ? \Carbon\Carbon::parse($request->query('start'))->toDateString()
            : \Carbon\Carbon::create($year, 1, 1)->toDateString();

        $end   = $request->query('end')
            ? \Carbon\Carbon::parse($request->query('end'))->toDateString()
            : \Carbon\Carbon::create($year, 12, 31)->toDateString();

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        // filter status (default hanya yang sudah terbit & pasca-terbit)
        $statuses = $request->query('status', ['Disetujui', 'Arsip']);
        if (!is_array($statuses)) $statuses = [$statuses];

        // filter jenis_dokumen (tambahkan ini)
        $jenisDokumen = $request->query('jenis_dokumen', ['peraturan_desa', 'peraturan_kepala_desa', 'peraturan_bersama_kepala_desa']);
        if (!is_array($jenisDokumen)) $jenisDokumen = [$jenisDokumen];

        // Query dengan filter jenis_dokumen
        $items = Document::query()
            ->whereNotNull('tanggal_diundangkan')
            ->whereBetween(('tanggal_diundangkan'), [$start, $end])
            ->when($statuses, fn($q) => $q->whereIn('status', $statuses))
            ->when($jenisDokumen, fn($q) => $q->whereIn('jenis_dokumen', $jenisDokumen)) // ⬅️ TAMBAH INI
            ->orderBy('tanggal_diundangkan')
            ->orderBy('jenis_dokumen')
            ->orderBy('nomor_diundangkan')
            ->get([
                'id',
                'nomor_urut',
                'jenis_dokumen',
                'nomor_ditetapkan',
                'tanggal_ditetapkan',
                'tentang',
                'tanggal_diundangkan',
                'nomor_diundangkan',
                'keterangan',
                'status',
            ]);

        if ($request->query('format') === 'pdf') {
            $rows = $items->map(function ($d) {
                $jenisLabel = match ($d->jenis_dokumen) {
                    'peraturan_desa'                 => 'Peraturan Desa',
                    'peraturan_kepala_desa'          => 'Peraturan Kepala Desa',
                    'peraturan_bersama_kepala_desa'  => 'Peraturan Bersama Kepala Desa',
                    default => ucfirst(str_replace('_', ' ', (string)$d->jenis_dokumen)),
                };

                $noUndangDisp = $d->nomor_diundangkan && $d->tanggal_diundangkan
                    ? sprintf(
                        '%s/%d/%03d',
                        $d->jenis_dokumen === 'peraturan_desa' ? 'LD' : 'BD',
                        (int) \Carbon\Carbon::parse($d->tanggal_diundangkan)->format('Y'),
                        (int) $d->nomor_diundangkan
                    )
                    : null;

                return [
                    'nomor_urut'            => $d->nomor_urut,
                    'jenis_label'           => $jenisLabel,
                    'nomor_ditetapkan'      => $d->nomor_ditetapkan,
                    'tanggal_ditetapkan'    => $d->tanggal_ditetapkan ? \Carbon\Carbon::parse($d->tanggal_ditetapkan)->format('d-m-Y') : '',
                    'tentang'               => $d->tentang,
                    'tanggal_diundangkan'   => $d->tanggal_diundangkan ? \Carbon\Carbon::parse($d->tanggal_diundangkan)->format('d-m-Y') : '',
                    'nomor_diundangkan'     => $d->nomor_diundangkan,
                    'nomor_diundangkan_disp' => $noUndangDisp,
                    'keterangan'            => $d->keterangan ?? '-',
                ];
            });

            // pakai facade Pdf (pastikan importnya benar)
            $pdf = Pdf::loadView('reports.lembaran', [
                'rows'  => $rows,
                'start' => $start,
                'end'   => $end,
                'judul' => 'Buku Lembaran dan Berita Desa',
            ])->setPaper('A4', 'portrait');

            return $pdf->download("buku-lembaran-{$start}-{$end}.pdf");
        }

        // JSON preview (kalau butuh)
        return response()->json([
            'meta'  => [
                'basis'     => 'tanggal_diundangkan',
                'by'        => $by,
                'start'     => $start,
                'end'       => $end,
                'statuses'  => $statuses,
                'count'     => $items->count(),
            ],
            'items' => $items,
        ]);
    }
}
