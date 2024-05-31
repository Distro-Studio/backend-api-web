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
            'jam_from' => Carbon::parse('2024-01-01 06:00:00'),
            'jam_to' => Carbon::parse('2024-01-01 15:00:00')
        ]);
        
        Shift::create([
            'nama' => 'Sore',
            'jam_from' => Carbon::parse('2024-01-01 15:00:00'),
            'jam_to' => Carbon::parse('2024-01-01 22:00:00')
        ]);
        
        Shift::create([
            'nama' => 'Malam',
            'jam_from' => Carbon::parse('2024-01-01 22:00:00'),
            'jam_to' => Carbon::parse('2024-01-02 06:00:00') // Perhatikan bahwa tanggalnya berubah ke hari berikutnya
        ]);
    }
}
