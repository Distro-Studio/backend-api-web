<?php

namespace Database\Seeders\Pengaturan_Finance;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TERSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $kategori_terID = [1, 2, 3];
            $ptkpID = [1, 2, 3, 4, 5, 6, 7, 8];

            $ters = [
                'kategori_ter_id' => rand(1, count($kategori_terID)),
                'ptkp_id' => rand(1, count($ptkpID)),
                'from_ter' => rand(800000, 5400000),
                'to_ter' => rand(5400000, 10050000),
                'percentage_ter' => rand(0, 34),
                'created_at' => Carbon::now()->subDays(rand(0, 365)), // Random dalam 1 tahun
                'updated_at' => Carbon::now(),
            ];

            DB::table('ters')->insert($ters);
        }
    }
}
