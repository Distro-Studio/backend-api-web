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
        Schema::create('riwayat_pembatalan_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('tipe_pembatalan', ['cuti', 'presensi', 'izin']);
            $table->timestamp('tgl_pembatalan')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('cuti_id')->nullable()->constrained('cutis')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('presensi_id')->nullable()->constrained('presensis')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('riwayat_izin_id')->nullable()->constrained('riwayat_izins')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('verifikator_1')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('is_anulir_presensi')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pembatalan_rewards');
    }
};
