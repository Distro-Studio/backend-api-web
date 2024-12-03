<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\DataKaryawan;
use App\Models\RewardbulanLalu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAndResetReward extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-and-reset-reward-presensi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis update dan reset status reward';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::transaction(function () {
                $data_karyawan_ids = DataKaryawan::where('id', '!=', 1)
                    ->whereHas('users', function ($query) {
                        $query->where('status_aktif', 2);
                    })
                    ->pluck('id')
                    ->toArray();

                // Step 1: Cek tabel reward_bulan_lalus
                $dataKaryawans = DataKaryawan::whereIn('id', $data_karyawan_ids)->get();

                foreach ($dataKaryawans as $dataKaryawan) {
                    $existingReward = RewardbulanLalu::where('data_karyawan_id', $dataKaryawan->id)->first();
                    $statusReward = $dataKaryawan->status_reward_presensi;

                    if ($existingReward) {
                        // Jika sudah ada, lakukan update
                        $existingReward->update(['status_reward' => $statusReward]);
                    } else {
                        // Jika belum ada, create data baru
                        RewardbulanLalu::create([
                            'data_karyawan_id' => $dataKaryawan->id,
                            'status_reward' => $statusReward,
                        ]);
                    }
                }

                // Step 2: Ambil id dari karyawan dengan cuti aktif pada bulan ini
                $karyawanDenganCutiIds = $this->getKaryawanDenganCuti();

                // Step 3: Update `status_reward_presensi` menjadi true, kecuali untuk karyawan dengan cuti
                DB::table('data_karyawans')
                    ->whereIn('id', $data_karyawan_ids)
                    ->whereNotIn('id', $karyawanDenganCutiIds)
                    ->update(['status_reward_presensi' => true]);

                Log::info('Update status_reward_presensi berhasil untuk karyawan: ' . implode(', ', $data_karyawan_ids));
            });

            $this->info('Status reward telah di-reset dan diperiksa.');
        } catch (\Exception $e) {
            // Jika terjadi error, rollback dan log errornya
            Log::error('Terjadi kesalahan saat melakukan reset dan cek reward: ' . $e->getMessage());
            $this->error('Terjadi kesalahan. Proses dibatalkan.');
        }
    }

    private function getKaryawanDenganCuti(): array
    {
        $currentMonth = Carbon::now('Asia/Jakarta')->month;
        $currentYear = Carbon::now('Asia/Jakarta')->year;
        $startOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m-d');
        $endOfMonth = Carbon::createFromDate($currentYear, $currentMonth, Carbon::now()->daysInMonth)->format('Y-m-d');

        $karyawanDenganCutiUserIds = Cuti::where('status_cuti_id', 4)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereRaw('STR_TO_DATE(tgl_from, "%d-%m-%Y") <= ?', [$endOfMonth])
                    ->whereRaw('STR_TO_DATE(tgl_to, "%d-%m-%Y") >= ?', [$startOfMonth]);
            })
            ->pluck('user_id')
            ->toArray();

        return DataKaryawan::whereIn('user_id', $karyawanDenganCutiUserIds)
            ->pluck('id')
            ->toArray();
    }
}
