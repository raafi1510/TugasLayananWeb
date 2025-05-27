<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $fillable = [
        'id_matkuliah',
        'id_kelas',
        'id_dosen',
        'id_mahasiswa',
        'tanggal',
        'status',
        'pertemuan',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function matakuliah()
    {
        return $this->belongsTo(Matakuliah::class, 'id_matkul');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}