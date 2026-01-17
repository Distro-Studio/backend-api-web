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
            $table->foreignId('gambar')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->foreignId('dokumen_eksternal')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->foreignId('dokumen_diklat_1')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('dokumen_diklat_2')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('dokumen_diklat_3')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('dokumen_diklat_4')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('dokumen_diklat_5')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->string('nama');
            $table->foreignId('kategori_diklat_id')->constrained('kategori_diklats'); // 1 = Internal 2 = Eksternal
            $table->foreignId('status_diklat_id')->constrained('status_diklats');
            $table->boolean('certificate_published')->nullable()->default(0); // 1 = Sudah publish 0 = Belum publish
            $table->foreignId('certificate_verified_by')->nullable()->constrained('users');
            $table->string('deskripsi');
            $table->integer('kuota')->nullable();
            $table->unsignedBigInteger('total_peserta')->nullable()->default(0);
            $table->text('skp')->nullable();
            $table->string('tgl_mulai'); // d-m-Y
            $table->string('tgl_selesai'); // d-m-Y
            $table->string('jam_mulai'); // H:i:s
            $table->string('jam_selesai'); // H:i:s
            $table->integer('durasi');
            $table->string('lokasi');
            $table->foreignId('verifikator_1')->nullable()->constrained('users');
            $table->foreignId('verifikator_2')->nullable()->constrained('users');
            $table->foreignId('verifikator_3')->nullable()->constrained('users');
            $table->text('alasan')->nullable();
            $table->boolean('is_whitelist')->default(0); // 1 = Pasti ikut diklat
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
