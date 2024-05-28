<?php

namespace App\Console\Commands;

use App\Models\DataKaryawan;
use Illuminate\Console\Command;
use App\Models\TransferKaryawan;

class UpdateDataKaryawanTransfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-data-karyawan-transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembaharuan rutin data karyawan setelah transfer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now();
        $tomorrow = $today->copy()->addDay()->startOfDay();

        // Ambil transfer yang tanggal mulainya kurang dari atau sama dengan H-1
        $transfers = TransferKaryawan::where(function ($query) use ($today, $tomorrow) {
            $query->whereDate('tanggal_mulai', '<', $today)
                ->orWhereDate('tanggal_mulai', '=', $tomorrow->format('Y-m-d'));
        })->get();

        foreach ($transfers as $transfer) {
            DataKaryawan::where('user_id', $transfer->user_id)
                ->update([
                    'unit_kerja_id' => $transfer->unit_kerja_tujuan,
                    'jabatan_id' => $transfer->jabatan_tujuan,
                ]);
        }

        $this->info('Data karyawan berhasil diperbarui untuk yang tanggal mulainya H-1 atau sudah terlewat.');
    }
}
