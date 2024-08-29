<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\DataKaryawan;
use Illuminate\Console\Command;
use App\Models\TransferKaryawan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

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
        $today = Carbon::now('Asia/Jakarta');
        $tomorrow = $today->copy()->addDay()->startOfDay();

        // Ambil transfer yang tanggal mulainya kurang dari atau sama dengan H-1
        $transfers = TransferKaryawan::where(function ($query) use ($today, $tomorrow) {
            $query->whereDate('tgl_mulai', '<', $today->format('d-m-Y'))
                ->orWhereDate('tgl_mulai', '=', $tomorrow->format('d-m-Y'));
        })
            ->where('is_processed', 0) // Hanya ambil yang belum diproses
            ->get();
        // Log::info("Ada {$transfers->count()} transfer yang harus diperbarui.");

        foreach ($transfers as $transfer) {
            $updateData = [];

            if (!empty($transfer->unit_kerja_tujuan)) {
                $updateData['unit_kerja_id'] = $transfer->unit_kerja_tujuan;
            }

            if (!empty($transfer->jabatan_tujuan)) {
                $updateData['jabatan_id'] = $transfer->jabatan_tujuan;
            }

            if (!empty($transfer->kelompok_gaji_tujuan)) {
                $updateData['kelompok_gaji_id'] = $transfer->kelompok_gaji_tujuan;
            }

            if (!empty($updateData)) {
                DataKaryawan::where('user_id', $transfer->user_id)
                    ->update($updateData);
            }

            if (!empty($transfer->role_tujuan)) {
                $user = User::find($transfer->user_id);

                if ($user) {
                    // Update role_id di tabel users
                    $user->update(['role_id' => $transfer->role_tujuan]);

                    // Assign role baru ke user
                    $role = Role::find($transfer->role_tujuan);
                    if ($role) {
                        $user->syncRoles([$role->name]);
                    } else {
                        Log::warning("Role dengan ID {$transfer->role_tujuan} tidak ditemukan untuk user_id {$transfer->user_id}.");
                    }
                } else {
                    Log::warning("User dengan ID {$transfer->user_id} tidak ditemukan saat mencoba memperbarui role.");
                }
            }
            $transfer->update(['is_processed' => 1]);
        }

        $this->info('Data karyawan berhasil diperbarui pada tanggal mulainya H-1 atau sudah terlewat.');
    }
}
