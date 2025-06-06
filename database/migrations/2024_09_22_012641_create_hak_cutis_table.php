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
        Schema::create('hak_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('tipe_cuti_id')->constrained('tipe_cutis')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('kuota'); // sisa kuota
            $table->integer('used_kuota')->default(0);
            $table->timestamp('last_reset')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hak_cutis');
    }
};
