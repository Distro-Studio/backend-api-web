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
        Schema::create('run_thrs', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('thr_id')->constrained('thrs');
            // $table->foreignId('user_id')->constrained('users');
            $table->foreignId('riwayat_thr_id')->nullable()->constrained('riwayat_thrs');
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->string('tgl_run_thr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('run_thrs');
    }
};
