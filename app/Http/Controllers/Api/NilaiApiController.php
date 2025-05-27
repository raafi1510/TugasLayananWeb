<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\Nilai; // Import model Nilai
use App\Models\Matakuliah; // Import model Matakuliah

class NilaiApiController extends Controller
{
    /**
     * Mengambil data user yang berelasi dengan mahasiswa berdasarkan ID kelas.
     * Dapat dilengkapi dengan pencarian berdasarkan NIM atau nama.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $classId ID dari model Kelas
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersByClass(Request $request, $classId)
    {
        // Validasi input
        $request->validate([
            'classId' => 'required|integer|exists:kelas,id', // Pastikan classId ada di tabel kelas
            'search' => 'nullable|string', // Parameter pencarian opsional
            'search_by' => 'nullable|in:nim,name', // Parameter pencarian berdasarkan nim atau name
        ]);

        try {
            $kelas = Kelas::findOrFail($classId);

            $query = $kelas->mahasiswa()->with('user'); // Eager load relasi 'user' dari setiap Mahasiswa

            // Tambahkan filter pencarian jika ada
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

            // Format data untuk respons API
            $usersData = $mahasiswaInClass->map(function ($mahasiswa) {
                return [
                    'mahasiswa_id' => $mahasiswa->id,
                    'user_id' => $mahasiswa->user->id,
                    'name' => $mahasiswa->user->name,
                    'email' => $mahasiswa->user->email,
                    'nim' => $mahasiswa->nim,
                    'kelas_id' => $mahasiswa->kelas_id,
                    'kelas_name' => $mahasiswa->kelas->nama_kelas ?? null, // Asumsi ada kolom nama_kelas di tabel kelas
                ];
            });

            if ($usersData->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada mahasiswa ditemukan untuk kelas ini dengan kriteria yang diberikan.'
                ], 404);
            }

            return response()->json([
                'message' => 'Data user dan mahasiswa berhasil diambil berdasarkan kelas.',
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
     * Mencari dan memfilter data user yang berelasi dengan mahasiswa berdasarkan NIM atau nama.
     * Pencarian dilakukan di seluruh data, tidak terbatas pada kelas tertentu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterUsersAndMahasiswa(Request $request)
    {
        // Validasi input
        $request->validate([
            'search' => 'required|string', // Parameter pencarian wajib
            'search_by' => 'required|in:nim,name', // Parameter pencarian berdasarkan nim atau name wajib
        ]);

        $searchTerm = $request->input('search');
        $searchBy = $request->input('search_by');

        try {
            // Memulai query dari model Mahasiswa karena NIM ada di sana, dan eager load User dan Kelas
            $query = Mahasiswa::with('user', 'kelas');

            if ($searchBy === 'name') {
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            } elseif ($searchBy === 'nim') {
                $query->where('nim', 'like', '%' . $searchTerm . '%');
            }

            $filteredMahasiswa = $query->get();

            // Format data untuk respons API
            $usersData = $filteredMahasiswa->map(function ($mahasiswa) {
                return [
                    'mahasiswa_id' => $mahasiswa->id,
                    'user_id' => $mahasiswa->user->id,
                    'name' => $mahasiswa->user->name,
                    'email' => $mahasiswa->user->email,
                    'nim' => $mahasiswa->nim,
                    'kelas_id' => $mahasiswa->kelas_id,
                    'kelas_name' => $mahasiswa->kelas->nama_kelas ?? null,
                ];
            });

            if ($usersData->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada user/mahasiswa ditemukan dengan kriteria tersebut.'
                ], 404);
            }

            return response()->json([
                'message' => 'Data user/mahasiswa berhasil difilter.',
                'data' => $usersData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan nilai baru berdasarkan data yang diterima.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeNilai(Request $request)
    {
        // Validasi input sesuai dengan kolom di model Nilai Anda
        $request->validate([
            'id_matkuliah' => 'required|integer|exists:matakuliah,id',
            'id_kelas' => 'required|integer|exists:kelas,id',
            'id_mahasiswa' => 'required|integer|exists:mahasiswa,id',
            'tipe_nilai' => 'required|string', // Contoh: 'quiz', 'uts', 'uas', 'tugas'
            'nilai' => 'required|numeric|min:0|max:100', // Nilai harus angka antara 0-100
        ]);

        try {
            $mahasiswaId = $request->input('id_mahasiswa');
            $matkuliahId = $request->input('id_matkuliah');
            $kelasId = $request->input('id_kelas');
            $tipeNilai = $request->input('tipe_nilai');

            // Opsional: Cek apakah nilai untuk mahasiswa, matakuliah, kelas, dan tipe_nilai ini sudah ada
            // Anda bisa menambahkan unique constraint di database atau logika ini
            $existingNilai = Nilai::where('id_mahasiswa', $mahasiswaId)
                ->where('id_matkuliah', $matkuliahId)
                ->where('id_kelas', $kelasId)
                ->where('tipe_nilai', $tipeNilai)
                ->first();

            if ($existingNilai) {
                // Jika sudah ada, bisa diupdate atau dikembalikan pesan konflik
                $existingNilai->nilai = $request->input('nilai');
                $existingNilai->save();
                return response()->json([
                    'message' => 'Nilai untuk mahasiswa ini pada matakuliah, kelas, dan tipe nilai ini sudah ada dan berhasil diupdate.',
                    'data' => $existingNilai
                ], 200); // 200 OK untuk update
            }

            // Simpan nilai baru
            $nilai = Nilai::create([
                'id_matkuliah' => $matkuliahId,
                'id_kelas' => $kelasId,
                'id_mahasiswa' => $mahasiswaId,
                'tipe_nilai' => $tipeNilai,
                'nilai' => $request->input('nilai'),
            ]);

            return response()->json([
                'message' => 'Nilai berhasil disimpan.',
                'data' => $nilai
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan nilai: ' . $e->getMessage()
            ], 500);
        }
    }
}