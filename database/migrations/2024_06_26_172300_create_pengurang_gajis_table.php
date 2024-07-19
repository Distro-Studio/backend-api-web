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
        Schema::create('pengurang_gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->foreignId('premi_id')->constrained('premis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengurang_gajis');
    }
};
