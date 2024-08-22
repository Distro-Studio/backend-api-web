<?php

namespace Database\Seeders\Perusahaan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Berkas;
use App\Models\Diklat;
use Illuminate\Support\Str;
use App\Models\StatusBerkas;
use App\Models\StatusDiklat;
use App\Models\KategoriBerkas;
use App\Models\KategoriDiklat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DiklatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriDiklatIds = [1, 2];
        $user_ids = User::where('nama', '!=', 'Super Admin')->pluck('id')->take(25)->all();

        foreach ($user_ids as $i => $user_id) {
            $startDate = Carbon::now()->subDays(rand(0, 365));
            $endDate = $startDate->copy()->addDays(rand(1, 5));
            $startTime = Carbon::parse('08:00:00');
            $endTime = $startTime->copy()->addHours(rand(1, 4));
            $duration = $startTime->diffInSeconds($endTime);

            // Pilih kategori diklat ID secara acak
            $kategori_diklat_id = $kategoriDiklatIds[array_rand($kategoriDiklatIds)];

            if ($kategori_diklat_id == 1) {
                // Jika kategori diklat adalah Internal
                $gambarUrl = '/path/to/diklat/berkas/Diklat_Thumbnail_' . $i;
                $berkas_gambar = $this->createBerkas($user_id, 'Berkas Diklat - ' . User::find($user_id)->nama, $gambarUrl);
                $dokumen_eksternal = null;
                $kuota = rand(10, 50);
            } else {
                // Jika kategori diklat adalah Eksternal
                $gambarUrl = '/path/to/diklat/berkas/Diklat_Eksternal_' . $i;
                $berkas_gambar = null;
                $dokumen_eksternal = $this->createBerkas($user_id, 'Berkas Diklat Eksternal - ' . User::find($user_id)->nama, $gambarUrl);
                $kuota = 1;
            }

            Diklat::create([
                'gambar' => $berkas_gambar ? $berkas_gambar->id : null,
                'dokumen_eksternal' => $dokumen_eksternal ? $dokumen_eksternal->id : null,
                'nama' => 'Diklat ' . ($i + 1),
                'kategori_diklat_id' => $kategori_diklat_id,
                'status_diklat_id' => 1,
                'deskripsi' => 'Deskripsi Diklat ' . ($i + 1),
                'kuota' => $kuota,
                'tgl_mulai' => $startDate->format('Y-m-d'),
                'tgl_selesai' => $endDate->format('Y-m-d'),
                'jam_mulai' => $startTime->format('H:i:s'),
                'jam_selesai' => $endTime->format('H:i:s'),
                'durasi' => $duration,
                'lokasi' => 'Lokasi ' . ($i + 1),
            ]);
        }
    }

    private function createBerkas($user_id, $nama, $path)
    {
        return Berkas::create([
            'user_id' => $user_id,
            'file_id' => (string) Str::uuid(),
            'nama' => $nama,
            'kategori_berkas_id' => KategoriBerkas::where('label', 'System')->first()->id,
            'status_berkas_id' => StatusBerkas::where('label', 'Menunggu')->first()->id,
            'path' => $path,
            'tgl_upload' => now(),
            'nama_file' => 'dokumen_' . $user_id,
            'ext' => 'image/jpeg',
            'size' => rand(1000, 2000),
        ]);
    }
}
