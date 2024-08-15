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
      'jam_from' => '06:00:00',
      'jam_to' => '16:00:00'
    ]);

    Shift::create([
      'nama' => 'Sore',
      'jam_from' => '16:00:00',
      'jam_to' => '23:00:00'
    ]);

    Shift::create([
      'nama' => 'Malam',
      'jam_from' => '23:00:00',
      'jam_to' => '06:00:00'
    ]);
  }
}
