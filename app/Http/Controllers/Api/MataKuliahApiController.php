<?php

namespace App\Http\Controllers\Api;
use App\Models\MataKuliah;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
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


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|unique:mata_kuliahs,kode',
            'nama' => 'required|string|max:255',
            'sks' => 'required|integer|min:1|max:6',
            'dosen_id' => 'required|exists:dosens,id',
            'prodi_id' => 'required|exists:prodis,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $matakuliah = MataKuliah::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'sks' => $request->sks,
            'dosen_id' => $request->dosen_id,
            'prodi_id' => $request->prodi_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mata kuliah berhasil ditambahkan',
            'data' => $matakuliah
        ], 201);
    }

    public function destroy($id)
    {
        $matakuliah = MataKuliah::find($id);

        if (!$matakuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan'
            ], 404);
        }

        $matakuliah->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mata kuliah berhasil dihapus'
        ], 200);
    }

}