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
        Schema::create('detail_gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penggajian_id')->constrained('penggajians');
            $table->integer('kategori'); // 1 = pengurang, 2 = penambah, 3 = gaji pokok
            $table->string('nama_detail');
            $table->integer('besaran')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_gajis');
    }
};
