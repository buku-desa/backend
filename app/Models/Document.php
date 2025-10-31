<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // protected $casts = ['tanggal_ditetapkan'=>'date','tanggal_dilaporkan'=>'date','tanggal_diundangkan'=>'date'];

    protected $fillable = [
        // 'tipe',
        'jenis_dokumen',
        'nomor_ditetapkan',
        'tanggal_ditetapkan',
        'tentang',
        // 'uraian_singkat',
        // 'nomor_dan_tanggal_dilaporkan',
        'nomor_diundangkan',
        'tanggal_diundangkan',
        'keterangan',
        'file_upload',
        'status',
        'id_user',
    ];

    // relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // relasi ke activity log
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'id_dokumen');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }

            if (empty($model->nomor_urut) && !empty($model->jenis_dokumen)) {
                $lastNumber = self::where('jenis_dokumen', $model->jenis_dokumen)->max('nomor_urut');
                $model->nomor_urut = $lastNumber ? $lastNumber + 1 : 1;
            }
        });
    }


    // ==== Tambahkan/ubah helper penomoran diundangkan (INT) ====
    public static function nextNomorDiundangkan(string $jenis): int
    {
        $tahun = now()->year;

        // Ambil nomor maksimum di tahun & jenis yang sama lalu +1
        $max = self::where('jenis_dokumen', $jenis)
            ->whereYear('tanggal_diundangkan', $tahun)
            ->max('nomor_diundangkan');

        return (int)($max ?? 0) + 1;
    }

    // ==== (Opsional tapi sangat berguna) accessor tampilan nomor ====
    public function getNomorDiundangkanDisplayAttribute(): ?string
    {
        if (is_null($this->nomor_diundangkan) || is_null($this->tanggal_diundangkan)) {
            return null;
        }
        $year   = $this->tanggal_diundangkan->year;
        $prefix = $this->jenis_dokumen === 'peraturan_desa' ? 'LD' : 'BD'; // Lembaran vs Berita Desa
        return sprintf('%s/%d/%03d', $prefix, $year, $this->nomor_diundangkan);
    }


    protected $casts = [
        'tanggal_ditetapkan' => 'date',
        'tanggal_diundangkan' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // App\Models\Document.php
    public function arsipkan($userId, $tanggal_arsip = null, $keterangan = null)
    {
        // Pastikan statusnya sudah Disetujui sebelum bisa diarsipkan
        if ($this->status !== 'Disetujui') {
            throw new \Exception('Hanya dokumen Disetujui yang bisa diarsipkan.');
        }

        // Buat arsip baru otomatis
        $arsip = Archive::create([
            'id_dokumen'    => $this->id,
            'user_id'       => $userId,
            'tanggal_arsip' => $tanggal_arsip ?? now(),
            'keterangan'    => $keterangan ?? 'Arsip otomatis setelah disetujui.',
        ]);

        // Update status dokumen
        $this->update(['status' => 'Arsip']);

        // Catat aktivitas
        $this->storeActivity('diarsipkan oleh sekretaris desa');

        return $arsip;
    }
}
