<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nim' => 'required|unique:mahasiswas,nim',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'kelas_id' => 'required|exists:kelas,id',
            'prodi_id' => 'required|exists:prodis,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // 1. Buat User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'mahasiswa',
        ]);

        // 2. Buat Mahasiswa
        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => $request->nim,
            'kelas_id' => $request->kelas_id,
            'prodi_id' => $request->prodi_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mahasiswa berhasil dibuat',
            'data' => [
                'user' => $user,
                'mahasiswa' => $mahasiswa
            ]
        ], 201);
    }

    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::find($id);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }

        // Jika ingin sekaligus hapus user yang terkait
        if ($mahasiswa->user) {
            $mahasiswa->user->delete();
        }

        $mahasiswa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mahasiswa dan user terkait berhasil dihapus'
        ], 200);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $mahasiswa = \App\Models\Mahasiswa::with('user', 'prodi')
            ->where('nama', 'like', "%$query%")
            ->orWhere('nim', 'like', "%$query%")
            ->orWhereHas('user', function ($q) use ($query) {
                $q->where('email', 'like', "%$query%");
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mahasiswa
        ]);
    }

    public function update(Request $request, $id)
    {
        $mahasiswa = \App\Models\Mahasiswa::find($id);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'nim' => 'sometimes|required|string|unique:mahasiswas,nim,' . $mahasiswa->id,
            'prodi_id' => 'sometimes|required|exists:prodis,id',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $mahasiswa->update($request->only(['nama', 'nim', 'prodi_id', 'user_id']));

        return response()->json([
            'success' => true,
            'message' => 'Data mahasiswa berhasil diperbarui',
            'data' => $mahasiswa
        ]);
    }

}