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
        Schema::create('penyesuaian_gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penggajian_id')->constrained('penggajians')->onDelete('cascade');
            $table->foreignId('kategori_gaji_id')->constrained('kategori_gajis'); // 1 = Penghasilan Dasar, 2 = Penambah, 3 = Pengurang
            $table->string('nama_detail');
            $table->integer('besaran');
            $table->string('bulan_mulai')->nullable();
            $table->string('bulan_selesai')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_gajis');
    }
};
