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
            $table->foreignId('pendidikan_terakhir')->constrained('kategori_pendidikans');
            $table->boolean('status_hidup'); // 1 = hidup, 0 = meninggal
            $table->string('pekerjaan')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_bpjs')->default(1);
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
