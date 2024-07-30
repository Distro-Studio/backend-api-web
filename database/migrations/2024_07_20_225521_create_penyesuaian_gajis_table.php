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
            $table->foreignId('penggajian_id')->constrained('penggajians');
            $table->foreignId('kategori_gaji_id')->constrained('kategori_gajis'); // 1 = pengurang, 2 = penambah
            $table->string('nama_detail');
            $table->integer('besaran');
            $table->date('bulan_mulai')->nullable();
            $table->date('bulan_selesai')->nullable();
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
