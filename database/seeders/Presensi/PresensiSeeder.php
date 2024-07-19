<?php

namespace Database\Seeders\Presensi;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\Presensi;
use Faker\Factory as Faker;
use App\Models\DataKaryawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $kategori = ['Tepat Waktu', 'Terlambat', 'Hadir', 'Absen', 'Izin', 'Invalid', 'Libur', 'Cuti'];
        $presensi_absen = ['Hadir', 'Izin', 'Sakit'];

        $lat_default = 33.7490;
        $long_default = -84.3880;

        $user_ids = User::pluck('id')->all();
        $jadwal_ids = Jadwal::pluck('id')->all();

        $startDate = Carbon::now()->subMonth()->startOfMonth(); // Hari pertama bulan ini
        $endDate = Carbon::now()->subMonth()->endOfMonth(); // Hari terakhir bulan ini

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

                // Tentukan kategori untuk 20 karyawan pertama
                $kategoriPresensi = ($index < 20) ? 'Tepat Waktu' : $kategori[array_rand($kategori)];

                // Pastikan presensi adalah 'Hadir' untuk 20 orang pertama
                $presensi = ($index < 8) ? 'Hadir' : $presensi_absen[array_rand($presensi_absen)];

                Presensi::create([
                    'user_id' => $user_id,
                    'data_karyawan_id' => $data_karyawan_id,
                    'jadwal_id' => $jadwal_id,
                    'jam_masuk' => $jam_masuk,
                    'jam_keluar' => $jam_keluar,
                    'durasi' => $jam_keluar->diffInSeconds($jam_masuk),
                    'lat' => $lat,
                    'long' => $long,
                    'foto_masuk' => 'storage/' . $currentDate->format('Y-m-d') . '/foto_masuk/user_' . $user_id . '.jpg',
                    'foto_keluar' => 'storage/' . $currentDate->format('Y-m-d') . '/foto_keluar/user_' . $user_id . '.jpg',
                    'presensi' => $presensi,
                    'kategori' => $kategoriPresensi
                ]);

                $currentDate->addDay();
            }
        }
    }
}
