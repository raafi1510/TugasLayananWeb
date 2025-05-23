<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function dosens()
    {
        return $this->hasMany(Dosen::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

}