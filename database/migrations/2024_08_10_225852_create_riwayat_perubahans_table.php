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
        Schema::create('riwayat_perubahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->enum('jenis_perubahan', ['Keluarga', 'Personal']);
            $table->string('kolom');
            $table->string('original_data');
            $table->string('updated_data');
            $table->foreignId('status_perubahan_id')->constrained('status_perubahans'); // 1 = menunggu 2 = diverifikasi 3 = ditolak
            $table->foreignId('verifikator_1')->nullable()->constrained('users');
            $table->text('alasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahans');
    }
};
