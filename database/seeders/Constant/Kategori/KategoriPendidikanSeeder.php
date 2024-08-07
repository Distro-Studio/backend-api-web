<?php

namespace Database\Seeders\Constant\Kategori;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriPendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['SD', 'SMP', 'SMA', 'SMK', 'Diploma 1 (D1)', 'Diploma 2 (D2)', 'Diploma 3 (D3)', 'Diploma 4 (D4) / Sarjana Terapan', 'Sarjana (S1)', 'Magister (S2)', 'Doktor (S3)', 'Pendidikan Non-Formal'];
        foreach ($kategori as $kategori) {
            DB::table('kategori_pendidikans')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
