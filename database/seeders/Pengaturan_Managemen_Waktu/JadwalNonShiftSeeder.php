<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use App\Models\NonShift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JadwalNonShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NonShift::create([
            'id' => 1,
            'nama' => 'Jam Pulang',
            'jam_from' => '06:00:00',
            'jam_to' => '17:30:00',
        ]);
    }
}
