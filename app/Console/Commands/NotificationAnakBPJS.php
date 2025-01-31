<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Notifikasi;
use App\Models\DataKeluarga;
use Illuminate\Console\Command;

class NotificationAnakBPJS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-anak-bpjs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jika ada anak BPJS dengan umur maks 25, maka kirim notifikasi dan matikan BPJS nya';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // 1. Get data_keluargas dengan umur maks 25 tahun
            $dataKeluargas = DataKeluarga::whereIn('hubungan', ['Anak Ke-1', 'Anak Ke-2', 'Anak Ke-3'])
                ->where('status_hidup', 1)
                ->where('is_menikah', 1)
                ->whereRaw("TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) > 21")
                ->get();
            if ($dataKeluargas->isEmpty()) {
                $this->info('Tidak ada data anak BPJS dengan umur maks 21 tahun.');
                return;
            }

            foreach ($dataKeluargas as $keluarga) {
                // 2. Get data_karyawan_id dari tabel data_keluargas
                $dataKaryawanId = $keluarga->data_karyawan_id;

                // 3. Cari user berdasarkan data_karyawan_id
                $userKaryawan = User::where('data_karyawan_id', $dataKaryawanId)->first();
                if (!$userKaryawan) {
                    $this->warn("Karyawan dengan data_karyawan_id {$dataKaryawanId} tidak ditemukan.");
                    continue;
                }

                // 4. Update fields pada data_keluargas
                $keluarga->update([
                    'is_bpjs' => 0
                ]);

                // 5. Kirim notifikasi kepada user_id = 1 dan user_id karyawan tersebut
                $notifikasiMessage = "Pemberitahuan: Anak dengan nama '{$keluarga->nama_keluarga}' telah mencapai batas usia maksimal 25 tahun. Harap segera perbarui data BPJS.";
                $messageSuperAdmin = "Notifikasi untuk Super Admin: Pemberitahuan: Anak dengan nama '{$keluarga->nama_keluarga}' dari karyawan '{$userKaryawan->nama}' telah mencapai batas usia maksimal 25 tahun. Harap segera perbarui data BPJS.";

                // Notifikasi untuk user_id = 1 (Admin)
                Notifikasi::create([
                    'user_id' => 1,
                    'kategori_notifikasi_id' => 12,
                    'message' => $messageSuperAdmin,
                    'is_read' => false,
                    'is_verifikasi' => true,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);

                // Notifikasi untuk user karyawan
                Notifikasi::create([
                    'user_id' => $userKaryawan->id,
                    'kategori_notifikasi_id' => 12,
                    'message' => $notifikasiMessage,
                    'is_read' => false,
                    'is_verifikasi' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);

                $this->info("Notifikasi berhasil dikirimkan untuk Super Admin dan Karyawan {$userKaryawan->id}, dengan anak bernama '{$keluarga->nama_keluarga}' sudah mencapai batas usia maksimal 25 tahun untuk BPJS Kesehatan.");
            }
        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan: {$e->getMessage()}");
        }
    }
}
