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
        Schema::create('riwayat_thrs', function (Blueprint $table) {
            $table->id();
            $table->date('periode'); // Y-m-d
            $table->foreignId('riwayat_penggajian_id')->nullable()->constrained('riwayat_penggajians');
            $table->integer('karyawan_thr');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_thrs');
    }
};
