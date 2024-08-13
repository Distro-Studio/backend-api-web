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
            $table->string('tgl_mulai');
            $table->string('tgl_selesai');
            $table->foreignId('status_karyawan_id')->constrained('status_karyawans');
            $table->integer('lama_bekerja')->nullable();
            $table->integer('total_pertanyaan')->nullable();
            $table->integer('rata_rata')->nullable();
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
