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
        'tipe',
        'jenis_dokumen',
        'nomor_dokumen',
        'tanggal_ditetapkan',
        'tentang',
        'uraian_singkat',
        'nomor_dan_tanggal_dilaporkan',
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

            if (empty($model->nomor_urut) && !empty($model->tipe)) {
                $lastNumber = self::where('tipe', $model->tipe)->max('nomor_urut');
                $model->nomor_urut = $lastNumber ? $lastNumber + 1 : 1;
            }
        });
    }

    public static function generateNomorDokumen($tipe)
    {
        $tahun = now()->year;
        $prefix = $tipe === 'peraturan_desa' ? 'PERDES' : 'KEPKADES';

        // PostgreSQL version
        $lastNomor = self::where('tipe', $tipe)
            ->whereYear('created_at', $tahun)
            ->whereNotNull('nomor_dokumen')
            ->max(DB::raw("CAST(split_part(nomor_dokumen, '/', 3) AS INTEGER)"));

        $nextNomor = ($lastNomor ?? 0) + 1;

        return sprintf('%s/%d/%03d', $prefix, $tahun, $nextNomor);
    }


    public static function generateNomorDiundangkan($tipe)
    {
        $tahun = now()->year;
        $prefix = $tipe === 'peraturan_desa' ? 'LD' : 'BD'; // Lembaran Desa / Berita Desa
        $count = self::where('tipe', $tipe)
            ->whereYear('tanggal_diundangkan', $tahun)
            ->count() + 1;

        return sprintf('%s/%d/%03d', $prefix, $tahun, $count);
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
