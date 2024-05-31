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
            $table->string('durasi');
            $table->string('lat');
            $table->string('long');
            $table->string('foto');
            $table->string('absensi'); //(hadir,izin,sakit)
            $table->string('kategori');
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
