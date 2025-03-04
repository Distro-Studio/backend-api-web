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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->foreignId('role_id')->nullable();
            $table->string('foto_profil')->nullable();
            $table->integer('data_completion_step')->default(1); // 1 = not complete, 0 = complete
            $table->foreignId('status_aktif')->constrained('status_aktifs'); // 1 = tidak aktif, 2 = aktif, 3 = dinonaktifkan

            // New Features
            $table->string('tgl_dinonaktifkan')->nullable();
            $table->text('alasan')->nullable();

            $table->rememberToken();
            $table->timestamp('remember_token_expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
