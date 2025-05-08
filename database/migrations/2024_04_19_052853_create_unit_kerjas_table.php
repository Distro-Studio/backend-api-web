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
        Schema::create('unit_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_unit');
            $table->boolean('jenis_karyawan'); // 1 = shift, 0 = non shift
            $table->foreignId('kategori_unit_id')->nullable()->constrained('kategori_unit_kerjas')->onUpdate('cascade'); // 1 = direksi, 2 = karyawan
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_kerjas');
    }
};
