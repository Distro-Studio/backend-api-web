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
        Schema::create('data_karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('email')->nullable();
            $table->integer('no_rm')->nullable();
            $table->integer('no_manulife')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->date('tgl_keluar')->nullable();
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->nullable();
            $table->foreignId('jabatan_id')->constrained('jabatans')->nullable();
            $table->foreignId('kompetensi_id')->constrained('kompetensis')->nullable();

            $table->integer('tunjangan_jabatan')->nullable();
            $table->integer('tunjangan_fungsional')->nullable();
            $table->integer('tunjangan_khusus')->nullable();
            $table->integer('tunjangan_lainnya')->nullable();
            $table->integer('uang_makan')->nullable();

            $table->integer('uang_lembur')->nullable();
            $table->string('nik')->nullable();
            $table->string('nik_ktp', 16)->nullable();
            $table->string('gelar_depan')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 50)->nullable();
            $table->string('no_bpjsksh', 50)->nullable();
            $table->string('no_bpjsktk', 50)->nullable();
            $table->date('tgl_diangkat')->nullable();
            $table->integer('masa_kerja')->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('jenis_kelamin', 50)->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('golongan_darah', 50)->nullable();
            $table->integer('tinggi_badan')->nullable();
            $table->integer('berat_badan')->nullable();
            $table->string('no_ijazah')->nullable();
            $table->integer('tahun_lulus')->nullable();
            $table->string('no_kk', 20)->nullable();
            $table->string('status_karyawan', 50)->nullable();
            $table->unsignedBigInteger('kelompok_gaji_id')->nullable();
            $table->string('no_str', 16)->nullable();
            $table->date('masa_berlaku_str')->nullable();
            $table->string('no_sip', 50)->nullable();
            $table->date('masa_berlaku_sip')->nullable();
            $table->unsignedBigInteger('ptkp_id')->nullable();
            $table->date('tgl_berakhir_pks')->nullable();
            $table->integer('masa_diklat')->nullable();
            $table->timestamps();

            $table->foreign('kelompok_gaji_id')->on('kelompok_gajis')->references('id');
            $table->foreign('ptkp_id')->on('ptkps')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_karyawans');
    }
};
