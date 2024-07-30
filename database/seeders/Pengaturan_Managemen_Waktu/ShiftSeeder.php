<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use Carbon\Carbon;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Shift::create([
            'nama' => 'Pagi',
            'jam_from' => Carbon::parse('07:00:00'),
            'jam_to' => Carbon::parse('17:00:00')
        ]);

        Shift::create([
            'nama' => 'Sore',
            'jam_from' => Carbon::parse('17:00:00'),
            'jam_to' => Carbon::parse('22:00:00')
        ]);

        Shift::create([
            'nama' => 'Malam',
            'jam_from' => Carbon::parse('22:00:00'),
            'jam_to' => Carbon::parse('07:00:00')
        ]);
    }
}
