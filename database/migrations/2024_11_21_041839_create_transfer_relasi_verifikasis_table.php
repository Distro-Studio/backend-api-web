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
        Schema::create('transfer_relasi_verifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_karyawan_id')->nullable()->constrained('transfer_karyawans');
            $table->integer('master_relasi_id')->nullable(); // jika is_created = 1, maka master_relasi_id akan diisi dengan null, jika is_created = 0, maka master_relasi_id akan diisi dengan id relasi verifikasi yang mau di edit
            $table->string('nama')->nullable();
            $table->foreignId('verifikator')->nullable()->constrained('users');
            $table->foreignId('modul_verifikasi')->nullable()->constrained('modul_verifikasis');
            $table->integer('order')->nullable(); // untuk verifikasi level
            $table->text('user_diverifikasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_relasi_verifikasis');
    }
};
