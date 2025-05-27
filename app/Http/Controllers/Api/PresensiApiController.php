<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\Presensi; // Import model Presensi
use App\Models\Matakuliah; // Asumsi model Matakuliah ada di App\Models
use App\Models\Dosen; // Asumsi model Dosen ada di App\Models

class PresensiApiController extends Controller
{
    /**
     * Mengambil data user berdasarkan kelas (dengan atau tanpa pencarian).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersByClass(Request $request, $classId)
    {
        // Validasi input
        $request->validate([
            'classId' => 'required|exists:kelas,id', // Pastikan classId ada di tabel kelas
            'search' => 'nullable|string', // Parameter pencarian opsional
            'search_by' => 'nullable|in:nim,name', // Parameter pencarian berdasarkan nim atau name
        ]);

        try {
            // Temukan kelas berdasarkan ID
            $kelas = Kelas::findOrFail($classId);

            // Ambil mahasiswa yang terkait dengan kelas tersebut
            $query = $kelas->mahasiswa()->with('user');

            // Tambahkan fitur pencarian jika parameter 'search' dan 'search_by' ada
            if ($request->has('search') && $request->has('search_by')) {
                $searchTerm = $request->input('search');
                $searchBy = $request->input('search_by');

                if ($searchBy === 'name') {
                    $query->whereHas('user', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    });
                } elseif ($searchBy === 'nim') {
                    $query->where('nim', 'like', '%' . $searchTerm . '%');
                }
            }

            $mahasiswaInClass = $query->get();

            // Filter data untuk mendapatkan informasi user yang berelasi
            $usersData = $mahasiswaInClass->map(function ($mahasiswa) {
                return [
                    'user_id' => $mahasiswa->user->id,
                    'name' => $mahasiswa->user->name,
                    'email' => $mahasiswa->user->email,
                    'nim' => $mahasiswa->nim,
                ];
            });

            return response()->json([
                'message' => 'Data user berdasarkan kelas berhasil diambil',
                'class_name' => $kelas->nama_kelas,
                'data' => $usersData
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kelas tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mencari dan memfilter user berdasarkan nama atau NIM (tanpa batasan kelas).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterUsers(Request $request)
    {
        // Validasi input
        $request->validate([
            'search' => 'required|string',
            'search_by' => 'required|in:nim,name',
        ]);

        $searchTerm = $request->input('search');
        $searchBy = $request->input('search_by');

        try {
            $query = Mahasiswa::with('user', 'kelas');

            if ($searchBy === 'name') {
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            } elseif ($searchBy === 'nim') {
                $query->where('nim', 'like', '%' . $searchTerm . '%');
            }

            $filteredMahasiswa = $query->get();

            $usersData = $filteredMahasiswa->map(function ($mahasiswa) {
                return [
                    'user_id' => $mahasiswa->user->id,
                    'name' => $mahasiswa->user->name,
                    'email' => $mahasiswa->user->email,
                    'nim' => $mahasiswa->nim,
                    'kelas_id' => $mahasiswa->kelas->id ?? null,
                    'kelas_name' => $mahasiswa->kelas->nama_kelas ?? null,
                ];
            });

            if ($usersData->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada user yang ditemukan dengan kriteria tersebut.'
                ], 404);
            }

            return response()->json([
                'message' => 'Data user berhasil difilter.',
                'data' => $usersData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan data presensi siswa berdasarkan model Presensi yang diberikan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePresensi(Request $request)
    {
        // Validasi input sesuai dengan kolom di model Presensi Anda
        $request->validate([
            'id_mahasiswa' => 'required|integer|exists:mahasiswa,id',
            'id_matkuliah' => 'required|integer|exists:matakuliah,id',
            'id_kelas' => 'required|integer|exists:kelas,id',
            'id_dosen' => 'required|integer|exists:dosen,id',
            // Asumsi status hanya bisa 'hadir', 'izin', 'sakit', 'alpa'
            'status' => 'required|string|in:hadir,izin,sakit,alpa',
            'pertemuan' => 'required|integer|min:1', // Asumsi pertemuan dimulai dari 1
            'tanggal' => 'nullable|date_format:Y-m-d', // Tanggal opsional, jika tidak ada pakai tanggal hari ini
        ]);

        try {
            $mahasiswaId = $request->input('id_mahasiswa');
            $matkuliahId = $request->input('id_matkuliah');
            $kelasId = $request->input('id_kelas');
            $dosenId = $request->input('id_dosen');
            $tanggal = $request->input('tanggal') ?? now()->toDateString(); // Gunakan tanggal dari request atau tanggal hari ini

            // Cek apakah presensi untuk mahasiswa, matkuliah, kelas, dosen, dan pertemuan ini sudah ada pada tanggal tertentu
            $existingPresensi = Presensi::where('id_mahasiswa', $mahasiswaId)
                ->where('id_matkuliah', $matkuliahId)
                ->where('id_kelas', $kelasId)
                ->where('id_dosen', $dosenId)
                ->where('pertemuan', $request->input('pertemuan'))
                ->whereDate('tanggal', $tanggal)
                ->first();

            if ($existingPresensi) {
                return response()->json([
                    'message' => 'Presensi untuk mahasiswa, matakuliah, kelas, dosen, dan pertemuan ini pada tanggal ' . $tanggal . ' sudah tercatat.',
                    'data' => $existingPresensi
                ], 409); // 409 Conflict
            }

            // Simpan data presensi baru
            $presensi = Presensi::create([
                'id_mahasiswa' => $mahasiswaId,
                'id_matkuliah' => $matkuliahId,
                'id_kelas' => $kelasId,
                'id_dosen' => $dosenId,
                'tanggal' => $tanggal,
                'status' => $request->input('status'),
                'pertemuan' => $request->input('pertemuan'),
            ]);

            return response()->json([
                'message' => 'Presensi berhasil dicatat.',
                'data' => $presensi
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mencatat presensi: ' . $e->getMessage()
            ], 500);
        }
    }
}