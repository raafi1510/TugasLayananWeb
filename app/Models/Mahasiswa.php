<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class);
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    protected $fillable = [
        'user_id',
        'nim',
        'kelas_id',
        'prodi_id',
    ];
}