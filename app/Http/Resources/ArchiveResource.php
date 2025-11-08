<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArchiveResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'id_dokumen'     => $this->id_dokumen,
            'user_id'        => $this->user_id,
            'nomor_arsip'    => $this->nomor_arsip,
            'tanggal_arsip'  => optional($this->tanggal_arsip)->toDateString(),
            'keterangan'     => $this->keterangan,
            'created_at'     => $this->created_at?->toAtomString(),
            'updated_at'     => $this->updated_at?->toAtomString(),
            'document'       => $this->whenLoaded('document', function () {
                return [
                    'id'                => $this->document->id,
                    'jenis_dokumen'     => $this->document->jenis_dokumen,
                    'nomor_ditetapkan'  => $this->document->nomor_ditetapkan,
                    'tanggal_ditetapkan'=> $this->document->tanggal_ditetapkan?->format('Y-m-d'),
                    'tentang'           => $this->document->tentang,
                    'status'            => $this->document->status,
                    'file_url'          => $this->document->file_upload ? url('storage/'.$this->document->file_upload) : null,
                ];
            }),
        ];
    }
}
