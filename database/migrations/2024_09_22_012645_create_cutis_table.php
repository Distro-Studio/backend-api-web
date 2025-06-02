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
        Schema::create('cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('tipe_cuti_id')->constrained('tipe_cutis');
            $table->foreignId('hak_cuti_id')->nullable()->constrained('hak_cutis')->onUpdate('cascade')->onDelete('cascade');
            $table->text('keterangan')->nullable();
            $table->string('tgl_from'); // d-m-Y
            $table->string('tgl_to'); // d-m-Y
            $table->text('catatan')->nullable();
            $table->integer('durasi');
            $table->foreignId('status_cuti_id')->constrained('status_cutis');
            $table->foreignId('verifikator_1')->nullable()->constrained('users');
            $table->foreignId('verifikator_2')->nullable()->constrained('users');
            $table->text('alasan')->nullable();

            // New Updates
            $table->string('presensi_ids')->nullable();
            $table->string('jadwal_ids')->nullable();
            $table->string('izin_ids')->nullable();
            $table->string('lembur_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutis');
    }
};
