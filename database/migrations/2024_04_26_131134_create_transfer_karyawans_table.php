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
        Schema::create('transfer_karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('tanggal_mulai');
            $table->foreignId('unit_kerja_asal')->constrained('unit_kerjas');
            $table->foreignId('unit_kerja_tujuan')->constrained('unit_kerjas');
            $table->foreignId('jabatan_asal')->constrained('jabatans');
            $table->foreignId('jabatan_tujuan')->constrained('jabatans');
            $table->string('tipe');
            $table->text('alasan');
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_karyawans');
    }
};
