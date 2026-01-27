<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Map DB column names to API field names (keep backward-compatible keys)
        return [
            'id' => $this->usr_id ?? $this->id ?? null,

            // username and raw name parts (compat)
            'nama_pengguna' => $this->usr_username ?? $this->username ?? null,
            'nama_depan' => $this->usr_first_name ?? $this->first_name ?? null,
            'nama_belakang' => $this->usr_last_name ?? $this->last_name ?? null,

            // preferred consumer-friendly fields
            'name' => $this->name ?? trim((($this->usr_first_name ?? '') . ' ' . ($this->usr_last_name ?? ''))),
            'username' => $this->usr_username ?? $this->username ?? null,
            'first_name' => $this->usr_first_name ?? $this->first_name ?? null,
            'last_name' => $this->usr_last_name ?? $this->last_name ?? null,

            'email' => $this->usr_email ?? $this->email ?? null,
            'role' => $this->usr_role ?? $this->role ?? null,
            'peran' => $this->usr_role ?? $this->role ?? null,

            'avatar' => $this->usr_avatar_url ?? $this->avatar ?? null,
        ];
    }
}
