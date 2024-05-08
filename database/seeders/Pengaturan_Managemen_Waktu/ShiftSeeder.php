<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Shift::create([
            'nama' => 'Pagi',
            'jam_from' => '07:00:00',
            'jam_to' => '15:00:00'
        ]);
        Shift::create([
            'nama' => 'Sore',
            'jam_from' => '15:00:00',
            'jam_to' => '23:00:00'
        ]);
        Shift::create([
            'nama' => 'Malam',
            'jam_from' => '23:00:00',
            'jam_to' => '07:00:00'
        ]);
    }
}
