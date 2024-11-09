<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\DataKaryawan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateAutoAlfaPresensi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-auto-alfa-presensi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update kategori presensi menjadi Alfa jika hari telah berganti';

    public function handle()
    {
        $kemarin = Carbon::yesterday('Asia/Jakarta')->format('Y-m-d');
        Log::info("Update kategori presensi untuk tanggal {$kemarin}.");
        // $today = date('2024-11-04');

        // Ambil semua presensi hari ini yang memiliki jam masuk namun belum ada jam keluar
        $presensiHariIni = Presensi::whereNotNull('jam_masuk')
            ->whereNull('jam_keluar')
            ->whereDate('jam_masuk', $kemarin)
            ->get();
        Log::info("Ada '{$presensiHariIni->count()}' presensi yang harus diperbarui.");

        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        foreach ($presensiHariIni as $presensi) {
            if ($presensi->jadwal_id) {
                // Case A: Presensi dengan jadwal_id yang valid
                $jadwal = Jadwal::find($presensi->jadwal_id);

                if ($jadwal && $jadwal->tgl_selesai) {
                    if ($jadwal->tgl_selesai !== $today) {
                        $presensi->update(['kategori_presensi_id' => 4]);
                        Log::info("Presensi ID {$presensi->id} diperbarui menjadi Alfa (Case A).");
                    }
                }
            } else {
                // Case B: Presensi tanpa jadwal_id (karyawan non-shift)
                $jamMasukDate = Carbon::parse($presensi->jam_masuk)->format('Y-m-d');
                if ($jamMasukDate !== $today) {
                    $presensi->update(['kategori_presensi_id' => 4]);
                    Log::info("Presensi ID {$presensi->id} diperbarui menjadi Alfa (Case B).");
                }
            }
        }

        $dataKaryawanIds = Presensi::whereDate('jam_masuk', $kemarin)
            ->where('kategori_presensi_id', 4)
            ->pluck('data_karyawan_id')
            ->unique();

        foreach ($dataKaryawanIds as $dataKaryawanId) {
            DataKaryawan::where('id', $dataKaryawanId)->update(['status_reward_presensi' => false]);
            Log::info("Status reward presensi karyawan ID {$dataKaryawanId} diperbarui menjadi false.");
        }
    }
}
