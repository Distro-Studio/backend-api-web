<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KelompokGajiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        for ($i = 0; $i < strlen($alphabet); $i++) {
            $randomCharacter = $alphabet[$i];

            $kelompok_gaji = [
                'nama_kelompok' => 'Kelompok Gaji ' . $randomCharacter,
                'besaran_gaji' => $faker->numberBetween(5000000, 10000000),
                'created_at' => Carbon::now()->subDays($faker->numberBetween(0, 365)),
                'updated_at' => Carbon::now(),
            ];

            DB::table('kelompok_gajis')->insert($kelompok_gaji);
        }
    }
}
