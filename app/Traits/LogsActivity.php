<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait LogsActivity
{
    public function logActivity(string $activity, ?string $documentId = null): void
    {
        try {
            ActivityLog::create([
                'id' => Str::uuid(),
                'id_user' => Auth::id(),
                'id_dokumen' => $documentId,
                'aktivitas' => $activity,
                'waktu_aktivitas' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}