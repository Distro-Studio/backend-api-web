<?php

namespace Database\Seeders;

use App\Models\Ptkp;
use App\Models\Ter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PTKPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $TER_A = [
            'TK/0',
            'TK/1',
            'K/0'
        ];
        $TER_B = [
            'TK/2',
            'TK/3',
            'K/1',
            'K/2'
        ];
        $TER_C = [
            'K/3'
        ];

        foreach ($TER_A as $item) {
            Ptkp::create([
                'kode_ptkp' => $item,
                'kategori_ter_id' => 1
            ]);
        }
        foreach ($TER_B as $item) {
            Ptkp::create([
                'kode_ptkp' => $item,
                'kategori_ter_id' => 2
            ]);
        }
        foreach ($TER_C as $item) {
            Ptkp::create([
                'kode_ptkp' => $item,
                'kategori_ter_id' => 3
            ]);
        }
    }
}
