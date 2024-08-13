<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Jabatan;
use App\Models\Pertanyaan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PertanyaanSeeder extends Seeder
{
    public function run()
    {
        $pertanyaans = [
            [
                'role_id' => 4,
                'penilaian_id' => 1, // Pastikan penilaian_id ini sesuai dengan yang ada di tabel `penilaians`
                'pertanyaan' => 'Seberapa sering Anda memenuhi target kerja yang ditetapkan?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 4,
                'penilaian_id' => 1,
                'pertanyaan' => 'Seberapa baik Anda beradaptasi dengan perubahan di tempat kerja?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 4,
                'penilaian_id' => 2, // Contoh untuk penilaian lain
                'pertanyaan' => 'Seberapa efektif Anda dalam berkomunikasi dengan rekan kerja?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 2,
                'penilaian_id' => 2,
                'pertanyaan' => 'Bagaimana Anda menilai kemampuan Anda dalam bekerja secara tim?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 2,
                'penilaian_id' => 3, // Contoh untuk penilaian lain
                'pertanyaan' => 'Seberapa puas Anda dengan lingkungan kerja yang disediakan oleh perusahaan?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Masukkan data ke dalam tabel `pertanyaans`
        Pertanyaan::insert($pertanyaans);
    }
}
