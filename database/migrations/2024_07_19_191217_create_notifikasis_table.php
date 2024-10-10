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
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_notifikasi_id')->constrained('kategori_notifikasis');
            $table->foreignId('user_id')->constrained('users'); // penerima
            $table->text('message');
            $table->boolean('is_read')->default(false); // true = read, false = unread
            $table->boolean('is_verifikasi')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
