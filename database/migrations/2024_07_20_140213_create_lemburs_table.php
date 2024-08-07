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
        Schema::create('lemburs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('jadwal_id')->constrained('jadwals');
            $table->string('tgl_pengajuan');
            $table->foreignId('kompensasi_lembur_id')->constrained('kategori_kompensasis');
            // $table->string('tipe'); //! MASIH RANCU
            $table->string('durasi');
            $table->text('catatan');
            $table->foreignId('status_lembur_id')->constrained('status_lemburs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lemburs');
    }
};
