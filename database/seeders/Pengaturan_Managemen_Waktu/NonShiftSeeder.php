<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use App\Models\NonShift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NonShiftSeeder extends Seeder
{
    public function run(): void
    {
        NonShift::create([
            'nama' => 'Jam kerja tanpa shift',
            'jam_from' => '06:00:00',
            'jam_to' => '17:00:00'
        ]);
    }
}
