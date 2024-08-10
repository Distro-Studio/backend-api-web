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
        Schema::create('diklats', function (Blueprint $table) {
            $table->id();
            $table->string('gambar')->nullable();
            $table->string('nama');
            $table->foreignId('kategori_diklat_id')->constrained('kategori_diklats');
            $table->foreignId('status_diklat_id')->constrained('status_diklats');
            $table->string('deskripsi');
            $table->integer('kuota');
            $table->string('tgl_mulai');
            $table->string('tgl_selesai');
            $table->string('jam_mulai');
            $table->string('jam_selesai');
            $table->integer('durasi');
            $table->string('lokasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diklats');
    }
};
