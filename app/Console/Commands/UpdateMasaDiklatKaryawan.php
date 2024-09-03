<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Diklat;
use App\Models\DataKaryawan;
use App\Models\PesertaDiklat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateMasaDiklatKaryawan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-masa-diklat-karyawan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $carbonToday = Carbon::now('Asia/Jakarta')->format('d-m-Y');

        // Step 1: Cari diklat terbaru yang belum selesai berdasarkan created_at dan tgl_selesai
        $latestDiklat = Diklat::where('tgl_selesai', '>=', $carbonToday)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestDiklat) {
            Log::info('Tidak ada diklat terbaru yang ditemukan saat menjalankan update masa diklat.');
            return;
        }

        // Step 2: Ambil semua peserta yang mengikuti diklat terbaru
        $pesertaDiklat = PesertaDiklat::where('diklat_id', $latestDiklat->id)->pluck('peserta');

        if ($pesertaDiklat->isEmpty()) {
            Log::info("Diklat ID {$latestDiklat->id} tidak memiliki peserta saat menjalankan update masa diklat.");
            return;
        }

        // Step 3: Validasi jumlah peserta sesuai dengan kuota diklat
        $totalPeserta = $pesertaDiklat->count();
        if ($totalPeserta > $latestDiklat->kuota) {
            Log::warning("Jumlah peserta ({$totalPeserta}) melebihi kuota ({$latestDiklat->kuota}) untuk diklat ID {$latestDiklat->id}.");
            return;
        }

        // Step 4: Loop melalui peserta dan update masa_diklat
        foreach ($pesertaDiklat as $userId) {
            $dataKaryawan = DataKaryawan::where('user_id', $userId)->first();
            if ($dataKaryawan) {
                // Update masa_diklat berdasarkan durasi diklat terbaru
                $dataKaryawan->masa_diklat = $latestDiklat->durasi;
                $dataKaryawan->save();

                Log::info("Masa diklat untuk karyawan dengan user_id {$userId} telah diupdate menjadi {$latestDiklat->durasi} untuk diklat ID {$latestDiklat->id}.");
            } else {
                Log::error("Data karyawan dengan user_id {$userId} tidak ditemukan saat mencoba update masa diklat untuk diklat ID {$latestDiklat->id}.");
            }
        }
        Log::info("Proses update masa diklat selesai untuk diklat ID {$latestDiklat->id} dengan jumlah peserta {$totalPeserta}.");
    }
}
