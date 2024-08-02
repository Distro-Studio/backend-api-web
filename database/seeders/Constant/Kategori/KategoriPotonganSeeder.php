<?php

namespace Database\Seeders\Constant\Kategori;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriPotonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['Gaji Bruto', 'Gaji Pokok'];

        foreach ($kategori as $kategori) {
            DB::table('kategori_potongans')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
