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
        Schema::create('riwayat_izins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('tgl_izin'); // Y-m-d
            $table->string('waktu_izin'); // H:i:s
            $table->integer('durasi');
            $table->text('keterangan');
            $table->foreignId('status_izin_id')->nullable()->constrained('status_riwayat_izins');
            $table->foreignId('verifikator_1')->nullable()->constrained('users');
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
        Schema::dropIfExists('riwayat_izins');
    }
};
