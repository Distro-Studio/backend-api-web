<?php

namespace Database\Seeders\Perusahaan;

use Carbon\Carbon;
use App\Models\Penilaian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PenilaianSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user_dinilai' => 1, // contoh ID user yang dinilai, sesuaikan dengan data yang ada di tabel users
                'user_penilai' => 2, // contoh ID user yang menilai, sesuaikan dengan data yang ada di tabel users
                'jenis_penilaian_id' => 1, // contoh ID jenis penilaian, sesuaikan dengan data yang ada di tabel jenis_penilaians
                'pertanyaan_jawaban' => json_encode([
                    ['pertanyaan' => 'Bagaimana kinerja harian?', 'jawaban' => 4],
                    ['pertanyaan' => 'Apakah karyawan berinisiatif?', 'jawaban' => 5],
                ]),
                'total_pertanyaan' => 2, // jumlah pertanyaan yang dijawab
                'rata_rata' => 3, // contoh rata-rata nilai jawaban
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_dinilai' => 3, // contoh ID user yang dinilai
                'user_penilai' => 4, // contoh ID user yang menilai
                'jenis_penilaian_id' => 2, // contoh ID jenis penilaian
                'pertanyaan_jawaban' => json_encode([
                    ['pertanyaan' => 'Bagaimana sikap kerja?', 'jawaban' => 3],
                    ['pertanyaan' => 'Apakah karyawan bekerja sama dengan tim?', 'jawaban' => 4],
                ]),
                'total_pertanyaan' => 2, // jumlah pertanyaan yang dijawab
                'rata_rata' => 5, // contoh rata-rata nilai jawaban
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_dinilai' => 5, // contoh ID user yang dinilai
                'user_penilai' => 6, // contoh ID user yang menilai
                'jenis_penilaian_id' => 3, // contoh ID jenis penilaian
                'pertanyaan_jawaban' => json_encode([
                    ['pertanyaan' => 'Bagaimana kehadiran kerja?', 'jawaban' => 5],
                    ['pertanyaan' => 'Apakah karyawan menyelesaikan tugas tepat waktu?', 'jawaban' => 4],
                ]),
                'total_pertanyaan' => 2, // jumlah pertanyaan yang dijawab
                'rata_rata' => 4, // contoh rata-rata nilai jawaban
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Masukkan data ke tabel penilaians
        DB::table('penilaians')->insert($data);
    }
}
