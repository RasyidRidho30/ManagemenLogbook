<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjekResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->pjk_id ?? null,
            'nama' => $this->pjk_nama ?? null,
            'pic' => $this->pjk_pic ?? null,
            'deskripsi' => $this->pjk_deskripsi ?? null,
            'tanggal_mulai' => $this->pjk_tanggal_mulai ?? null,
            'tanggal_selesai' => $this->pjk_tanggal_selesai ?? null,
            'status' => $this->pjk_status ?? null,
            'persentase_progress' => $this->pjk_persentasi_progress ?? null,
            'tanggal_dibuat' => $this->pjk_create_at ?? null,
            'tanggal_diubah' => $this->pjk_modified_at ?? null,

            // Tambahan untuk card listing
            'creator_name' => $this->creator_name ?? null,
            'leader_name' => $this->leader_name ?? null,
            'pic_name' => $this->pic_name ?? null,
            'total_tasks' => $this->total_tasks ?? 0,
            'completed_tasks' => $this->completed_tasks ?? 0,
        ];
    }
}
