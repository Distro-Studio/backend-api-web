<?php

namespace Database\Seeders\JadwalKaryawan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\TukarJadwal;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TukarJadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::pluck('id')->all();
        $jadwals = Jadwal::pluck('id')->all();
        for ($i = 0; $i < 20; $i++) {
            $user_id = $users[array_rand($users)];
            $jadwal_id = $jadwals[array_rand($jadwals)];
            $tukar_jadwal = new TukarJadwal([
                'user_pengajuan' => $user_id,
                'jadwal_pengajuan' => $jadwal_id,
                'user_ditukar' => $user_id,
                'jadwal_ditukar' => $jadwal_id,
                'status_penukaran' => rand(0, 1),
            ]);
            $tukar_jadwal->save();
        }
    }
}
