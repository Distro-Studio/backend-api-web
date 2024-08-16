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
    // public function run(): void
    // {
    //     $shifts = Shift::pluck('id')->all();
    //     $users = User::where('nama', '!=', 'Super Admin')->pluck('id')->all();

    //     // Mengatur timezone ke Jakarta
    //     $timezone = 'Asia/Jakarta';

    //     // Mendapatkan tanggal pertama dan terakhir minggu ini dengan timezone Jakarta
    //     $startOfWeek = Carbon::now($timezone)->startOfWeek();
    //     $endOfWeek = Carbon::now($timezone)->endOfWeek();

    //     foreach ($users as $user_id) {
    //         $currentDate = $startOfWeek->copy();

    //         while ($currentDate->lessThanOrEqualTo($endOfWeek)) {
    //             $shift_id = $shifts[array_rand($shifts)];

    //             // Tentukan tgl_selesai berdasarkan shift times
    //             $shift = Shift::find($shift_id);
    //             $tglSelesai = $currentDate->copy();
    //             if ($shift) {
    //                 $jamFrom = Carbon::parse($shift->jam_from);
    //                 $jamTo = Carbon::parse($shift->jam_to);

    //                 // Jika jamTo kurang dari jamFrom, artinya shift berakhir di hari berikutnya
    //                 if ($jamTo->lessThanOrEqualTo($jamFrom)) {
    //                     $tglSelesai->addDay();
    //                 }
    //             } else {
    //                 $tglSelesai = $currentDate; // When shift_id is null, tgl_selesai is same as tgl_mulai
    //             }

    //             // Format tanggal sesuai dengan format d/m/Y
    //             $tglMulaiFormatted = $currentDate->format('Y-m-d');
    //             $tglSelesaiFormatted = $tglSelesai->format('Y-m-d');;

    //             // Simpan jadwal
    //             $jadwal = new Jadwal([
    //                 'user_id' => $user_id,
    //                 'tgl_mulai' => $tglMulaiFormatted,
    //                 'tgl_selesai' => $tglSelesaiFormatted,
    //                 'shift_id' => $shift_id,
    //             ]);
    //             $jadwal->save();

    //             // Tambahkan 1 hari untuk iterasi berikutnya
    //             $currentDate->addDay();
    //         }
    //     }
    // }

    public function run(): void
    {
        // Mengambil pengguna yang bekerja dengan shift
        $userShift = User::whereHas('data_karyawans.unit_kerjas', function ($query) {
            $query->where('jenis_karyawan', 1); // 1 = shift
        })->where('nama', '!=', 'Super Admin')->get();

        // Mengambil semua shift yang tersedia
        $shifts = Shift::pluck('id')->all();

        // Mengatur timezone ke Jakarta
        $timezone = 'Asia/Jakarta';

        // Mendapatkan tanggal pertama dan terakhir minggu ini dengan timezone Jakarta
        $startOfWeek = Carbon::now($timezone)->startOfWeek();
        $endOfWeek = Carbon::now($timezone)->endOfWeek();

        foreach ($userShift as $user) {
            $currentDate = $startOfWeek->copy();

            while ($currentDate->lessThanOrEqualTo($endOfWeek)) {
                // Randomly decide if today is a working day or a day off
                if (rand(0, 1) === 1) {
                    // Work day
                    $shift_id = $shifts[array_rand($shifts)];

                    // Dapatkan shift times
                    $shift = Shift::find($shift_id);
                    $tglMulai = $currentDate->copy();
                    $tglSelesai = $currentDate->copy();

                    if ($shift) {
                        $jamFrom = Carbon::parse($shift->jam_from);
                        $jamTo = Carbon::parse($shift->jam_to);

                        // Jika shift berakhir pada hari berikutnya
                        if ($jamTo->lessThanOrEqualTo($jamFrom)) {
                            $tglSelesai->addDay();
                        }

                        // Simpan jadwal
                        Jadwal::create([
                            'user_id' => $user->id,
                            'tgl_mulai' => $tglMulai->format('Y-m-d'),
                            'tgl_selesai' => $tglSelesai->format('Y-m-d'),
                            'shift_id' => $shift_id,
                        ]);
                    }
                } else {
                    // Day off
                    Jadwal::create([
                        'user_id' => $user->id,
                        'tgl_mulai' => $currentDate->format('Y-m-d'),
                        'tgl_selesai' => $currentDate->format('Y-m-d'),
                        'shift_id' => 0, // Shift ID 0 for day off
                    ]);
                }

                // Tambahkan 1 hari untuk iterasi berikutnya
                $currentDate->addDay();
            }
        }
    }
}
