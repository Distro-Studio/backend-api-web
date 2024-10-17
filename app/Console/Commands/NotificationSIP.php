<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Notifikasi;
use App\Models\DataKaryawan;
use Illuminate\Console\Command;

class NotificationSIP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-warning-sip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification Warning SIP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now('Asia/Jakarta');
        $oneWeekAhead = $today->copy()->addDays(7);

        // Ambil karyawan yang masa berlaku SIP kurang dari atau sama dengan 7 hari
        $dataKaryawanList = DataKaryawan::whereHas('users', function ($query) {
            $query->where('data_completion_step', 0)
                ->where('status_aktif', 2);
        })
            ->whereRaw("STR_TO_DATE(masa_berlaku_sip, '%d-%m-%Y') <= ?", [$oneWeekAhead->format('Y-m-d')])
            ->whereRaw("STR_TO_DATE(masa_berlaku_sip, '%d-%m-%Y') >= ?", [$today->format('Y-m-d')])
            ->get();

        foreach ($dataKaryawanList as $karyawan) {
            $sipExpireDate = Carbon::createFromFormat('d-m-Y', $karyawan->masa_berlaku_sip, 'Asia/Jakarta');
            $daysRemaining = $today->diffInDays($sipExpireDate);

            // Cek apakah notifikasi sudah pernah dibuat untuk karyawan ini
            $notifikasi = Notifikasi::where('user_id', $karyawan->user_id)
                ->where('kategori_notifikasi_id', 15)
                ->where('message', 'like', '%Masa berlaku SIP%')
                ->first();

            if ($daysRemaining > 0) {
                $message = "Peringatan: Masa berlaku SIP Anda akan berakhir dalam {$daysRemaining} hari.";
            } else {
                $message = "Peringatan: Masa berlaku SIP Anda akan habis hari ini!";
            }

            if ($notifikasi) {
                // Jika notifikasi sudah ada, update pesan dan status is_read
                $notifikasi->update([
                    'message' => $message,
                    'is_read' => false,
                ]);

                // Hapus notifikasi jika sudah dibaca dan masa berlaku SIP sudah habis
                if ($daysRemaining == 0 && $notifikasi->is_read) {
                    $notifikasi->delete();
                }
            } else {
                Notifikasi::create([
                    'kategori_notifikasi_id' => 15,
                    'user_id' => $karyawan->user_id,
                    'message' => $message,
                    'is_read' => false,
                    'is_verifikasi' => false,
                    'created_at' => $today,
                ]);
            }
        }

        $this->info('Peringatan masa berlaku SIP berhasil dikirim.');
    }
}
