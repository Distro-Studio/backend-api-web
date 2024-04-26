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
            $table->date('tanggal');
            $table->foreignId('unit_kerja_from')->constrained('unit_kerjas');
            $table->foreignId('unit_kerja_to')->constrained('unit_kerjas');
            $table->foreignId('jabatan_from')->constrained('jabatans');
            $table->foreignId('jabatan_to')->constrained('jabatans');
            $table->string('tipe')->nullable();
            $table->text('alasan')->nullable();
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
