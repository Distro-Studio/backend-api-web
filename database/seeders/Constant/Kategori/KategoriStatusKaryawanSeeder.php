<?php

namespace Database\Seeders\Constant\Kategori;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriStatusKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['FullTime', 'PartTime', 'Outsourcing'];

        foreach ($kategori as $kategori) {
            DB::table('kategori_status_karyawans')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
