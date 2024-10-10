<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModulVerifikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'label' => 'Permintaan Perubahan Data',
                'max_order' => 1
            ],
            [
                'label' => 'Tukar Jadwal',
                'max_order' => 2
            ],
            [
                'label' => 'Cuti',
                'max_order' => 2
            ],
            [
                'label' => 'Izin',
                'max_order' => 1
            ],
            [
                'label' => 'Diklat Internal',
                'max_order' => 3
            ],
            [
                'label' => 'Diklat Eksternal',
                'max_order' => 2
            ]
        ];

        foreach ($data as $item) {
            DB::table('modul_verifikasis')->insert([
                'label' => $item['label'],
                'max_order' => $item['max_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
