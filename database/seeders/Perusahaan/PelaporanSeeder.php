<?php

namespace Database\Seeders\Perusahaan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Berkas;
use App\Models\Pelaporan;
use Illuminate\Support\Str;
use App\Models\StatusBerkas;
use App\Models\KategoriBerkas;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PelaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id')->all();
        $kategoriBerkas = KategoriBerkas::where('label', 'System')->first();
        $statusBerkas = StatusBerkas::where('label', 'Menunggu')->first();
        $basePath = '/path/to/uploads'; // Ubah sesuai dengan path yang sesuai untuk upload_foto

        for ($i = 1; $i <= 20; $i++) {
            // Ambil secara acak pelapor dan pelaku yang berbeda
            $pelaporId = $userIds[array_rand($userIds)];
            do {
                $pelakuId = $userIds[array_rand($userIds)];
            } while ($pelakuId == $pelaporId);

            // Generate tanggal kejadian secara acak dalam 30 hari terakhir
            $tglKejadian = Carbon::now()->subDays(rand(1, 30))->format('Y-m-d H:i:s');

            // Buat record di tabel pelaporan
            $pelaporan = Pelaporan::create([
                'pelapor' => $pelaporId,
                'pelaku' => $pelakuId,
                'tgl_kejadian' => $tglKejadian,
                'lokasi' => 'Lokasi Kejadian ' . $i,
                'kronologi' => 'Kronologi kejadian pelaporan ' . $i . ' yang berisi detail kejadian dan saksi-saksi.',
                'upload_foto' => null, // Set null sementara, akan di-update setelah berkas dibuat
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Simulasi upload foto dan simpan ke dalam tabel berkas
            $fileId = (string) Str::uuid();
            $filePath = $basePath . '/pelaporan_' . $i . '.jpg'; // Misal file gambar dengan ekstensi jpg

            $berkas = Berkas::create([
                'user_id' => $pelaporId,
                'file_id' => $fileId,
                'nama' => 'Upload Foto Pelaporan ' . $i,
                'kategori_berkas_id' => $kategoriBerkas->id,
                'status_berkas_id' => $statusBerkas->id,
                'path' => $filePath,
                'tgl_upload' => now(),
                'nama_file' => 'pelaporan_' . $i . '.jpg',
                'ext' => 'image/jpeg',
                'size' => rand(1000, 3000), // Contoh ukuran file antara 1000KB - 3000KB
            ]);

            // Update kolom upload_foto di tabel pelaporan
            $pelaporan->update(['upload_foto' => $berkas->id]);
        }
    }
}
