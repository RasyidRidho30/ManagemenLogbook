<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiwa extends Model
{
    public $timestamps = false;
    protected $table = 'ms_mahasiswa';
    protected $primaryKey = 'mhs_id';
    protected $fillable = ['mhs_nim', 'mhs_nama'];
}
