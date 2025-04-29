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
        Schema::create('status_karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->integer('kategori_status')->nullable(); // 1 = fulltime, 2 = parttime, 3 = outsourcing
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_karyawans');
    }
};
