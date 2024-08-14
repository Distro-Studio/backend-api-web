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
            $table->foreignId('jenis_penilaian_id')->constrained('jenis_penilaians');
            $table->string('pertanyaan_jawaban');
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
