<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModulResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->mdl_id ?? null,
            'projek_id' => $this->pjk_id ?? null,
            'nama' => $this->mdl_nama ?? null,
            'urut' => $this->mdl_urut ?? null,
        ];
    }
}
