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
        Schema::create('template_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('sertfikat_id')->unique(); // cek duplikat ndak, nek ndak buat random aja
            $table->foreignId('diklat_id')->constrained('diklats');
            $table->foreignId('submitted_by')->constrained('users');
            $table->text('konten');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_certificates');
    }
};
