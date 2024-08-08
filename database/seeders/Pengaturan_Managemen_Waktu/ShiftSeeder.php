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
            'jam_from' => Carbon::createFromTime(7, 0, 0)->toTimeString(),
            'jam_to' => Carbon::createFromTime(17, 0, 0)->toTimeString()
        ]);

        Shift::create([
            'nama' => 'Sore',
            'jam_from' => Carbon::createFromTime(17, 0, 0)->toTimeString(),
            'jam_to' => Carbon::createFromTime(22, 0, 0)->toTimeString()
        ]);

        Shift::create([
            'nama' => 'Malam',
            'jam_from' => Carbon::createFromTime(22, 0, 0)->toTimeString(),
            'jam_to' => Carbon::createFromTime(7, 0, 0)->toTimeString()
        ]);
    }
}
