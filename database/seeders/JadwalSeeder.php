<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_ids = User::pluck('id')->all();
        $shift_ids = Shift::pluck('id')->all();

        for ($i = 0; $i < 30; $i++) {
            if (count($user_ids) <= $i) {
                break; // break if we run out of unique user_ids
            }

            $user_id = $user_ids[$i];
            $shift_id = $shift_ids[array_rand($shift_ids)];

            $jadwal = new Jadwal([
                'user_id' => $user_id,
                'tanggal_mulai' => date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2024), mktime(0, 0, 0, 3, 31, 2024))),
                'tanggal_selesai' => date('Y-m-d', rand(mktime(0, 0, 0, 3, 31, 2024), mktime(0, 0, 0, 5, 31, 2024))),
                'shift_id' => $shift_id,
            ]);

            $jadwal->save();
        }
    }
}
