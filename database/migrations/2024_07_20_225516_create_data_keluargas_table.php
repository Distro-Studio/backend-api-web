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
        Schema::create('data_keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_karyawan_id')->constrained('data_karyawans');
            $table->string('nama_keluarga');
            $table->enum('hubungan', ['Ayah', 'Ibu', 'Anak', 'Suami', 'Istri', 'Nenek', 'Kakek', 'Ayah Suami', 'Ibu Suami', 'Ayah Istri', 'Ibu Istri']);
            $table->string('pendidikan_terakhir');
            $table->boolean('status_hidup');
            $table->string('pekerjaan')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_keluargas');
    }
};
