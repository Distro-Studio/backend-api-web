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
            $table->string('jenis');
            $table->date('tgl_diklat');
            $table->string('tempat');
            $table->string('waktu');
            $table->string('penanggung_jawab');
            $table->string('peserta');
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
