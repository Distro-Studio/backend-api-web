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
        Schema::create('hak_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('tipe_cuti_id')->constrained('tipe_cutis')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('kuota');
            $table->integer('used_kuota')->default(0);
            // TODO: Tambahkan kolom used_kuota, untuk menampung penjumlahan panjangnya cuti yang sudah diajukan. Dan jangan lupa reset menjadi 0 ketika cronjob
            // TODO: Refactor untuk penambahkan used_kuota di function verifikasi 2 di cuti controller
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hak_cutis');
    }
};
