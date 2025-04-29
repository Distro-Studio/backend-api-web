<?php

namespace Database\Seeders\Constant\Kategori;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriUnitKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['Direksi', 'Karyawan'];

        foreach ($kategori as $kategori) {
            DB::table('kategori_unit_kerjas')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
