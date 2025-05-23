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

    public function search(Request $request)
    {
        $query = $request->input('query');

        $matakuliah = MataKuliah::where('nama', 'like', "%$query%")
            ->orWhere('kode', 'like', "%$query%")
            ->orWhere('sks', 'like', "%$query%")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $matakuliah
        ]);
    }

    public function update(Request $request, $id)
    {
        $matakuliah = MataKuliah::find($id);

        if (!$matakuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'kode' => 'sometimes|required|string|max:10|unique:mata_kuliahs,kode,' . $matakuliah->id,
            'sks' => 'sometimes|required|integer|min:1|max:6',
            'prodi_id' => 'sometimes|required|exists:prodis,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $matakuliah->update($request->only(['nama', 'kode', 'sks', 'prodi_id']));

        return response()->json([
            'success' => true,
            'message' => 'Mata kuliah berhasil diperbarui',
            'data' => $matakuliah
        ]);
    }

}