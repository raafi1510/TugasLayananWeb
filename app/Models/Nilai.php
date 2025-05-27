<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $fillable = [
        'id_matkuliah',
        'id_kelas',
        'id_mahasiswa',
        'tipe_nilai',
        'nilai',
    ];
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }
}