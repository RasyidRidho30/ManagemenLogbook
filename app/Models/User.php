<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'usr_id';

    protected $fillable = [
        'usr_username',
        'usr_email',
        'usr_password',
        'usr_first_name',
        'usr_last_name',
        'usr_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute()
    {
        return trim(($this->usr_first_name ?? '') . ' ' . ($this->usr_last_name ?? ''));
    }

    public function getRoleAttribute()
    {
        return $this->usr_role;
    }

    public function getAvatarAttribute()
    {
        return $this->usr_avatar_url;
    }

    public function getAuthPassword()
    {
        return $this->usr_password;
    }
}
