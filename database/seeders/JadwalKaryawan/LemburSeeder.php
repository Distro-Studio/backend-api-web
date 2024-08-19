<?php

namespace Database\Seeders\JadwalKaryawan;

use App\Models\Jadwal;
use App\Models\KategoriKompensasi;
use App\Models\User;
use App\Models\Shift;
use App\Models\Lembur;
use App\Models\StatusLembur;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_ids = User::pluck('id')->all();
        $jadwal_ids = Jadwal::pluck('id')->all();
        $kompensasi_lembur_ids = KategoriKompensasi::pluck('id')->all();
        // $status_lembur_ids = StatusLembur::pluck('id')->all();

        for ($i = 0; $i < 20; $i++) {
            if (count($user_ids) <= $i) {
                break;
            }

            $user_id = $user_ids[$i];
            $jadwal_id = $jadwal_ids[array_rand($jadwal_ids)];
            $durasi_mulai = rand(1, 12);
            $durasi_selesai = rand(1, 60);
            $lembur = new Lembur([
                'user_id' => $user_id,
                'jadwal_id' => $jadwal_id,
                'tgl_pengajuan' => date('Y-m-d', rand(mktime(0, 0, 0, 6, 1, 2024), mktime(0, 0, 0, 6, 30, 2024))),
                // 'kompensasi_lembur_id' => $kompensasi_lembur_ids[array_rand($kompensasi_lembur_ids)],
                'durasi' => $durasi_mulai . 'J ' . $durasi_selesai . 'M',
                'catatan' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                // 'status_lembur_id' => $status_lembur_ids[array_rand($status_lembur_ids)],
                'status_lembur_id' => 1,
            ]);
            $lembur->save();
        }
    }
}
