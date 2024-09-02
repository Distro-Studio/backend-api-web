<?php

namespace Database\Seeders\JadwalKaryawan;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\StatusRiwayatIzin;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PerizinanSeeder extends Seeder
{
    public function run()
    {
        $user_id = User::pluck('id')->all();
        $status_izin_id = StatusRiwayatIzin::pluck('id')->all();

        for ($i = 1; $i <= 15; $i++) {
            DB::table('riwayat_izins')->insert([
                'user_id' => $user_id[array_rand($user_id)],
                'durasi' => rand(200, 360),
                'keterangan' => 'Keterangan izin ' . $i,
                'status_izin_id' => $status_izin_id[array_rand($status_izin_id)],
                'tgl_izin' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'waktu_izin' => Carbon::createFromTime(rand(0, 23), rand(0, 59), rand(0, 59))->format('H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
