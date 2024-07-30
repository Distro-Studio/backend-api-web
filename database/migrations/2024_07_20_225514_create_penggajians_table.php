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
        Schema::create('penggajians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riwayat_penggajian_id')->constrained('riwayat_penggajians');
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->timestamp('tgl_penggajian');
            $table->integer('gaji_pokok');
            $table->integer('total_tunjangan');
            $table->integer('reward');
            $table->integer('gaji_bruto');
            $table->integer('total_premi');
            $table->integer('pph_21');
            $table->integer('take_home_pay');
            $table->foreignId('status_gaji_id')->constrained('status_gajis'); // 1 = created 2 = published
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
        Schema::dropIfExists('penggajians');
    }
};
