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
        Schema::create('tukar_jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_pengajuan');
            $table->foreignId('user_ditukar');
            $table->foreignId('jadwal_pengajuan');
            $table->foreignId('jadwal_ditukar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tukar_jadwals');
    }
};
