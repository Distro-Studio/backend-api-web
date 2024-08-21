<?php

namespace App\Console\Commands;

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
                // Ambil daftar data_karyawan_id yang memenuhi kriteria
                $data_karyawan_ids = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')
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

                // Step 2: Reset kolom status_reward_presensi pada tabel data_karyawans menjadi true
                DB::table('data_karyawans')
                    ->whereIn('id', $data_karyawan_ids)
                    ->update(['status_reward_presensi' => true]);
            });

            $this->info('Status reward telah di-reset dan diperiksa.');
        } catch (\Exception $e) {
            // Jika terjadi error, rollback dan log errornya
            Log::error('Terjadi kesalahan saat melakukan reset dan cek reward: ' . $e->getMessage());
            $this->error('Terjadi kesalahan. Proses dibatalkan.');
        }
    }
}
