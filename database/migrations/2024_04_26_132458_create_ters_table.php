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
            $table->string('from_ter', 20)->default('0');
            $table->string('to_ter', 20)->default('0');
            $table->string('percentage_ter')->default('0');
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
