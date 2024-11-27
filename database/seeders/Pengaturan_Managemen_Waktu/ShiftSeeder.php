<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use Carbon\Carbon;
use App\Models\Shift;
use App\Models\UnitKerja;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;

class ShiftSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $unit_kerja_id = UnitKerja::pluck('id')->all();
    Shift::create([
      'nama' => 'Pagi',
      'unit_kerja_id' => $unit_kerja_id[array_rand($unit_kerja_id)],
      'jam_from' => '06:00:00',
      'jam_to' => '16:00:00'
    ]);

    Shift::create([
      'nama' => 'Sore',
      'unit_kerja_id' => $unit_kerja_id[array_rand($unit_kerja_id)],
      'jam_from' => '16:00:00',
      'jam_to' => '23:00:00'
    ]);

    Shift::create([
      'nama' => 'Malam',
      'unit_kerja_id' => $unit_kerja_id[array_rand($unit_kerja_id)],
      'jam_from' => '23:00:00',
      'jam_to' => '06:00:00'
    ]);
  }
}
