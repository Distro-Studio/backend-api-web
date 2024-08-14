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
        Schema::create('perubahan_personals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riwayat_perubahan_id')->constrained('riwayat_perubahans');
            $table->string('tempat_lahir');
            $table->string('tgl_lahir');
            $table->string('no_telp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perubahan_personals');
    }
};
