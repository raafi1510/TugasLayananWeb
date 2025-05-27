<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\Matakuliah;
use App\Models\Presensi; // Pastikan model Presensi sudah ada
use App\Models\Nilai;    // Pastikan model Nilai sudah ada
use Illuminate\Support\Facades\DB; // Untuk query aggregate

class RekapNilaiApiController extends Controller
{
    /**
     * Menampilkan rekap nilai siswa berdasarkan mata kuliah dan kelas.
     * Dilengkapi dengan fitur pencarian berdasarkan NIM atau Nama Mahasiswa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRekapNilai(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'id_matkuliah' => 'required|integer|exists:matakuliah,id',
            'id_kelas' => 'required|integer|exists:kelas,id',
            'search' => 'nullable|string',
            'search_by' => 'nullable|in:nim,name',
        ]);

        $idMatkuliah = $request->input('id_matkuliah');
        $idKelas = $request->input('id_kelas');
        $searchTerm = $request->input('search');
        $searchBy = $request->input('search_by');

        try {
            // Dapatkan Matakuliah dan Kelas untuk informasi respons
            $matakuliah = Matakuliah::findOrFail($idMatkuliah);
            $kelas = Kelas::findOrFail($idKelas);

            // 2. Query Dasar Mahasiswa
            // Ambil semua mahasiswa yang terdaftar di kelas yang dipilih
            $queryMahasiswa = Mahasiswa::where('kelas_id', $idKelas)
                ->with('user'); // Eager load user untuk nama

            // 3. Tambahkan Filter Pencarian (NIM atau Nama)
            if ($searchTerm && $searchBy) {
                if ($searchBy === 'name') {
                    $queryMahasiswa->whereHas('user', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    });
                } elseif ($searchBy === 'nim') {
                    $queryMahasiswa->where('nim', 'like', '%' . $searchTerm . '%');
                }
            }

            $mahasiswaList = $queryMahasiswa->get();

            if ($mahasiswaList->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada mahasiswa ditemukan di kelas ini atau dengan kriteria pencarian yang diberikan.',
                    'data' => []
                ], 404);
            }

            $rekapData = [];
            foreach ($mahasiswaList as $mahasiswa) {
                $mahasiswaId = $mahasiswa->id;

                // 4. Hitung Persentase Presensi
                // Asumsi: Kita perlu tahu total pertemuan yang seharusnya untuk matkul ini
                // Ini bisa diambil dari tabel jadwal, atau dari total pertemuan yang tercatat 'hadir'
                // Untuk contoh ini, saya akan hitung total pertemuan yang tercatat (hadir/izin/sakit)
                $totalPertemuanTerdata = Presensi::where('id_mahasiswa', $mahasiswaId)
                    ->where('id_matkuliah', $idMatkuliah)
                    ->where('id_kelas', $idKelas) // Pastikan kelasnya sesuai
                    ->count();

                $totalHadir = Presensi::where('id_mahasiswa', $mahasiswaId)
                    ->where('id_matkuliah', $idMatkuliah)
                    ->where('id_kelas', $idKelas)
                    ->whereIn('status', ['hadir', 'izin', 'sakit']) // Asumsi izin/sakit juga dianggap hadir
                    ->count();

                $persentasePresensi = ($totalPertemuanTerdata > 0) ? ($totalHadir / $totalPertemuanTerdata) * 100 : 0;


                // 5. Ambil Nilai Tugas, UTS, UAS
                $nilaiTugas = Nilai::where('id_mahasiswa', $mahasiswaId)
                    ->where('id_matkuliah', $idMatkuliah)
                    ->where('id_kelas', $idKelas)
                    ->where('tipe_nilai', 'tugas')
                    ->avg('nilai'); // Ambil rata-rata jika ada banyak nilai tugas

                $nilaiUTS = Nilai::where('id_mahasiswa', $mahasiswaId)
                    ->where('id_matkuliah', $idMatkuliah)
                    ->where('id_kelas', $idKelas)
                    ->where('tipe_nilai', 'uts')
                    ->value('nilai'); // Ambil satu nilai jika hanya ada 1 UTS

                $nilaiUAS = Nilai::where('id_mahasiswa', $mahasiswaId)
                    ->where('id_matkuliah', $idMatkuliah)
                    ->where('id_kelas', $idKelas)
                    ->where('tipe_nilai', 'uas')
                    ->value('nilai'); // Ambil satu nilai jika hanya ada 1 UAS

                // Set nilai ke 0 jika null (belum ada data)
                $nilaiTugas = round($nilaiTugas ?? 0);
                $nilaiUTS = round($nilaiUTS ?? 0);
                $nilaiUAS = round($nilaiUAS ?? 0);


                // 6. Hitung Nilai Akhir (Contoh Pembobotan)
                // Anda bisa menyesuaikan pembobotan ini sesuai kebijakan penilaian
                $nilaiAkhir = ($nilaiTugas * 0.3) + ($nilaiUTS * 0.3) + ($nilaiUAS * 0.4); // Contoh: Tugas 30%, UTS 30%, UAS 40%
                $nilaiAkhir = round($nilaiAkhir); // Bulatkan nilai akhir

                $rekapData[] = [
                    'no' => count($rekapData) + 1, // Nomor urut
                    'nim' => $mahasiswa->nim,
                    'nama_mahasiswa' => $mahasiswa->user->name,
                    'presensi' => round($persentasePresensi) . '%',
                    'tugas' => $nilaiTugas,
                    'uts' => $nilaiUTS,
                    'uas' => $nilaiUAS,
                    'nilai_akhir' => $nilaiAkhir,
                ];
            }

            return response()->json([
                'message' => 'Rekap nilai berhasil diambil.',
                'matakuliah' => $matakuliah->nama_matkuliah, // Asumsi nama kolom di Matakuliah adalah 'nama_matkuliah'
                'kelas' => $kelas->nama_kelas,
                'data' => $rekapData
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Mata kuliah atau Kelas tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}