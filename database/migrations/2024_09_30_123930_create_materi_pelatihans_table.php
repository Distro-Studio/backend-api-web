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
        Schema::create('materi_pelatihans', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi');
            $table->foreignId('pj_materi')->constrained('users');
            $table->foreignId('dokumen_materi_1')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('dokumen_materi_2')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('dokumen_materi_3')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_pelatihans');
    }
};
