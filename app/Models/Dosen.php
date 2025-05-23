<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function mataKuliah()
    {
        return $this->hasMany(MataKuliah::class);
    }

}