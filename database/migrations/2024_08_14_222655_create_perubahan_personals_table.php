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
        Schema::create('perubahan_personals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riwayat_perubahan_id')->constrained('riwayat_perubahans');
            $table->string('tempat_lahir');
            $table->string('tgl_lahir');
            $table->string('no_hp');
            $table->boolean('jenis_kelamin');
            $table->string('nik_ktp');
            $table->string('no_kk');
            $table->foreignId('kategori_agama_id')->constrained('kategori_agamas');
            $table->foreignId('kategori_darah_id')->constrained('kategori_darahs');
            $table->integer('tinggi_badan');
            $table->integer('berat_badan');
            $table->string('alamat');
            $table->string('no_ijasah');
            $table->integer('tahun_lulus');
            $table->foreignId('pendidikan_terakhir')->constrained('kategori_pendidikans');
            $table->string('gelar_depan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perubahan_personals');
    }
};
