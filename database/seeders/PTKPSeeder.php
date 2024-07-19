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
            ['kode_ptkp' => 'TK/0', 'nilai' => 54000000],
            ['kode_ptkp' => 'TK/1', 'nilai' => 58500000],
            ['kode_ptkp' => 'K/0', 'nilai' => 58500000]
        ];
        $TER_B = [
            ['kode_ptkp' => 'TK/2', 'nilai' => 63000000],
            ['kode_ptkp' => 'TK/3', 'nilai' => 67500000],
            ['kode_ptkp' => 'K/1', 'nilai' => 63000000],
            ['kode_ptkp' => 'K/2', 'nilai' => 67500000]
        ];
        $TER_C = [
            ['kode_ptkp' => 'K/3', 'nilai' => 72000000]
        ];

        foreach ($TER_A as $item) {
            Ptkp::create([
                'kode_ptkp' => $item['kode_ptkp'],
                'kategori_ter_id' => 1,
                'nilai' => $item['nilai']
            ]);
        }
        foreach ($TER_B as $item) {
            Ptkp::create([
                'kode_ptkp' => $item['kode_ptkp'],
                'kategori_ter_id' => 2,
                'nilai' => $item['nilai']
            ]);
        }
        foreach ($TER_C as $item) {
            Ptkp::create([
                'kode_ptkp' => $item['kode_ptkp'],
                'kategori_ter_id' => 3,
                'nilai' => $item['nilai']
            ]);
        }
    }
}
