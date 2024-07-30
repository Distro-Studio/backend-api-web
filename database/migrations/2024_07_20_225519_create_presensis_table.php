<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->foreignId('jadwal_id')->constrained('jadwals');
            $table->timestamp('jam_masuk');
            $table->timestamp('jam_keluar')->nullable();
            $table->integer('durasi');
            $table->string('lat');
            $table->string('long');
            $table->string('latkeluar')->nullable();
            $table->string('longkeluar')->nullable();
            $table->foreignId('foto_masuk')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->foreignId('foto_keluar')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->foreignId('kategori_presensi_id')->constrained('kategori_presensis'); //1 = 'Tepat Waktu', 2. 'Terlambat', 3 = 'Cuti', 4 = 'Absen'
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
