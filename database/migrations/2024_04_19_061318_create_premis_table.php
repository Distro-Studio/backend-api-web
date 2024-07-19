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
        Schema::create('premis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_premi');
            $table->string('sumber_potongan');
            $table->boolean('jenis_premi');
            $table->integer('besaran_premi');
            $table->integer('minimal_rate')->nullable();
            $table->integer('maksimal_rate')->nullable();
            $table->boolean('has_custom_formula')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premis');
    }
};
