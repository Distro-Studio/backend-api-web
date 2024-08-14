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
        $kategoriDiklatIds = KategoriDiklat::pluck('id')->all();
        $kategoriBerkas = KategoriBerkas::where('label', 'System')->first();
        $statusBerkas = StatusBerkas::where('label', 'Menunggu')->first();
        $user_ids = User::where('nama', '!=', 'Super Admin')->pluck('id')->all();

        for ($i = 1; $i <= 25; $i++) {
            if (count($user_ids) <= $i) {
                break; // break if we run out of unique user_ids
            }

            $user_id = $user_ids[$i];
            // Generate random dates
            $startDate = Carbon::now()->subDays(rand(0, 365));
            $endDate = $startDate->copy()->addDays(rand(1, 5));
            $startTime = Carbon::parse('08:00:00');
            $endTime = $startTime->copy()->addHours(rand(1, 4));

            // Calculate duration
            $duration = $startTime->diffInSeconds($endTime);

            // Generate image name based on the Diklat name and date
            $bulanTahun = $startDate->locale('id')->format('MMMM Y');
            $gambarName = 'Diklat Thumbnail - Diklat ' . $i . ' ' . $bulanTahun;
            $gambarUrl = '/path/to/diklat/thumbnails/' . $gambarName;

            // Create Diklat record
            $diklat = Diklat::create([
                'gambar' => $gambarUrl,
                'nama' => 'Diklat ' . $i,
                'kategori_diklat_id' => $kategoriDiklatIds[array_rand($kategoriDiklatIds)],
                'status_diklat_id' => 1,
                'deskripsi' => 'Deskripsi Diklat ' . $i,
                'kuota' => rand(10, 50),
                'tgl_mulai' => $startDate->format('Y-m-d'),
                'tgl_selesai' => $endDate->format('Y-m-d'),
                'jam_mulai' => $startTime->format('H:i:s'),
                'jam_selesai' => $endTime->format('H:i:s'),
                'durasi' => $duration,
                'lokasi' => 'Lokasi ' . $i,
            ]);

            Berkas::create([
                'user_id' => $user_id,
                'file_id' => (string) Str::uuid(),
                'nama' => 'Berkas Diklat - ' . User::find($user_id)->nama,
                'kategori_berkas_id' => $kategoriBerkas->id,
                'status_berkas_id' => $statusBerkas->id,
                'path' => $gambarUrl,
                'tgl_upload' => now(),
                'nama_file' => 'dokumen_' . $user_id,
                'ext' => 'image/jpeg',
                'size' => rand(1000, 2000), // Example file size in KB
            ]);

            // Update Diklat record with the image URL
            $diklat->update(['gambar' => $gambarUrl]);
        }
    }
}
