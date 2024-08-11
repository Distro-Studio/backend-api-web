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
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_dinilai')->constrained('users');
            $table->foreignId('user_penilai')->constrained('users');
            $table->foreignId('unit_kerja_dinilai')->constrained('unit_kerjas');
            $table->foreignId('unit_kerja_penilai')->constrained('unit_kerjas');
            $table->foreignId('jabatan_dinilai')->constrained('jabatans');
            $table->foreignId('jabatan_penilai')->constrained('jabatans');
            $table->integer('total_pertanyaan');
            $table->integer('rata_rata');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaians');
    }
};
