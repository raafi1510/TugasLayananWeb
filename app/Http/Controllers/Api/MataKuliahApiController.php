<?php

namespace App\Http\Controllers\Api;
use App\Models\MataKuliah;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MataKuliahApiController extends Controller
{
    public function index()
    {
        $matakuliah = MataKuliah::with(['dosen', 'prodi'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List data mata kuliah',
            'data' => $matakuliah
        ]);
    }
}