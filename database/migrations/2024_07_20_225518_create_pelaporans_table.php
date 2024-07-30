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
        Schema::create('pelaporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelapor')->constrained('users');
            $table->foreignId('pelaku')->constrained('users');
            $table->dateTime('tgl_kejadian');
            $table->string('lokasi');
            $table->text('kronologi');
            $table->foreignId('upload_foto')->nullable()->constrained('berkas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelaporans');
    }
};
