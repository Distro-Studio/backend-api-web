<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Notifikasi;
use App\Models\Penggajian;
use Illuminate\Console\Command;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAutoPublishPenggajian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-auto-publish-penggajian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembaharuan otomatis terhadap status riwayat penggajian';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jadwalPenggajian = DB::table('jadwal_penggajians')
            ->select(DB::raw("CAST(tgl_mulai AS UNSIGNED) as tgl_mulai")) // Memastikan tgl_mulai adalah integer
            ->where('id', 1)
            ->first();

        if (!$jadwalPenggajian) {
            Log::error('Tidak ada jadwal penggajian yang tersedia.');
            return;
        }

        // Ambil nilai status dari tabel status_gajis
        $statusCreated = DB::table('status_gajis')->where('label', 'Belum Dipublikasi')->value('id');
        $statusPublished = DB::table('status_gajis')->where('label', 'Sudah Dipublikasi')->value('id');

        $currentDate = Carbon::now('Asia/Jakarta');
        // Log::info("Tanggal sekarang {$currentDate}");
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
        $tgl_mulai = Carbon::create($currentYear, $currentMonth, $jadwalPenggajian->tgl_mulai);
        // Log::info("Tgl Mulai: {$tgl_mulai}");
        $tgl_selesai = $tgl_mulai->copy()->addDay(1);

        if ($currentDate->isSameDay($tgl_mulai) || $currentDate->isSameDay($tgl_selesai)) {
            $riwayatPenggajians = RiwayatPenggajian::where('status_gaji_id', $statusCreated)->get();

            foreach ($riwayatPenggajians as $riwayatPenggajian) {
                if ($currentDate->greaterThanOrEqualTo($tgl_mulai) && $currentDate->lessThanOrEqualTo($tgl_selesai)) {
                    DB::beginTransaction();
                    try {
                        $riwayatPenggajian->update(['status_gaji_id' => $statusPublished]);

                        Penggajian::where('riwayat_penggajian_id', $riwayatPenggajian->id)->update(['status_gaji_id' => $statusPublished]);

                        $penggajians = Penggajian::where('riwayat_penggajian_id', $riwayatPenggajian->id)
                            ->where('status_gaji_id', $statusPublished)
                            ->get();
                        $totalTakeHomePay = $penggajians->sum('take_home_pay');
                        $riwayatPenggajian->update([
                            'periode_gaji_karyawan' => $totalTakeHomePay
                        ]);

                        DB::commit();
                        Log::info("Riwayat penggajian ID {$riwayatPenggajian->id} berhasil dipublikasikan otomatis.");

                        // Kirim notifikasi ke user terkait
                        $penggajians = Penggajian::where('riwayat_penggajian_id', $riwayatPenggajian->id)->get();
                        foreach ($penggajians as $penggajian) {
                            $this->createNotifikasiPenggajianPublish($penggajian, $currentDate->locale('id')->isoFormat('MMMM Y'));
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Gagal publikasi otomatis riwayat penggajian ID {$riwayatPenggajian->id}, Pesan Error: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info('Penggajian berhasil dipublikasikan secara otomatis.');
    }

    private function createNotifikasiPenggajianPublish($penggajian, $periode)
    {
        // Pastikan penggajian memiliki relasi yang valid ke data_karyawans
        if ($penggajian->data_karyawans && $penggajian->data_karyawans->users) {
            $user = $penggajian->data_karyawans->users;

            $message = "Penggajian untuk periode {$periode} telah dipublikasikan. Silakan cek slip gaji Anda.";
            $messageSuperAdmin = "Notifikasi untuk Super Admin: Penggajian untuk karyawan pada periode {$periode} telah dipublikasikan.";

            $userIds = [$user->id];

            foreach ($userIds as $userId) {
                $messageToSend = $userId === 1 ? $messageSuperAdmin : $message;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 5,
                    'user_id' => $userId,
                    'message' => $messageToSend,
                    'is_read' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        }
    }
}
