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
        Schema::create('thrs', function (Blueprint $table) {
            $table->id();
            $table->string('perhitungan');
            $table->integer('nominal_satu')->nullable();
            $table->integer('nominal_dua')->nullable();
            $table->string('potongan')->nullable();
            $table->string('kriteria_karyawan_kontrak')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thrs');
    }
};
