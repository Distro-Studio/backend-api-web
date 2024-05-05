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
            $table->date('tanggal');
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->string('gaji_pokok', 20);
            $table->string('total_tunjangan', 20);
            $table->string('reward',20)->default('0');
            $table->string('gaji_bruto', 20)->default('0');
            $table->string('total_premi',20)->default('0');
            $table->string('pph21', 20)->default('0');
            $table->string('take_home_pay', 20)->default('0');
            $table->string('status')->nullable();
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
