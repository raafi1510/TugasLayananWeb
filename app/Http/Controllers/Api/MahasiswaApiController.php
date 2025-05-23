<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mahasiswa;

class MahasiswaApiController extends Controller
{
    public function index()
    {
        $mahasiswa = Mahasiswa::with(['user', 'prodi'])->get(); // relasi user dan prodi

        return response()->json([
            'success' => true,
            'message' => 'List data mahasiswa',
            'data' => $mahasiswa
        ]);
    }
}