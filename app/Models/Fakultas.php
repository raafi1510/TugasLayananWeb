<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    public function prodis()
    {
        return $this->hasMany(Prodi::class);
    }
}