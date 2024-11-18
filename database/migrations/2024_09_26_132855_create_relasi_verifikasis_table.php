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
        Schema::create('relasi_verifikasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('verifikator')->constrained('users');
            $table->foreignId('modul_verifikasi')->constrained('modul_verifikasis');
            $table->integer('order'); // untuk verifikasi 1, verifikasi 2, dst (intinya untuk menyimpan urutan verifikasi mana yang harus dilakukan)
            $table->text('user_diverifikasi'); // target user yang datanya akan diverifikasi
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relasi_verfikasis');
    }
};
