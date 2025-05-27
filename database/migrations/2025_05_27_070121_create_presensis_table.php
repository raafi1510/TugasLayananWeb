<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->integer('id_matakuliah');
            $table->integer('id_kelas');
            $table->integer('id_dosen');
            $table->integer('id_mahasiswa');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Tidak Hadir', 'Izin', 'Sakit']);
            $table->string('pertemuan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};