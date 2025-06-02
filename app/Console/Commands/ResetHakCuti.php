<?php

namespace App\Console\Commands;

use App\Models\HakCuti;
use App\Models\TipeCuti;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetHakCuti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-hak-cuti';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Hak Cuti Karyawan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now('Asia/Jakarta');

        // Setting log channel khusus untuk reset hak cuti
        $loggerTahunan = Log::channel('reset_hak_cuti_tahunan');
        $loggerBesar = Log::channel('reset_hak_cuti_besar');
        $loggerLainnya = Log::channel('reset_hak_cuti_lainnya');

        // Ambil tipe cuti
        $tipeCutiTahunan = TipeCuti::where('id', 1)->first();
        $tipeCutiBesar = TipeCuti::where('id', 5)->first();
        $tipeCutiLain = TipeCuti::where('is_unlimited', 0)->whereNotIn('id', [1, 5])->get();

        if (!$tipeCutiTahunan || !$tipeCutiBesar) {
            $this->info('Tipe cuti tahunan atau cuti besar tidak ditemukan.');
            return;
        }

        if ($tipeCutiLain->isEmpty()) {
            $this->info('Tipe cuti lain tidak ditemukan.');
            return;
        }

        // --- Proses Cuti Tahunan ---
        $hakCutisTahunan = HakCuti::where('tipe_cuti_id', $tipeCutiTahunan->id)->get();
        foreach ($hakCutisTahunan as $hakCuti) {
            $karyawan = $hakCuti->data_karyawans;
            if (!$karyawan || !$karyawan->tgl_masuk) {
                $this->warn("| Hak Cuti Tahunan | - data karyawan atau tanggal masuk tidak lengkap untuk hak cuti ID {$hakCuti->id}");
                $loggerTahunan->warning("| Hak Cuti Tahunan | - data karyawan atau tanggal masuk tidak lengkap untuk hak cuti ID {$hakCuti->id}");
                continue;
            }

            // Parsing tanggal masuk format d-m-Y
            try {
                $tglMasuk = Carbon::createFromFormat('d-m-Y', $karyawan->tgl_masuk);
            } catch (\Exception $e) {
                $this->warn("| Hak Cuti Tahunan | - format tanggal masuk salah untuk karyawan ID {$karyawan->id}");
                $loggerTahunan->warning("| Hak Cuti Tahunan | - format tanggal masuk salah untuk karyawan ID {$karyawan->id}");
                continue;
            }

            // Hitung selisih bulan masa kerja
            $diffMonths = $now->diffInMonths($tglMasuk);

            // Cek sudah minimal 12 bulan
            if ($diffMonths < 12) {
                $this->info("| Hak Cuti Tahunan | - karyawan ID {$karyawan->id} belum 1 tahun kerja, belum dapat cuti tahunan.");
                $loggerTahunan->info("| Hak Cuti Tahunan | - karyawan ID {$karyawan->id} belum 1 tahun kerja, belum dapat cuti tahunan.");
                continue;
            }

            // Cek last_reset
            $lastReset = $hakCuti->last_reset ? Carbon::parse($hakCuti->last_reset) : null;
            if ($lastReset && $lastReset->diffInMonths($now) < 12) {
                $this->info("| Hak Cuti Tahunan | - ID {$hakCuti->id} untuk karyawan ID {$karyawan->id} sudah direset kurang dari 1 tahun yang lalu. Skip.");
                $loggerTahunan->info("| Hak Cuti Tahunan | - ID {$hakCuti->id} untuk karyawan ID {$karyawan->id} sudah direset kurang dari 1 tahun yang lalu. Skip.");
                continue;
            }

            // Kuota dihitung dari bulan masuk (bulan keberapa), diambil dari bulan masuk dari Januari (bulan 1)
            // Contoh: masuk Februari (bulan 2) => kuota 12 - 2 = 10
            $kuotaBaru = 12 - $tglMasuk->month;
            if ($kuotaBaru < 0) $kuotaBaru = 0;

            $hakCuti->kuota = $kuotaBaru;
            $hakCuti->used_kuota = 0;
            $hakCuti->last_reset = $now;
            $hakCuti->save();

            $this->info("| Hak Cuti Tahunan | - karyawan ID {$karyawan->id} berhasil diupdate: kuota {$kuotaBaru}");
            $loggerTahunan->info("Reset | Hak Cuti Tahunan | - karyawan ID {$karyawan->id}: kuota={$kuotaBaru}, used_kuota=0");
        }

        // Proses Cuti Besar
        $hakCutisBesar = HakCuti::where('tipe_cuti_id', $tipeCutiBesar->id)->get();
        foreach ($hakCutisBesar as $hakCuti) {
            $karyawan = $hakCuti->data_karyawans;
            if (!$karyawan || !$karyawan->tgl_masuk) {
                $this->warn("| Hak Cuti Besar | - data karyawan atau tanggal masuk tidak lengkap untuk hak cuti ID {$hakCuti->id}");
                $loggerBesar->warning("| Hak Cuti Besar | - data karyawan atau tanggal masuk tidak lengkap untuk hak cuti ID {$hakCuti->id}");
                continue;
            }

            try {
                $tglMasuk = Carbon::createFromFormat('d-m-Y', $karyawan->tgl_masuk);
            } catch (\Exception $e) {
                $this->warn("| Hak Cuti Besar | - format tanggal masuk salah untuk karyawan ID {$karyawan->id}");
                $loggerBesar->warning("| Hak Cuti Besar | - format tanggal masuk salah untuk karyawan ID {$karyawan->id}");
                continue;
            }

            $diffMonths = $now->diffInMonths($tglMasuk);

            // Syarat minimal 7 tahun = 84 bulan
            if ($diffMonths < 84) {
                $this->info("| Hak Cuti Besar | - karyawan ID {$karyawan->id} belum 7 tahun kerja, belum dapat cuti besar.");
                $loggerBesar->info("| Hak Cuti Besar | - karyawan ID {$karyawan->id} belum 7 tahun kerja, belum dapat cuti besar.");
                continue;
            }

            // Cek last_reset
            $lastReset = $hakCuti->last_reset ? Carbon::parse($hakCuti->last_reset) : null;
            if ($lastReset && $lastReset->diffInMonths($now) < 12) {
                $this->info("| Hak Cuti Besar | - ID {$hakCuti->id} untuk karyawan ID {$karyawan->id} sudah direset kurang dari 1 tahun yang lalu. Skip.");
                $loggerBesar->info("| Hak Cuti Besar | - ID {$hakCuti->id} untuk karyawan ID {$karyawan->id} sudah direset kurang dari 1 tahun yang lalu. Skip.");
                continue;
            }

            // Kuota cuti besar ambil dari tipe_cuti (default)
            $kuotaBaru = $tipeCutiBesar->kuota ?? 0;

            $hakCuti->kuota = $kuotaBaru;
            $hakCuti->used_kuota = 0;
            $hakCuti->last_reset = $now;
            $hakCuti->save();

            $this->info("| Hak Cuti Besar | - karyawan ID {$karyawan->id} berhasil diupdate: kuota {$kuotaBaru}");
            $loggerBesar->info("Reset | Hak Cuti Besar | - karyawan ID {$karyawan->id}: kuota={$kuotaBaru}, used_kuota=0");
        }

        // Proses cuti lainnya selain Cuti Tahunan dan Cuti Besar
        foreach ($tipeCutiLain as $tipeCuti) {
            $hakCutis = HakCuti::where('tipe_cuti_id', $tipeCuti->id)->get();

            foreach ($hakCutis as $hakCuti) {
                // Cek last_reset
                $lastReset = $hakCuti->last_reset ? Carbon::parse($hakCuti->last_reset) : null;
                if ($lastReset && $lastReset->diffInMonths($now) < 12) {
                    $this->info("| Hak Cuti '{$tipeCuti->nama}' | - ID {$hakCuti->id} untuk karyawan ID {$hakCuti->data_karyawans->id} sudah direset kurang dari 1 tahun yang lalu. Skip.");
                    $loggerLainnya->info("| Hak Cuti '{$tipeCuti->nama}' | - ID {$hakCuti->id} untuk karyawan ID {$hakCuti->data_karyawans->id} sudah direset kurang dari 1 tahun yang lalu. Skip.");
                    continue;
                }

                $hakCuti->kuota = $tipeCuti->kuota ?? 0;
                $hakCuti->used_kuota = 0;
                $hakCuti->last_reset = $now;
                $hakCuti->save();

                $this->info("| Hak Cuti '{$tipeCuti->nama}' | - karyawan ID {$hakCuti->data_karyawans->id} berhasil direset kuota dan used_kuota.");
                $loggerLainnya->info("Reset | Hak Cuti '{$tipeCuti->nama}' | - karyawan ID {$hakCuti->data_karyawans->id}: kuota={$hakCuti->kuota}, used_kuota=0");
            }
        }
    }
}
