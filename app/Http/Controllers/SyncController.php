<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SyncController extends Controller
{
    /**
     * Sinkronisasi data dummy dari modul Peraturan Desa & Keputusan Kepala Desa
     */
    public function syncDummy()
    {
        // Dummy data seolah dari API eksternal
        $dummyPerdes = [
            [
                'nomor_dokumen' => '001/PERDES/2024',
                'tanggal_ditetapkan' => '2024-06-12',
                'tentang' => 'Peraturan tentang Kebersihan Desa',
                'tanggal_diundangkan' => '2024-06-15',
                'nomor_diundangkan' => 'LD-2024-01',
                'keterangan' => 'Peraturan ini mengatur sistem kebersihan desa',
                'jenis_dokumen' => 'Peraturan Desa',
                'type' => 'peraturan_desa'
            ],
        ];

        $dummyKepdes = [
            [
                'nomor_dokumen' => '001/KEPDES/2024',
                'tanggal_ditetapkan' => '2024-07-02',
                'tentang' => 'Keputusan Kepala Desa Tentang Pengangkatan Kaur Keuangan',
                'tanggal_diundangkan' => '2024-07-05',
                'nomor_diundangkan' => 'BD-2024-01',
                'keterangan' => 'Keputusan tentang pengangkatan perangkat desa',
                'jenis_dokumen' => 'Keputusan Kepala Desa',
                'type' => 'keputusan_kepala_desa'
            ],
        ];

        // Gabung dua dummy sumber
        $combined = array_merge($dummyPerdes, $dummyKepdes);


        /**
         * ==========================================================
         * # REAL API CALL (aktifkan nanti saat API tim lain sudah siap)
         * ==========================================================
         *
         * Contoh penggunaan:
         *
         * // Ambil data dari modul Peraturan Desa
         * $responsePerdes = Http::get('https://api.peraturan-desa.local/api/peraturan');
         * $perdesData = $responsePerdes->json();
         *
         * // Ambil data dari modul Keputusan Kepala Desa
         * $responseKepdes = Http::get('https://api.keputusan-desa.local/api/keputusan');
         * $kepdesData = $responseKepdes->json();
         *
         * // Gabungkan hasil keduanya
         * $combined = array_merge($perdesData, $kepdesData);
         *
         * // Simpan atau update ke database
         * foreach ($combined as $item) {
         *     Document::updateOrCreate(
         *         ['nomor_dokumen' => $item['nomor_dokumen']],
         *         [
         *             'jenis_dokumen' => $item['jenis_dokumen'] ?? 'Tidak Diketahui',
         *             'tanggal_ditetapkan' => $item['tanggal_ditetapkan'] ?? null,
         *             'tentang' => $item['tentang'] ?? null,
         *             'tanggal_diundangkan' => $item['tanggal_diundangkan'] ?? null,
         *             'nomor_diundangkan' => $item['nomor_diundangkan'] ?? null,
         *             'keterangan' => $item['keterangan'] ?? null,
         *             'status' => 'Disetujui',
         *             'type' => $item['type'] ?? 'peraturan_desa'
         *         ]
         *     );
         * }
         *
         */


        //simpan
        foreach ($combined as $data) {
            Document::updateOrCreate(
                ['nomor_dokumen' => $data['nomor_dokumen']],
                [
                    'id_user' => Auth::id() ?? Str::uuid(),
                    'jenis_dokumen' => $data['jenis_dokumen'],
                    'nomor_dokumen' => $data['nomor_dokumen'],
                    'tanggal_ditetapkan' => $data['tanggal_ditetapkan'],
                    'tentang' => $data['tentang'],
                    'tanggal_diundangkan' => $data['tanggal_diundangkan'],
                    'nomor_diundangkan' => $data['nomor_diundangkan'],
                    'keterangan' => $data['keterangan'],
                    'file_upload' => null,
                    'status' => 'Disetujui',
                    'type' => $data['type']
                ]
            );
        }

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Dummy data sinkronisasi berhasil disimulasikan'
            ],
            'data' => [
                'total_synced' => count($combined),
            ]
        ]);
    }
}
