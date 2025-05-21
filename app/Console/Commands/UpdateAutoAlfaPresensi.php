<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\DataKaryawan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

        // Ini yg lama
        // $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        // foreach ($presensiHariIni as $presensi) {
        //     if ($presensi->jadwal_id) {
        //         // Case A: Presensi dengan jadwal_id yang valid
        //         $jadwal = Jadwal::find($presensi->jadwal_id);

        //         if ($jadwal && $jadwal->tgl_selesai) {
        //             if ($jadwal->tgl_selesai !== $today) {
        //                 $presensi->update(['kategori_presensi_id' => 4]);
        //                 Log::info("Presensi ID {$presensi->id} diperbarui menjadi Alfa (Case A).");
        //             }
        //         }
        //     } else {
        //         // Case B: Presensi tanpa jadwal_id (karyawan non-shift)
        //         $jamMasukDate = Carbon::parse($presensi->jam_masuk)->format('Y-m-d');
        //         if ($jamMasukDate !== $today) {
        //             $presensi->update(['kategori_presensi_id' => 4]);
        //             Log::info("Presensi ID {$presensi->id} diperbarui menjadi Alfa (Case B).");
        //         }
        //     }
        // }

        // Ini yang baru (update untuk estimasi jam keluar + 2 jam)
        $today = Carbon::now('Asia/Jakarta');
        foreach ($presensiHariIni as $presensi) {
            $updateAlfa = false;
            if ($presensi->jadwal_id) {
                // Case A: Presensi dengan jadwal_id yang valid
                $jadwal = Jadwal::with('shifts')->find($presensi->jadwal_id);

                // Update untuk kondisi jam 00.00
                if ($jadwal && $jadwal->tgl_selesai) {
                    if ($jadwal->tgl_selesai !== $today->format('Y-m-d')) {
                        // Cek shift jam_to + 2 jam
                        if ($jadwal->shifts && $jadwal->shifts->jam_to) {
                            $jamTo = Carbon::parse("{$kemarin} {$jadwal->shifts->jam_to}", 'Asia/Jakarta')->addHours(2);
                            if ($today->greaterThan($jamTo)) {
                                $updateAlfa = true;
                                Log::info("Presensi ID {$presensi->id} melebihi batas jam_to + 2 jam (Case A).");
                            } else {
                                Log::info("Presensi ID {$presensi->id} belum melebihi batas jam_to + 2 jam (Case A).");
                            }
                        } else {
                            // Jika tidak ada shift atau jam_to, langsung update
                            $updateAlfa = true;
                        }
                    }
                }
            } else {
                // Case B: Presensi tanpa jadwal_id (karyawan non-shift)
                $jamMasukDate = Carbon::parse($presensi->jam_masuk)->format('Y-m-d');
                if ($jamMasukDate !== $today->format('Y-m-d')) {
                    $updateAlfa = true;
                    Log::info("Presensi ID {$presensi->id} diperbarui menjadi Alfa (Case B).");
                }
            }

            if ($updateAlfa) {
                $presensi->update(['kategori_presensi_id' => 4]);
            }
        }

        $dataKaryawanIds = Presensi::whereDate('jam_masuk', $kemarin)
            ->where('kategori_presensi_id', 4)
            ->pluck('data_karyawan_id')
            ->unique();

        $now = Carbon::now('Asia/Jakarta');

        foreach ($dataKaryawanIds as $dataKaryawanId) {
            // Cek ada tidaknya riwayat penggajian bulan ini untuk karyawan ini
            $gajiBulanIni = DB::table('riwayat_penggajians')
                ->whereYear('periode', $now->year)
                ->whereMonth('periode', $now->month)
                ->exists();
            if ($gajiBulanIni) {
                // Jika ada gaji bulan ini, update di data_karyawans
                DB::table('data_karyawans')
                    ->where('id', $dataKaryawanId)
                    ->update(['status_reward_presensi' => false]);
                Log::info("Status reward presensi karyawan ID {$dataKaryawanId} diperbarui menjadi false di data_karyawans.");
            } else {
                // Jika tidak ada, update di reward_bulan_lalus
                DB::table('reward_bulan_lalus')
                    ->where('data_karyawan_id', $dataKaryawanId)
                    ->update(['status_reward' => false]);
                Log::info("Status reward bulan lalu karyawan ID {$dataKaryawanId} diperbarui menjadi false di reward_bulan_lalus.");
            }

            // Insert ke riwayat_pembatalan_rewards
            $presensi = Presensi::where('data_karyawan_id', $dataKaryawanId)
                ->whereDate('jam_masuk', $kemarin)
                ->where('kategori_presensi_id', 4)
                ->first();

            try {
                DB::table('riwayat_pembatalan_rewards')->insert([
                    'data_karyawan_id' => $dataKaryawanId,
                    'tipe_pembatalan' => 'presensi',
                    'tgl_pembatalan' => now('Asia/Jakarta'),
                    'keterangan' => "Update presensi alfa untuk pembatalan reward presensi otomatis",
                    'presensi_id' => $presensi ? $presensi->id : null,
                    'verifikator_1' => 1,
                    'created_at' => now('Asia/Jakarta'),
                    'updated_at' => now('Asia/Jakarta'),
                ]);
                Log::info("Riwayat pembatalan reward presensi dibuat untuk karyawan ID {$dataKaryawanId}.");
            } catch (\Exception $e) {
                Log::error("Gagal membuat riwayat pembatalan reward untuk karyawan ID {$dataKaryawanId}: " . $e->getMessage());
            }
        }
    }
}
