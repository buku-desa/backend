<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            // 'tipe'                => $this->tipe,
            'nomor_urut'         => $this->nomor_urut,
            'jenis_dokumen'       => $this->jenis_dokumen,
            'nomor_ditetapkan'       => $this->nomor_ditetapkan,
            'tanggal_ditetapkan'  => optional($this->tanggal_ditetapkan)->toDateString(),
            'tentang'             => $this->tentang,
            // 'uraian_singkat'      => $this->uraian_singkat,
            // 'nomor_tanggal_dilaporkan'  => optional($this->nomor_tanggal_dilaporkan)->toDateString(),
            'tanggal_diundangkan' => optional($this->tanggal_diundangkan)->toDateString(),
            'nomor_diundangkan'   => $this->nomor_diundangkan,
            'keterangan'          => $this->keterangan,
            'file_upload'         => $this->file_upload, // path relatif di disk public
            'file_url'            => $this->file_upload ? url('storage/' . $this->file_upload) : null,
            'status'              => $this->status,
            'created_at'          => $this->created_at?->toAtomString(),
            'updated_at'          => $this->updated_at?->toAtomString(),
        ];
    }
}
