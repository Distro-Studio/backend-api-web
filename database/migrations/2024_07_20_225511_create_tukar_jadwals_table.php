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
            $table->foreignId('user_pengajuan')->constrained('users');
            $table->foreignId('jadwal_pengajuan')->constrained('jadwals')->onDelete('cascade');
            $table->foreignId('user_ditukar')->constrained('users');
            $table->foreignId('jadwal_ditukar')->constrained('jadwals')->onDelete('cascade');
            $table->foreignId('status_penukaran_id')->constrained('status_tukar_jadwals');
            $table->foreignId('kategori_penukaran_id')->constrained('kategori_tukar_jadwals');
            $table->foreignId('verifikator_1')->nullable()->constrained('users');
            $table->foreignId('verifikator_2')->nullable()->constrained('users');
            $table->text('alasan')->nullable();
            $table->softDeletes();
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
