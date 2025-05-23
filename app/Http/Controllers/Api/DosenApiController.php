<?php

namespace App\Http\Controllers\Api;
use App\Models\Dosen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class DosenApiController extends Controller
{
    public function index()
    {
        $dosen = Dosen::with(['user', 'prodi'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List data dosen',
            'data' => $dosen
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nidn' => 'required|unique:dosens,nidn',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'fakultas_id' => 'required|exists:fakultas,id',
            'prodi_id' => 'required|exists:prodis,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buat akun User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'dosen',
        ]);

        // Buat data Dosen
        $dosen = Dosen::create([
            'user_id' => $user->id,
            'nidn' => $request->nidn,
            'fakultas_id' => $request->fakultas_id,
            'prodi_id' => $request->prodi_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dosen berhasil dibuat',
            'data' => [
                'user' => $user,
                'dosen' => $dosen
            ]
        ], 201);
    }

    public function destroy($id)
    {
        $dosen = Dosen::find($id);

        if (!$dosen) {
            return response()->json([
                'success' => false,
                'message' => 'Dosen tidak ditemukan'
            ], 404);
        }

        // Jika ingin hapus user yang terkait juga
        if ($dosen->user) {
            $dosen->user->delete();
        }

        $dosen->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dosen dan user terkait berhasil dihapus'
        ], 200);
    }

}