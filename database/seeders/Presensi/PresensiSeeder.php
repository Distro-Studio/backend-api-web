<?php

namespace Database\Seeders\Presensi;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\Presensi;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresensiSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $kategoriPresensi = DB::table('kategori_presensis')->pluck('label', 'id')->all();
        $berkas_id = DB::table('berkas')->pluck('id')->all();

        $lat_default = 33.7490;
        $long_default = -84.3880;

        $user_ids = User::where('nama', '!=', 'Super Admin')->pluck('id')->all();
        $jadwal_ids = Jadwal::pluck('id')->all();

        $currentYear = Carbon::now()->year;
        $randomMonth = rand(1, 12);
        // $startDate = Carbon::createFromDate($currentYear, $randomMonth, rand(1, 28))->startOfMonth(); // Tanggal acak pada bulan tahun ini
        $startDate = Carbon::now()->startOfMonth(); // Tanggal acak pada bulan tahun ini
        $endDate = $startDate->copy()->endOfMonth(); // Hari terakhir bulan tersebu

        $kategoriPresensiId = array_search('Tepat Waktu', $kategoriPresensi);

        foreach ($user_ids as $index => $user_id) {
            // Ambil data_karyawan_id yang sesuai dengan user_id
            $data_karyawan_id = DB::table('data_karyawans')
                ->where('user_id', $user_id)
                ->value('id');

            // Pastikan data_karyawan_id ditemukan
            if (!$data_karyawan_id) {
                continue;
            }

            // Loop melalui setiap hari dalam bulan ini
            $currentDate = $startDate->copy();
            while ($currentDate->lessThanOrEqualTo($endDate)) {
                $jadwal_id = $jadwal_ids[array_rand($jadwal_ids)];

                $lat = $faker->latitude(
                    $min = ($lat_default * 10000 - rand(0, 50)) / 10000,
                    $max = ($lat_default * 10000 + rand(0, 50)) / 10000
                );
                $long = $faker->longitude(
                    $min = ($long_default * 10000 - rand(0, 50)) / 10000,
                    $max = ($long_default * 10000 + rand(0, 50)) / 10000
                );

                $jam_masuk = $currentDate->copy()->startOfDay()->addHours(rand(7, 9));
                $jam_keluar = (clone $jam_masuk)->addHours(rand(7, 10));

                // Tentukan kategori dan status untuk 20 karyawan pertama
                $kategori_id = ($index < 20) ? $kategoriPresensiId : array_rand($kategoriPresensi);

                Presensi::create([
                    'user_id' => $user_id,
                    'data_karyawan_id' => $data_karyawan_id,
                    'jadwal_id' => $jadwal_id,
                    'jam_masuk' => $jam_masuk,
                    'jam_keluar' => null,
                    // 'durasi' => $jam_keluar->diffInSeconds($jam_masuk),
                    'durasi' => null,
                    'lat' => $lat,
                    'long' => $long,
                    'latkeluar' => $lat,
                    'longkeluar' => $long,
                    'foto_masuk' => $berkas_id[array_rand($berkas_id)],
                    'foto_keluar' => $berkas_id[array_rand($berkas_id)],
                    'kategori_presensi_id' => $kategori_id,
                ]);

                $currentDate->addDay();
            }
        }
    }
}
