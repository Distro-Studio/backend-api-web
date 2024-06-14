<?php

namespace Database\Seeders\JadwalKaryawan;

use App\Models\User;
use App\Models\Shift;
use App\Models\Lembur;
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
        $shift_ids = Shift::pluck('id')->all();
        $kompensasi = ['Lembur Dibayar', 'Lembur Tidak Dibayar', 'Lembur Mandiri'];
        $tipe = [
            'Cuti Tahunan',
            'Cuti Sakit',
            'Cuti Melahirkan',
            'Cuti Kematian',
            'Cuti Nikah',
        ];

        for ($i = 0; $i < 20; $i++) {
            if (count($user_ids) <= $i) {
                break;
            }

            $user_id = $user_ids[$i];
            $shift_id = $shift_ids[array_rand($shift_ids)];
            $durasi_mulai = rand(1, 12);
            $durasi_selesai = rand(1, 60);
            $lembur = new Lembur([
                'user_id' => $user_id,
                'shift_id' => $shift_id,
                'tgl_pengajuan' => date('Y-m-d', rand(mktime(0, 0, 0, 6, 1, 2024), mktime(0, 0, 0, 6, 30, 2024))),
                'kompensasi' => $kompensasi[array_rand($kompensasi)],
                'tipe' => $tipe[array_rand($tipe)],
                'durasi' => $durasi_mulai . 'J ' . $durasi_selesai . 'M',
                'catatan' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                // 'status_lembur' => 0, // bisa juga false atau 1, karena lembur hanya yang buat admin
            ]);
            $lembur->save();
        }
    }
}
