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
        Schema::create('tagihan_potongans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->foreignId('kategori_tagihan_id')->constrained('kategori_tagihan_potongans'); // 1 = Obat, 2 = Koperasi
            $table->foreignId('status_tagihan_id')->constrained('status_tagihan_potongans'); // 1 = Belum Tertagih, 2 = Tertagih, 3 = Terbayar
            // $table->integer('min_tagihan');
            $table->integer('besaran');
            $table->integer('tenor')->nullable();
            $table->integer('sisa_tenor')->nullable();
            $table->integer('sisa_tagihan')->nullable();
            $table->string('bulan_mulai')->nullable();
            $table->string('bulan_selesai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_potongans');
    }
};
