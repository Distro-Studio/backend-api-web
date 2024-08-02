<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use App\Models\LokasiKantor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LokasiKantorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LokasiKantor::create([
            'id' => 1,
            'alamat' => 'Jl. Slamet Riyadi No.404, Purwosari, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57142',
            'lat' => '-7.5626538',
            'long' => '110.8018715',
            'radius' => 25,
        ]);
    }
}
