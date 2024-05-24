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
        Schema::create('ters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_ter_id')->constrained('kategori_ters');
            $table->foreignId('ptkp_id')->constrained('ptkps');
            $table->integer('from_ter');
            $table->integer('to_ter');
            $table->integer('percentage_ter');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ters');
    }
};
