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
            $table->string('email')->unique()->nullable();
            $table->integer('no_rm')->nullable();
            $table->integer('no_manulife')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->date('tgl_keluar')->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas');
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans');
            $table->foreignId('kompetensi_id')->nullable()->constrained('kompetensis');

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
            $table->boolean('jenis_kelamin')->nullable();
            $table->foreignId('kategori_agama_id')->nullable()->constrained('kategori_agamas');
            $table->foreignId('kategori_darah_id')->nullable()->constrained('kategori_darahs');
            $table->integer('tinggi_badan')->nullable();
            $table->integer('berat_badan')->nullable();
            $table->enum('pendidikan_terakhir', ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'])->nullable();
            $table->string('no_ijazah')->nullable();
            $table->integer('tahun_lulus')->nullable();
            $table->string('no_kk', 20)->nullable();
            $table->foreignId('status_karyawan_id')->nullable()->constrained('status_karyawans'); // Tetap, Kontrak, Magang
            $table->foreignId('kelompok_gaji_id')->nullable()->constrained('kelompok_gajis');
            $table->string('no_str', 16)->nullable();
            $table->date('masa_berlaku_str')->nullable();
            $table->string('no_sip', 50)->nullable();
            $table->date('masa_berlaku_sip')->nullable();
            $table->foreignId('ptkp_id')->nullable()->constrained('ptkps');
            $table->date('tgl_berakhir_pks')->nullable();
            $table->integer('masa_diklat')->nullable();
            $table->timestamps();
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