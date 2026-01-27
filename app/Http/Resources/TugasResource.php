<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TugasResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->tgs_id ?? null,
            'id_kegiatan' => $this->kgt_id ?? null,
            'id_pic' => $this->usr_id ?? null,
            'nama_pic' => $this->pic_name ?? null,
            'kode' => $this->tgs_kode_prefix ?? null,
            'nama' => $this->tgs_nama ?? null,
            'tanggal_mulai' => $this->tgs_tanggal_mulai ?? null,
            'tanggal_selesai' => $this->tgs_tanggal_selesai ?? null,
            'bobot' => $this->tgs_bobot ?? null,
            'persentase_progress' => $this->tgs_persentasi_progress ?? null,
            'status' => $this->tgs_status ?? null,
            'tanggal_dibuat' => $this->tgs_create_at ?? null,
            'tanggal_diubah' => $this->tgs_modified_at ?? null,
        ];
    }
}
