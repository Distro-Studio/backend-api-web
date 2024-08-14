<?php

namespace Database\Seeders\JadwalKaryawan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Helpers\RandomHelper;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = Shift::pluck('id')->all();
        $users = User::where('nama', '!=', 'Super Admin')->pluck('id')->all();

        // Mengatur timezone ke Jakarta
        $timezone = 'Asia/Jakarta';

        // Mendapatkan tanggal pertama dan terakhir minggu ini dengan timezone Jakarta
        $startOfWeek = Carbon::now($timezone)->startOfWeek();
        $endOfWeek = Carbon::now($timezone)->endOfWeek();

        foreach ($users as $user_id) {
            $currentDate = $startOfWeek->copy();

            while ($currentDate->lessThanOrEqualTo($endOfWeek)) {
                $shift_id = $shifts[array_rand($shifts)];

                // Tentukan tgl_selesai berdasarkan shift times
                $shift = Shift::find($shift_id);
                $tglSelesai = $currentDate->copy();
                if ($shift) {
                    $jamFrom = Carbon::parse(RandomHelper::convertToTimeString($shift->jam_from));
                    $jamTo = Carbon::parse(RandomHelper::convertToTimeString($shift->jam_to));

                    // Jika jamTo kurang dari jamFrom, artinya shift berakhir di hari berikutnya
                    if ($jamTo->lessThanOrEqualTo($jamFrom)) {
                        $tglSelesai->addDay();
                    }
                } else {
                    $tglSelesai = $currentDate; // When shift_id is null, tgl_selesai is same as tgl_mulai
                }

                // Format tanggal sesuai dengan format d/m/Y
                $tglMulaiFormatted = $currentDate->format('d-m-Y');
                $tglSelesaiFormatted = $tglSelesai->format('d-m-Y');

                // Simpan jadwal
                $jadwal = new Jadwal([
                    'user_id' => $user_id,
                    'tgl_mulai' => $tglMulaiFormatted,
                    'tgl_selesai' => $tglSelesaiFormatted,
                    'shift_id' => $shift_id,
                ]);
                $jadwal->save();

                // Tambahkan 1 hari untuk iterasi berikutnya
                $currentDate->addDay();
            }
        }
    }
}
