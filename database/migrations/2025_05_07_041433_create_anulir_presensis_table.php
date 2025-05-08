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
        Schema::create('anulir_presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->foreignId('presensi_id')->constrained('presensis');
            $table->foreignId('dokumen_anulir_id')->nullable()->constrained('berkas')->onDelete('cascade')->onUpdate('cascade');
            $table->text('alasan');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anulir_presensis');
    }
};
