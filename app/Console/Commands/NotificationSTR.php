<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Notifikasi;
use App\Models\DataKaryawan;
use Illuminate\Console\Command;

class NotificationSTR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-warning-str';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification Warning STR';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now('Asia/Jakarta');
        $oneMonthAhead = $today->copy()->addMonth(7);

        // Ambil karyawan yang masa berlaku STR kurang dari atau sama dengan 7 bulan
        $dataKaryawanList = DataKaryawan::whereHas('users', function ($query) {
            $query->where('data_completion_step', 0)
                ->where('status_aktif', 2);
        })
            ->whereNotNull('masa_berlaku_str')
            ->whereRaw("STR_TO_DATE(masa_berlaku_str, '%d-%m-%Y') <= ?", [$oneMonthAhead->format('Y-m-d')])
            ->whereRaw("STR_TO_DATE(masa_berlaku_str, '%d-%m-%Y') >= ?", [$today->format('Y-m-d')])
            ->get();

        foreach ($dataKaryawanList as $karyawan) {
            $sipExpireDate = Carbon::createFromFormat('d-m-Y', $karyawan->masa_berlaku_str, 'Asia/Jakarta');

            // Selisih bulan antara hari ini dan masa berlaku STR
            $monthsRemaining = $today->diffInMonths($sipExpireDate);

            // Periksa sisa hari untuk penyesuaian
            $daysRemaining = $today->diffInDays($sipExpireDate) % 30;
            if ($daysRemaining > 0) {
                $monthsRemaining += 1; // Tambahkan 1 bulan jika ada sisa hari
            }

            $message = $monthsRemaining > 0
                ? "Peringatan: Masa berlaku STR Anda akan berakhir dalam {$monthsRemaining} bulan."
                : "Peringatan: Masa berlaku STR Anda akan habis bulan ini!";

            // Cek apakah notifikasi sudah pernah dibuat untuk karyawan ini
            $notifikasi = Notifikasi::where('user_id', $karyawan->user_id)
                ->where('kategori_notifikasi_id', 16)
                ->where('message', 'like', '%Peringatan: Masa berlaku STR%')
                ->first();
            if ($notifikasi) {
                // Jika notifikasi sudah ada, update pesan dan status is_read
                $notifikasi->update([
                    'message' => $message,
                    'is_read' => false,
                ]);

                // Hapus notifikasi jika sudah dibaca dan masa berlaku STR sudah habis
                if ($daysRemaining == 0 && $notifikasi->is_read) {
                    $notifikasi->delete();
                }
            } else {
                Notifikasi::create([
                    'kategori_notifikasi_id' => 16,
                    'user_id' => $karyawan->user_id,
                    'message' => $message,
                    'is_read' => false,
                    'is_verifikasi' => false,
                    'created_at' => $today,
                ]);
            }
        }

        $this->info('Peringatan masa berlaku STR berhasil dikirim.');
    }
}
