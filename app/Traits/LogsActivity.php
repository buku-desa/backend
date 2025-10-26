<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    public function logActivity(string $activity): void
    {
        try {
            ActivityLog::create([
                'id' => Str::uuid(),
                'id_user' => Auth::id(),
                'id_dokumen' => $this->id ?? null,
                'aktivitas' => class_basename($this) . " {$activity}",
                'waktu_aktivitas' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    public function storeActivity(string $description): void
    {
        try {
            ActivityLog::create([
                'id' => Str::uuid(),
                'id_user' => Auth::id(),
                'id_dokumen' => $this->id ?? null,
                'aktivitas' => $description,
                'waktu_aktivitas' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log custom activity: ' . $e->getMessage());
        }
    }
}
