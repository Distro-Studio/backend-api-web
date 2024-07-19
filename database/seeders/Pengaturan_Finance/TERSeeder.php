<?php

namespace Database\Seeders\Pengaturan_Finance;

use Carbon\Carbon;
use App\Models\Ter;
use App\Models\Ptkp;
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
        $tarif_TER_A = [
            ['from_ter' => 0, 'to_ter' => 5400000, 'percentage' => 0],
            ['from_ter' => 5400001, 'to_ter' => 5650000, 'percentage' => 0.25],
            ['from_ter' => 5650001, 'to_ter' => 5950000, 'percentage' => 0.50],
            ['from_ter' => 5950001, 'to_ter' => 6300000, 'percentage' => 0.75],
            ['from_ter' => 6300001, 'to_ter' => 6750000, 'percentage' => 1],
            ['from_ter' => 6750001, 'to_ter' => 7500000, 'percentage' => 1.25],
            ['from_ter' => 7500001, 'to_ter' => 8550000, 'percentage' => 1.50],
            ['from_ter' => 8550001, 'to_ter' => 9650000, 'percentage' => 1.75],
            ['from_ter' => 9650001, 'to_ter' => 10050000, 'percentage' => 2],
            ['from_ter' => 10050001, 'to_ter' => 10350000, 'percentage' => 2.25],
            ['from_ter' => 10350001, 'to_ter' => 10700000, 'percentage' => 2.50],
            ['from_ter' => 10700001, 'to_ter' => 11050000, 'percentage' => 3],
            ['from_ter' => 11050001, 'to_ter' => 11600000, 'percentage' => 3.50],
            ['from_ter' => 11600001, 'to_ter' => 12500000, 'percentage' => 4],
            ['from_ter' => 12500001, 'to_ter' => 13750000, 'percentage' => 5],
            ['from_ter' => 13750001, 'to_ter' => 15100000, 'percentage' => 6],
            ['from_ter' => 15100001, 'to_ter' => 16950000, 'percentage' => 7],
            ['from_ter' => 16950001, 'to_ter' => 19750000, 'percentage' => 8],
            ['from_ter' => 19750001, 'to_ter' => 24150000, 'percentage' => 9],
            ['from_ter' => 24150001, 'to_ter' => 26450000, 'percentage' => 10],
            ['from_ter' => 26450001, 'to_ter' => 28000000, 'percentage' => 11],
            ['from_ter' => 28000001, 'to_ter' => 30050000, 'percentage' => 12],
            ['from_ter' => 30050001, 'to_ter' => 32400000, 'percentage' => 13],
            ['from_ter' => 32400001, 'to_ter' => 35400000, 'percentage' => 14],
            ['from_ter' => 35400001, 'to_ter' => 39100000, 'percentage' => 15],
            ['from_ter' => 39100001, 'to_ter' => 43850000, 'percentage' => 16],
            ['from_ter' => 43850001, 'to_ter' => 47800000, 'percentage' => 17],
            ['from_ter' => 47800001, 'to_ter' => 51400000, 'percentage' => 18],
            ['from_ter' => 51400001, 'to_ter' => 56300000, 'percentage' => 19],
            ['from_ter' => 56300001, 'to_ter' => 62200000, 'percentage' => 20],
            ['from_ter' => 62200001, 'to_ter' => 68600000, 'percentage' => 21],
            ['from_ter' => 68600001, 'to_ter' => 77500000, 'percentage' => 22],
            ['from_ter' => 77500001, 'to_ter' => 89000000, 'percentage' => 23],
            ['from_ter' => 89000001, 'to_ter' => 103000000, 'percentage' => 24],
            ['from_ter' => 103000001, 'to_ter' => 125000000, 'percentage' => 25],
            ['from_ter' => 125000001, 'to_ter' => 157000000, 'percentage' => 26],
            ['from_ter' => 157000001, 'to_ter' => 206000000, 'percentage' => 27],
            ['from_ter' => 206000001, 'to_ter' => 337000000, 'percentage' => 28],
            ['from_ter' => 337000001, 'to_ter' => 454000000, 'percentage' => 29],
            ['from_ter' => 454000001, 'to_ter' => 550000000, 'percentage' => 30],
            ['from_ter' => 550000001, 'to_ter' => 695000000, 'percentage' => 31],
            ['from_ter' => 695000001, 'to_ter' => 910000000, 'percentage' => 32],
            ['from_ter' => 910000001, 'to_ter' => 1400000000, 'percentage' => 33],
            ['from_ter' => 1400000001, 'to_ter' => null, 'percentage' => 34],
        ];

        $tarif_TER_B = [
            ['from_ter' => 0, 'to_ter' => 6200000, 'percentage' => 0],
            ['from_ter' => 6200001, 'to_ter' => 6500000, 'percentage' => 0.25],
            ['from_ter' => 6500001, 'to_ter' => 6850000, 'percentage' => 0.50],
            ['from_ter' => 6850001, 'to_ter' => 7300000, 'percentage' => 0.75],
            ['from_ter' => 7300001, 'to_ter' => 9200000, 'percentage' => 1],
            ['from_ter' => 9200001, 'to_ter' => 10750000, 'percentage' => 1.50],
            ['from_ter' => 10750001, 'to_ter' => 11250000, 'percentage' => 2],
            ['from_ter' => 11250001, 'to_ter' => 11600000, 'percentage' => 2.50],
            ['from_ter' => 11600001, 'to_ter' => 12600000, 'percentage' => 3],
            ['from_ter' => 12600001, 'to_ter' => 13600000, 'percentage' => 4],
            ['from_ter' => 13600001, 'to_ter' => 14950000, 'percentage' => 5],
            ['from_ter' => 14950001, 'to_ter' => 16400000, 'percentage' => 6],
            ['from_ter' => 16400001, 'to_ter' => 18450000, 'percentage' => 7],
            ['from_ter' => 18450001, 'to_ter' => 21850000, 'percentage' => 8],
            ['from_ter' => 21850001, 'to_ter' => 26000000, 'percentage' => 9],
            ['from_ter' => 26000001, 'to_ter' => 27700000, 'percentage' => 10],
            ['from_ter' => 27700001, 'to_ter' => 29350000, 'percentage' => 11],
            ['from_ter' => 29350001, 'to_ter' => 31450000, 'percentage' => 12],
            ['from_ter' => 31450001, 'to_ter' => 33950000, 'percentage' => 13],
            ['from_ter' => 33950001, 'to_ter' => 37100000, 'percentage' => 14],
            ['from_ter' => 37100001, 'to_ter' => 41100000, 'percentage' => 15],
            ['from_ter' => 41100001, 'to_ter' => 45800000, 'percentage' => 16],
            ['from_ter' => 45800001, 'to_ter' => 49500000, 'percentage' => 17],
            ['from_ter' => 49500001, 'to_ter' => 53800000, 'percentage' => 18],
            ['from_ter' => 53800001, 'to_ter' => 58500000, 'percentage' => 19],
            ['from_ter' => 58500001, 'to_ter' => 64000000, 'percentage' => 20],
            ['from_ter' => 64000001, 'to_ter' => 71000000, 'percentage' => 21],
            ['from_ter' => 71000001, 'to_ter' => 80000000, 'percentage' => 22],
            ['from_ter' => 80000001, 'to_ter' => 93000000, 'percentage' => 23],
            ['from_ter' => 93000001, 'to_ter' => 109000000, 'percentage' => 24],
            ['from_ter' => 109000001, 'to_ter' => 129000000, 'percentage' => 25],
            ['from_ter' => 129000001, 'to_ter' => 163000000, 'percentage' => 26],
            ['from_ter' => 163000001, 'to_ter' => 211000000, 'percentage' => 27],
            ['from_ter' => 211000001, 'to_ter' => 374000000, 'percentage' => 28],
            ['from_ter' => 374000001, 'to_ter' => 459000000, 'percentage' => 29],
            ['from_ter' => 459000001, 'to_ter' => 555000000, 'percentage' => 30],
            ['from_ter' => 555000001, 'to_ter' => 704000000, 'percentage' => 31],
            ['from_ter' => 704000001, 'to_ter' => 957000000, 'percentage' => 32],
            ['from_ter' => 957000001, 'to_ter' => 1405000000, 'percentage' => 33],
            ['from_ter' => 1405000001, 'to_ter' => null, 'percentage' => 34],
        ];

        $tarif_TER_C = [
            ['from_ter' => 0, 'to_ter' => 6600000, 'percentage' => 0],
            ['from_ter' => 6600001, 'to_ter' => 6950000, 'percentage' => 0.25],
            ['from_ter' => 6950001, 'to_ter' => 7350000, 'percentage' => 0.50],
            ['from_ter' => 7350001, 'to_ter' => 7800000, 'percentage' => 0.75],
            ['from_ter' => 7800001, 'to_ter' => 8850000, 'percentage' => 1],
            ['from_ter' => 8850001, 'to_ter' => 9800000, 'percentage' => 1.25],
            ['from_ter' => 9800001, 'to_ter' => 10950000, 'percentage' => 1.50],
            ['from_ter' => 10950001, 'to_ter' => 11200000, 'percentage' => 1.75],
            ['from_ter' => 11200001, 'to_ter' => 12050000, 'percentage' => 2],
            ['from_ter' => 12050001, 'to_ter' => 12950000, 'percentage' => 3],
            ['from_ter' => 12950001, 'to_ter' => 14150000, 'percentage' => 4],
            ['from_ter' => 14150001, 'to_ter' => 15550000, 'percentage' => 5],
            ['from_ter' => 15550001, 'to_ter' => 17050000, 'percentage' => 6],
            ['from_ter' => 17050001, 'to_ter' => 19500000, 'percentage' => 7],
            ['from_ter' => 19500001, 'to_ter' => 22700000, 'percentage' => 8],
            ['from_ter' => 22700001, 'to_ter' => 26600000, 'percentage' => 9],
            ['from_ter' => 26600001, 'to_ter' => 28100000, 'percentage' => 10],
            ['from_ter' => 28100001, 'to_ter' => 30100000, 'percentage' => 11],
            ['from_ter' => 30100001, 'to_ter' => 32600000, 'percentage' => 12],
            ['from_ter' => 32600001, 'to_ter' => 35400000, 'percentage' => 13],
            ['from_ter' => 35400001, 'to_ter' => 38900000, 'percentage' => 14],
            ['from_ter' => 38900001, 'to_ter' => 43000000, 'percentage' => 15],
            ['from_ter' => 43000001, 'to_ter' => 47400000, 'percentage' => 16],
            ['from_ter' => 47400001, 'to_ter' => 51200000, 'percentage' => 17],
            ['from_ter' => 51200001, 'to_ter' => 55800000, 'percentage' => 18],
            ['from_ter' => 55800001, 'to_ter' => 60400000, 'percentage' => 19],
            ['from_ter' => 60400001, 'to_ter' => 66700000, 'percentage' => 20],
            ['from_ter' => 66700001, 'to_ter' => 74500000, 'percentage' => 21],
            ['from_ter' => 74500001, 'to_ter' => 83200000, 'percentage' => 22],
            ['from_ter' => 83200001, 'to_ter' => 95000000, 'percentage' => 23],
            ['from_ter' => 95000001, 'to_ter' => 110000000, 'percentage' => 24],
            ['from_ter' => 110000001, 'to_ter' => 134000000, 'percentage' => 25],
            ['from_ter' => 134000001, 'to_ter' => 169000000, 'percentage' => 26],
            ['from_ter' => 169000001, 'to_ter' => 221000000, 'percentage' => 27],
            ['from_ter' => 221000001, 'to_ter' => 390000000, 'percentage' => 28],
            ['from_ter' => 390000001, 'to_ter' => 463000000, 'percentage' => 29],
            ['from_ter' => 463000001, 'to_ter' => 561000000, 'percentage' => 30],
            ['from_ter' => 561000001, 'to_ter' => 709000000, 'percentage' => 31],
            ['from_ter' => 709000001, 'to_ter' => 965000000, 'percentage' => 32],
            ['from_ter' => 965000001, 'to_ter' => 1419000000, 'percentage' => 33],
            ['from_ter' => 1419000001, 'to_ter' => null, 'percentage' => 34],
        ];

        foreach ($tarif_TER_A as $tarif) {
            Ter::create([
                'kategori_ter_id' => 1,
                'from_ter' => $tarif['from_ter'],
                'to_ter' => $tarif['to_ter'],
                'percentage' => $tarif['percentage'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        foreach ($tarif_TER_B as $tarif) {
            Ter::create([
                'kategori_ter_id' => 2,
                'from_ter' => $tarif['from_ter'],
                'to_ter' => $tarif['to_ter'],
                'percentage' => $tarif['percentage'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        foreach ($tarif_TER_C as $tarif) {
            Ter::create([
                'kategori_ter_id' => 3,
                'from_ter' => $tarif['from_ter'],
                'to_ter' => $tarif['to_ter'],
                'percentage' => $tarif['percentage'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
