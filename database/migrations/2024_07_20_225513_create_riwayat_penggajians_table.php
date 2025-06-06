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
        Schema::create('riwayat_penggajians', function (Blueprint $table) {
            $table->id();
            $table->date('periode'); // Y-m-d
            $table->integer('karyawan_verifikasi');
            $table->boolean('jenis_riwayat'); // 0 = thr, 1 = non-thr
            $table->foreignId('status_gaji_id')->constrained('status_gajis'); // 1 = created 2 = published
            $table->integer('periode_gaji_karyawan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_penggajians');
    }
};
