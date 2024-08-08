<?php

namespace App\Console\Commands;

use Carbon\Carbon;
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
        $currentDate = Carbon::now();
        $jadwalPenggajian = DB::table('jadwal_penggajians')
            ->select('tgl_mulai')
            ->orderBy('tgl_mulai', 'desc')
            ->first();

        if (!$jadwalPenggajian) {
            Log::error('Tidak ada jadwal penggajian yang tersedia.');
            return;
        }

        // Ambil nilai status dari tabel status_gajis
        $statusCreated = DB::table('status_gajis')->where('label', 'Belum Dipublikasi')->value('id');
        $statusPublished = DB::table('status_gajis')->where('label', 'Sudah Dipublikasi')->value('id');

        if (is_null($statusCreated) || is_null($statusPublished)) {
            Log::error('Status gaji tidak ditemukan di tabel status_gajis.');
            return;
        }

        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
        $tgl_mulai = Carbon::create($currentYear, $currentMonth, $jadwalPenggajian->tgl_mulai);
        $tgl_selesai = $tgl_mulai->copy()->addDay(1);

        if ($currentDate->isSameDay($tgl_mulai) || $currentDate->isSameDay($tgl_selesai)) {
            $riwayatPenggajians = RiwayatPenggajian::where('status_gaji_id', $statusCreated)->get();

            foreach ($riwayatPenggajians as $riwayatPenggajian) {
                if ($currentDate->greaterThanOrEqualTo($tgl_mulai) && $currentDate->lessThanOrEqualTo($tgl_selesai)) {
                    DB::beginTransaction();
                    try {
                        $riwayatPenggajian->update(['status_gaji_id' => $statusPublished]);

                        Penggajian::where('riwayat_penggajian_id', $riwayatPenggajian->id)->update(['status_gaji_id' => $statusPublished]);

                        DB::commit();
                        Log::info("Riwayat penggajian ID {$riwayatPenggajian->id} berhasil dipublikasikan otomatis.");
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Gagal publikasi otomatis riwayat penggajian ID {$riwayatPenggajian->id}, Pesan Error: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info('Penggajian berhasil dipublikasikan secara otomatis.');
    }
}
