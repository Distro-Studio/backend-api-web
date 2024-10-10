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
        Schema::create('about_hospitals', function (Blueprint $table) {
            $table->id();
            $table->text('konten');
            $table->foreignId('edited_by')->nullable()->constrained('users');
            $table->foreignId('about_hospital_1')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->foreignId('about_hospital_2')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->foreignId('about_hospital_3')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_hospitals');
    }
};
