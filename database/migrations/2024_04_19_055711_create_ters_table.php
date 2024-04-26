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
            $table->foreignId('kategori_ter_id');
            $table->string('from', 20)->default('0');
            $table->string('to', 20)->default('0');
            $table->string('percentage')->default('0');
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
