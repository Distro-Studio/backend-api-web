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
        $kategori = ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];
        foreach ($kategori as $kategori) {
            DB::table('kategori_pendidikans')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
