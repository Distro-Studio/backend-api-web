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
        Schema::create('tipe_cutis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('kuota')->nullable();
            $table->boolean('is_need_requirement');
            $table->string('keterangan');
            $table->boolean('cuti_administratif')->default(0);
            $table->boolean('is_unlimited')->default(0); // 0 = limited, 1 = unlimited
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipe_cutis');
    }
};
