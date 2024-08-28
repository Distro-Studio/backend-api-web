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
            $table->enum('hubungan', ['Ayah', 'Ibu', 'Anak', 'Suami', 'Istri', 'Nenek', 'Kakek', 'Ayah Suami', 'Ibu Suami', 'Ayah Istri', 'Ibu Istri']);
            $table->string('pendidikan_terakhir');
            $table->boolean('status_hidup'); // 1 = hidup, 0 = meninggal
            $table->string('pekerjaan')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
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
