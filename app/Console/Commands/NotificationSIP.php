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
        $oneMonthAhead = $today->copy()->addMonth(3);

        // Ambil karyawan yang masa berlaku SIP kurang dari atau sama dengan 3 bulan
        $dataKaryawanList = DataKaryawan::whereHas('users', function ($query) {
            $query->where('data_completion_step', 0)
                ->where('status_aktif', 2);
        })
            ->whereNotNull('masa_berlaku_sip')
            ->whereRaw("STR_TO_DATE(masa_berlaku_sip, '%d-%m-%Y') <= ?", [$oneMonthAhead->format('Y-m-d')])
            ->whereRaw("STR_TO_DATE(masa_berlaku_sip, '%d-%m-%Y') >= ?", [$today->format('Y-m-d')])
            ->get();

        foreach ($dataKaryawanList as $karyawan) {
            $sipExpireDate = Carbon::createFromFormat('d-m-Y', $karyawan->masa_berlaku_sip, 'Asia/Jakarta');

            // Selisih bulan antara hari ini dan masa berlaku SIP
            $monthsRemaining = $today->diffInMonths($sipExpireDate);

            // Periksa sisa hari untuk penyesuaian
            $daysRemaining = $today->diffInDays($sipExpireDate) % 30;
            if ($daysRemaining > 0) {
                $monthsRemaining += 1; // Tambahkan 1 bulan jika ada sisa hari
            }

            $message = $monthsRemaining > 0
                ? "Peringatan: Masa berlaku SIP Anda akan berakhir dalam {$monthsRemaining} bulan."
                : "Peringatan: Masa berlaku SIP Anda akan habis bulan ini!";

            // Cek apakah notifikasi sudah pernah dibuat untuk karyawan ini
            $notifikasi = Notifikasi::where('user_id', $karyawan->user_id)
                ->where('kategori_notifikasi_id', 15)
                ->where('message', 'like', '%Peringatan: Masa berlaku SIP%')
                ->first();
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
