<?php

namespace Database\Seeders\Karyawan;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use Illuminate\Database\Seeder;
use App\Models\TransferKaryawan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransferKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unit_kerja_ids = UnitKerja::pluck('id')->all();
        $jabatan_ids = Jabatan::pluck('id')->all();
        $user_ids = User::pluck('id')->all();
        $tipe = ['Transfer Unit Kerja', 'Kenaikan Jabatan'];
        for ($i = 0; $i < 15; $i++) {
            if (count($user_ids) <= $i) {
                break; // break if we run out of unique user_ids
            }

            $user_id = $user_ids[$i];
            $unit_kerja_asal = $unit_kerja_ids[array_rand($unit_kerja_ids)];
            $unit_kerja_tujuan = $unit_kerja_ids[array_rand($unit_kerja_ids)];

            // Ensure unit_kerja_asal and unit_kerja_tujuan are different
            while ($unit_kerja_tujuan == $unit_kerja_asal) {
                $unit_kerja_tujuan = $unit_kerja_ids[array_rand($unit_kerja_ids)];
            }

            $jabatan_asal = $jabatan_ids[array_rand($jabatan_ids)];
            $jabatan_tujuan = $jabatan_ids[array_rand($jabatan_ids)];

            // Ensure jabatan_asal and jabatan_tujuan are different
            while ($jabatan_tujuan == $jabatan_asal) {
                $jabatan_tujuan = $jabatan_ids[array_rand($jabatan_ids)];
            }

            $dataTransfer = new TransferKaryawan([
                'user_id' => $user_id,
                'tanggal_mulai' => date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2024), mktime(0, 0, 0, 12, 31, 2024))),
                'unit_kerja_asal' => $unit_kerja_asal,
                'unit_kerja_tujuan' => $unit_kerja_tujuan,
                'jabatan_asal' => $jabatan_asal,
                'jabatan_tujuan' => $jabatan_tujuan,
                'tipe' => $tipe[array_rand($tipe)],
                'alasan' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!',
            ]);
            $dataTransfer->save();
        }
    }
}
