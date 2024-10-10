<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use Carbon\Carbon;
use App\Models\NonShift;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JadwalNonShiftSeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            ['day' => 'Senin', 'jam_to' => '17:30:00'],
            ['day' => 'Selasa', 'jam_to' => '17:30:00'],
            ['day' => 'Rabu', 'jam_to' => '17:30:00'],
            ['day' => 'Kamis', 'jam_to' => '17:30:00'],
            ['day' => 'Jumat', 'jam_to' => '17:30:00'],
            ['day' => 'Sabtu', 'jam_to' => '14:00:00']
        ];

        foreach ($days as $dayData) {
            NonShift::create([
                'nama' => $dayData['day'],
                'jam_from' => '06:00:00',
                'jam_to' => $dayData['jam_to'],
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }
}
