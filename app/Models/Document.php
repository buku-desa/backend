<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_dokumen';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'jenis_dokumen',
        'nomor_dokumen',
        'tanggal_ditetapkan',
        'tentang',
        'tanggal_diundangkan',
        'nomor_diundangkan',
        'keterangan',
        'file_upload',
        'ocr_metadata',
        'status',
        'id_user',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

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
}
