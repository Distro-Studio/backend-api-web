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
            $table->string('tgl_from');
            $table->string('tgl_to');
            $table->text('catatan')->nullable();
            $table->integer('durasi');
            $table->foreignId('status_cuti_id')->constrained('status_cutis');
            $table->foreignId('verifikator_1')->nullable()->constrained('users');
            $table->foreignId('verifikator_2')->nullable()->constrained('users');
            $table->text('alasan')->nullable();
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
