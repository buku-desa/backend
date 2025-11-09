<?php

namespace Database\Seeders;

use App\Models\Archive;
use App\Models\Document;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // === Ambil user dari UserSeeder ===
        $sekdes = User::where('email', 'sekdes1@example.com')->first();
        $kepdes = User::where('email', 'kepdes1@example.com')->first();

        $author = $sekdes ?? $kepdes ?? User::first();

        if (!$author) {
            $this->command?->warn('⚠️ Tidak ada user di DB. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        // === Helper untuk membuat dokumen ===
        $make = function (array $d) use ($author) {
            $defaults = [
                'id'                   => (string) Str::uuid(),
                'id_user'              => $d['id_user'] ?? $author->id,
                'jenis_dokumen'        => $d['jenis_dokumen'] ?? 'peraturan_desa',
                'nomor_ditetapkan'     => $d['nomor_ditetapkan'] ?? null,
                'tanggal_ditetapkan'   => $d['tanggal_ditetapkan'] ?? null,
                'tentang'              => $d['tentang'] ?? 'Tentang sesuatu',
                'nomor_diundangkan'    => $d['nomor_diundangkan'] ?? null,
                'tanggal_diundangkan'  => $d['tanggal_diundangkan'] ?? null,
                'keterangan'           => $d['keterangan'] ?? null,
                'file_upload'          => $d['file_upload'] ?? 'documents/sample.pdf',
                'status'               => $d['status'] ?? 'Draft',
                'created_at'           => $d['created_at'] ?? now(),
                'updated_at'           => $d['updated_at'] ?? now(),
            ];
            return Document::create($defaults);
        };

        // ========== DATA BERDASARKAN TANGGAL DIUNDANGKAN ========== //
        // 2021
        $make([
            'jenis_dokumen' => 'peraturan_desa',
            'tentang' => 'Perdes APBDes 2021',
            'status' => 'Disetujui',
            'nomor_diundangkan' => 1,
            'tanggal_diundangkan' => '2021-01-10',
            'nomor_ditetapkan' => '001/2021',
            'tanggal_ditetapkan' => '2021-01-05',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_desa',
            'tentang' => 'Perdes RTRW Desa 2021',
            'status' => 'Arsip',
            'nomor_diundangkan' => 2,
            'tanggal_diundangkan' => '2021-12-30',
            'nomor_ditetapkan' => '002/2021',
            'tanggal_ditetapkan' => '2021-12-28',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_kepala_desa',
            'tentang' => 'Edaran Internal 2021',
            'status' => 'Draft',
            'nomor_ditetapkan' => 'KEP/10/2021',
            'tanggal_ditetapkan' => '2021-11-15',
            'tanggal_diundangkan' => '2021-11-15',
        ]);

        // 2022
        $make([
            'jenis_dokumen' => 'peraturan_kepala_desa',
            'tentang' => 'Keputusan Pengangkatan Perangkat',
            'status' => 'Disetujui',
            'nomor_diundangkan' => 1,
            'tanggal_diundangkan' => '2022-02-02',
            'nomor_ditetapkan' => 'KEP/01/2022',
            'tanggal_ditetapkan' => '2022-02-01',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_bersama_kepala_desa',
            'tentang' => 'Kerja Sama Antar Desa',
            'status' => 'Ditolak',
            'tanggal_diundangkan' => '2022-06-30',
            'nomor_ditetapkan' => 'PB/02/2022',
            'tanggal_ditetapkan' => '2022-06-25',
            'keterangan' => 'Perlu revisi pasal 3',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_desa',
            'tentang' => 'Perdes Akhir Tahun 2022',
            'status' => 'Disetujui',
            'nomor_diundangkan' => 3,
            'tanggal_diundangkan' => '2022-12-31',
            'nomor_ditetapkan' => '003/2022',
        ]);

        // 2023
        $make([
            'jenis_dokumen' => 'peraturan_desa',
            'tentang' => 'Perdes Pajak Desa 2023',
            'status' => 'Disetujui',
            'nomor_diundangkan' => 1,
            'tanggal_diundangkan' => '2023-01-02',
            'nomor_ditetapkan' => '001/2023',
            'tanggal_ditetapkan' => '2023-01-01',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_kepala_desa',
            'tentang' => 'SK Panitia Pembangunan 2023',
            'status' => 'Draft',
            'tanggal_diundangkan' => '2023-07-16',
            'nomor_ditetapkan' => 'KEP/05/2023',
            'tanggal_ditetapkan' => '2023-07-15',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_bersama_kepala_desa',
            'tentang' => 'Perjanjian Air Bersih 2023',
            'status' => 'Arsip',
            'nomor_diundangkan' => 2,
            'tanggal_diundangkan' => '2023-12-10',
            'nomor_ditetapkan' => 'PB/09/2023',
            'tanggal_ditetapkan' => '2023-12-01',
        ]);
        // 2024
        $make([
            'jenis_dokumen' => 'peraturan_desa',
            'tentang' => 'Perdes BUMDes 2024',
            'status' => 'Disetujui',
            'nomor_diundangkan' => 1,
            'tanggal_diundangkan' => '2024-04-05',
            'nomor_ditetapkan' => '002/2024',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_desa',
            'tentang' => 'Perdes Keterbukaan Informasi 2024',
            'status' => 'Disetujui',
            'nomor_diundangkan' => 2,
            'tanggal_diundangkan' => '2024-07-01',
            'nomor_ditetapkan' => '003/2024',
            'tanggal_ditetapkan' => '2024-06-30',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_kepala_desa',
            'tentang' => 'SK Pengelola Sampah 2024',
            'status' => 'Ditolak',
            'tanggal_diundangkan' => '2024-12-31',
            'nomor_ditetapkan' => 'KEP/12/2024',
        ]);
        // 2025
        $make([
            'jenis_dokumen' => 'peraturan_desa',
            'tentang' => 'Perdes APBDes 2025',
            'status' => 'Disetujui',
            'nomor_diundangkan' => 1,
            'tanggal_diundangkan' => '2025-01-03',
            'nomor_ditetapkan' => '001/2025',
            'tanggal_ditetapkan' => '2025-01-02',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_desa',
            'tentang' => 'Perdes Tata Ruang 2025',
            'status' => 'Arsip',
            'nomor_diundangkan' => 2,
            'tanggal_diundangkan' => '2025-04-01',
            'nomor_ditetapkan' => '004/2025',
            'tanggal_ditetapkan' => '2025-03-31',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_kepala_desa',
            'tentang' => 'SK Panitia HUT RI 2025',
            'status' => 'Draft',
            'tanggal_diundangkan' => '2025-07-02',
            'nomor_ditetapkan' => 'KEP/07/2025',
        ]);
        $make([
            'jenis_dokumen' => 'peraturan_bersama_kepala_desa',
            'tentang' => 'Kerja Sama Layanan Kesehatan 2025',
            'status' => 'Disetujui',
            'nomor_diundangkan' => 3,
            'tanggal_diundangkan' => '2025-11-02',
            'nomor_ditetapkan' => 'PB/11/2025',
            'tanggal_ditetapkan' => '2025-11-01',
        ]);

        // === Buat arsip otomatis untuk status Arsip ===
        foreach (Document::where('status', 'Arsip')->get() as $doc) {
            Archive::firstOrCreate(
                ['id_dokumen' => $doc->id],
                [
                    'user_id'       => $author->id,
                    'tanggal_arsip' => $doc->tanggal_diundangkan ?? now(),
                    'keterangan'    => 'Arsip otomatis dari seeder',
                ]
            );
        }

        // === Buat activity log otomatis untuk semua dokumen ===
        $this->command?->info('🪵 Membuat activity log otomatis untuk semua dokumen...');

        foreach (Document::all() as $doc) {
            $aktivitas = match ($doc->status) {
                'Draft'     => 'Document created (draft)',
                'Disetujui' => 'Document approved',
                'Ditolak'   => 'Document rejected',
                'Arsip'     => 'Document archived',
                default     => 'Document created',
            };

            // Modul otomatis berdasarkan jenis dokumen
            $modul = match ($doc->jenis_dokumen) {
                'peraturan_desa'               => 'Lembaran Desa',
                'peraturan_kepala_desa'        => 'Berita Desa',
                'peraturan_bersama_kepala_desa' => 'Berita Desa',
                default                        => 'Dokumen',
            };

            // Keterangan otomatis (lebih manusiawi)
            $keterangan = match ($doc->status) {
                'Draft'     => "Dokumen '{$doc->tentang}' telah dibuat dan menunggu persetujuan.",
                'Disetujui' => "Dokumen '{$doc->tentang}' telah disetujui oleh Kepala Desa.",
                'Ditolak'   => "Dokumen '{$doc->tentang}' telah ditolak oleh Kepala Desa.",
                'Arsip'     => "Dokumen '{$doc->tentang}' telah diarsipkan.",
                default     => "Dokumen '{$doc->tentang}' dibuat oleh Sekdes.",
            };

            ActivityLog::create([
                'id' => (string) Str::uuid(),
                'id_user' => $doc->id_user,
                'id_dokumen' => $doc->id,
                'aktivitas' => $aktivitas,
                'modul' => $modul,
                'keterangan' => $keterangan,
                'waktu_aktivitas' => $doc->created_at ?? now(),
            ]);
        }
    }
}
