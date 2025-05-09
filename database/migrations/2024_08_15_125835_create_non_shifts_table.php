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
        Schema::create('non_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jam_from')->nullable(); // H:i:s
            $table->string('jam_to')->nullable(); // H:i:s
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_shifts');
    }
};
