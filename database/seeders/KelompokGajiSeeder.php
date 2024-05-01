<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KelompokGajiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Use uppercase letters
            $randomIndex = rand(0, strlen($alphabet) - 1);
            $randomCharacter = $alphabet[$randomIndex];

            $kelompok_gaji = [
                'nama_kelompok' => $randomCharacter,
                'besaran_gaji' => rand(500000, 5000000), // Tunjangan random antara 500.000 - 5.000.000
                'created_at' => Carbon::now()->subDays(rand(0, 365)), // Random dalam 1 tahun
                'updated_at' => Carbon::now(),
            ];

            DB::table('kelompok_gajis')->insert($kelompok_gaji);
        }
    }
}
