<?php

namespace Database\Seeders\Perusahaan;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JenisPenilaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data contoh untuk diisi pada tabel jenis_penilaians
        $data = [
            [
                'nama' => 'Penilaian Karyawan Tetap',
                'status_karyawan_id' => 1, // contoh status_karyawan_id untuk karyawan tetap
                'jabatan_penilai' => 2, // contoh jabatan_penilai, bisa diubah sesuai dengan ID di tabel jabatans
                'jabatan_dinilai' => 3, // contoh jabatan_dinilai, bisa diubah sesuai dengan ID di tabel jabatans
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Penilaian Karyawan Kontrak',
                'status_karyawan_id' => 2, // contoh status_karyawan_id untuk karyawan kontrak
                'jabatan_penilai' => 4, // contoh jabatan_penilai, bisa diubah sesuai dengan ID di tabel jabatans
                'jabatan_dinilai' => 5, // contoh jabatan_dinilai, bisa diubah sesuai dengan ID di tabel jabatans
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Penilaian Karyawan Magang',
                'status_karyawan_id' => 3, // contoh status_karyawan_id untuk karyawan magang
                'jabatan_penilai' => 6, // contoh jabatan_penilai, bisa diubah sesuai dengan ID di tabel jabatans
                'jabatan_dinilai' => 7, // contoh jabatan_dinilai, bisa diubah sesuai dengan ID di tabel jabatans
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Masukkan data ke tabel jenis_penilaians
        DB::table('jenis_penilaians')->insert($data);
    }
}
