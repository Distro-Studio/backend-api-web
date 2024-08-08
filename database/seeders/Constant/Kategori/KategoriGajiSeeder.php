<?php

namespace Database\Seeders\Constant\Kategori;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriGajiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['Penghasilan Dasar', 'Penambah', 'Pengurang'];

        foreach ($kategori as $kategori) {
            DB::table('kategori_gajis')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
