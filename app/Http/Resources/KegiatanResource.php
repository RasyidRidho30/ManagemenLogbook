<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KegiatanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->kgt_id ?? null,
            'modul_id' => $this->mdl_id ?? null,
            'nama' => $this->kgt_nama ?? null,
            'kode_prefix' => $this->kgt_kode_prefix ?? null,
            'tanggal_dibuat' => $this->kgt_create_at ?? null,
        ];
    }
}
