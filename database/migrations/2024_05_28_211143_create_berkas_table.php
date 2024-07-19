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
        Schema::create('berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('file_id');
            $table->string('nama');
            $table->enum('kategori', ['Pribadi', 'Umum', 'System', 'Lain - Lain'])->change();
            $table->string('path');
            $table->dateTime('tgl_upload');
            $table->string('nama_file');
            $table->string('ext')->nullable();
            $table->string('size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
