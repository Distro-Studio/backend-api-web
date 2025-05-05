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
        Schema::create('perubahan_keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riwayat_perubahan_id')->constrained('riwayat_perubahans');
            $table->foreignId('data_keluarga_id')->nullable()->constrained('data_keluargas');
            $table->string('nama_keluarga');
            $table->enum('hubungan', ['Suami', 'Istri', 'Anak Ke-1', 'Anak Ke-2', 'Anak Ke-3', 'Anak Ke-4', 'Anak Ke-5', 'Bapak', 'Ibu', 'Bapak Mertua', 'Ibu Mertua']);
            $table->string('tgl_lahir');

            // Tambahan baru
            $table->string('tempat_lahir')->nullable();
            $table->boolean('jenis_kelamin')->nullable();
            $table->foreignId('kategori_agama_id')->nullable()->constrained('kategori_agamas');
            $table->foreignId('kategori_darah_id')->nullable()->constrained('kategori_darahs');
            $table->string('no_rm')->nullable();
            // Tambahan baru

            $table->foreignId('pendidikan_terakhir')->nullable()->constrained('kategori_pendidikans');
            $table->boolean('status_hidup');
            $table->string('pekerjaan')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('status_keluarga_id')->constrained('status_keluargas');
            $table->boolean('is_menikah')->default(0); // 1 = Sudah menikah 0 = Belum menikah
            $table->boolean('is_bpjs')->default(1); // 1 = Dapet BPJS 0 = Gak dapet BPJS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perubahan_keluargas');
    }
};
