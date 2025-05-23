<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matakuliah extends Model
{
    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function kelases()
    {
        return $this->belongsToMany(Kelas::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }

    protected $fillable = [
        'kode',
        'nama',
        'sks',
        'dosen_id',
        'prodi_id'
    ];
}