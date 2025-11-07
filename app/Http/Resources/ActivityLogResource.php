<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? 'Unknown User',
                'role' => $this->user->role ?? null,
            ],
            'document_id' => $this->id_dokumen,
            'aktivitas' => $this->aktivitas,
            'modul' => $this->modul,
            'keterangan' => $this->keterangan,
            'waktu_aktivitas' => date('Y-m-d H:i:s', strtotime($this->waktu_aktivitas)),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
