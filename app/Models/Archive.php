<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Archive extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // protected $casts = ['tanggal_arsip' => 'date'];

    protected $fillable = [
        'id_dokumen',
        'user_id',
        'nomor_arsip',
        'tanggal_arsip',
        'keterangan',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }

            $doc = $model->document ?? \App\Models\Document::find($model->id_dokumen);

            $prefix = match ($doc?->jenis_dokumen) {
                'peraturan_desa' => 'PD',
                'peraturan_kepala_desa' => 'KD',
                'peraturan_bersama_kepala_desa' => 'PBD',
                default => 'AR',
            };

            $count = self::whereYear('created_at', now()->year)->count() + 1;

            // 🔹 Format nomor arsip => contoh: PD/2025/0001
            $model->nomor_arsip = sprintf('%s/%d/%04d', $prefix, now()->year, $count);
        });
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'id_dokumen');
    }

    protected $casts = [
        'tanggal_arsip' => 'date',
    ];

    public static function generateNomorArsip()
    {
        $tahun = now()->year;
        $last = self::whereYear('created_at', $tahun)
            ->max(DB::raw("CAST(split_part(nomor_arsip, '/', 3) AS INTEGER)"));

        $next = ($last ?? 0) + 1;
        return sprintf('ARSIP/%d/%03d', $tahun, $next);
    }
}
