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
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwals');
            $table->string('jam_masuk'); // Y-m-d H:i:s
            $table->string('jam_keluar')->nullable(); // Y-m-d H:i:s
            $table->integer('durasi')->nullable();
            $table->string('lat');
            $table->string('long');
            $table->string('latkeluar')->nullable();
            $table->string('longkeluar')->nullable();
            $table->foreignId('foto_masuk')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->foreignId('foto_keluar')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->foreignId('kategori_presensi_id')->constrained('kategori_presensis'); //1 = 'Tepat Waktu', 2. 'Terlambat', 3 = 'Cuti', 4 = 'Alfa'
            $table->string('note')->nullable();
            $table->boolean('is_pembatalan_reward')->default(0);
            $table->softDeletes();
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
