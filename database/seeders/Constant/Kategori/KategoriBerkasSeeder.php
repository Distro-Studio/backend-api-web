<?php

namespace Database\Seeders\Constant\Kategori;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriBerkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['Pribadi', 'Umum', 'System', 'Lainnya', 'Materi Pelatihan', 'Tentang RSKI'];

        foreach ($kategori as $kategori) {
            DB::table('kategori_berkas')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
