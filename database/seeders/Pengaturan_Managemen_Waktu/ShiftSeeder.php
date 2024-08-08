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
      'jam_from' => '2024-08-07T07:00:12.000000Z',
      'jam_to' => '2024-08-07T15:00:12.000000Z'
    ]);

    Shift::create([
      'nama' => 'Sore',
      'jam_from' => '2024-08-07T17:00:12.000000Z',
      'jam_to' => '2024-08-07T20:00:12.000000Z'
    ]);

    Shift::create([
      'nama' => 'Malam',
      'jam_from' => '2024-08-07T19:00:12.000000Z',
      'jam_to' => '2024-08-07T01:00:12.000000Z'
    ]);
  }
}
