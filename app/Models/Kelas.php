<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function mahasiswas()
    {
        return $this->belongsToMany(Mahasiswa::class); // Pivot: kelas_mahasiswa
    }

    public function mataKuliahs()
    {
        return $this->belongsToMany(MataKuliah::class); // Pivot: kelas_matakuliah
    }

}