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
            $lembur = new Lembur([
                'user_id' => $user_id,
                'jadwal_id' => $jadwal_id,
                'durasi' => '05:00:00',
                'catatan' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
            ]);
            $lembur->save();
        }
    }
}
