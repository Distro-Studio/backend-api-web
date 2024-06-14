<?php

namespace Database\Seeders\JadwalKaryawan;

use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = Shift::pluck('id')->all();
        for ($i = 0; $i < 10; $i++) {
            $user_id = rand(1, 5);
            $shift_id = $shifts[array_rand($shifts)];
            $jadwal = new Jadwal([
                'user_id' => $user_id,
                'tgl_mulai' => date('Y-m-d', rand(mktime(0, 0, 0, 6, 15, 2024), mktime(0, 0, 0, 6, 20, 2024))),
                'tgl_selesai' => date('Y-m-d', rand(mktime(0, 0, 0, 6, 30, 2024), mktime(0, 0, 0, 7, 5, 2024))),
                'shift_id' => $shift_id,
            ]);
            $jadwal->save();
        }
    }
}
