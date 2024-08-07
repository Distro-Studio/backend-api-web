<?php

namespace Database\Seeders\JadwalKaryawan;

use Carbon\Carbon;
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
        $users = User::where('nama', '!=', 'Super Admin')->pluck('id')->all();

        // Mengatur timezone ke Jakarta
        $timezone = 'Asia/Jakarta';

        // Mendapatkan tanggal pertama dan terakhir minggu ini dengan timezone Jakarta
        $startOfWeek = Carbon::now($timezone)->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::now($timezone)->endOfWeek()->format('Y-m-d');

        foreach ($users as $user_id) {
            $shift_id = $shifts[array_rand($shifts)];
            $jadwal = new Jadwal([
                'user_id' => $user_id,
                'tgl_mulai' => $startOfWeek,
                'tgl_selesai' => $endOfWeek,
                'shift_id' => $shift_id,
            ]);
            $jadwal->save();
        }
    }
}
