<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LogbookResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->lbk_id ?? null,
            'id_tugas' => $this->tgs_id ?? null,
            'tanggal' => $this->lbk_tanggal ?? null,
            'deskripsi' => $this->lbk_deskripsi ?? null,
            'komentar' => $this->lbk_komentar ?? null,
            'nama_pic' => $this->pic_name ?? null,
            'avatar_pic' => $this->usr_avatar_url ?? null,
            'peran_pic' => $this->usr_role ?? null,
            'tanggal_dibuat' => $this->lbk_create_at ?? null,
        ];
    }
}
