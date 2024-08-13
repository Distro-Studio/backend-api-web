<?php

namespace Database\Seeders\Perusahaan;

use App\Models\Penilaian;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PenilaianSeeder extends Seeder
{
    public function run()
    {
        $penilaians = [
            [
                'tgl_mulai' => '2024-01-01',
                'tgl_selesai' => '2024-03-31',
                'status_karyawan_id' => 3, // Asumsi: 1 untuk karyawan magang
                'lama_bekerja' => 90, // 3 bulan = 90 hari (asumsi setiap bulan memiliki 30 hari)
                'total_pertanyaan' => 10,
                'rata_rata' => null, // Nilai rata-rata akan dihitung setelah penilaian selesai
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tgl_mulai' => '2024-01-01',
                'tgl_selesai' => '2024-12-31',
                'status_karyawan_id' => 2, // Asumsi: 2 untuk karyawan kontrak
                'lama_bekerja' => 365, // 1 tahun = 365 hari
                'total_pertanyaan' => 15,
                'rata_rata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tgl_mulai' => '2024-01-01',
                'tgl_selesai' => '2024-12-31',
                'status_karyawan_id' => 1, // Asumsi: 3 untuk karyawan tetap
                'lama_bekerja' => 730, // 2 tahun = 730 hari (untuk contoh karyawan tetap yang telah bekerja lebih dari 1 tahun)
                'total_pertanyaan' => 20,
                'rata_rata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Masukkan data ke dalam tabel `penilaians`
        Penilaian::insert($penilaians);
    }
}
