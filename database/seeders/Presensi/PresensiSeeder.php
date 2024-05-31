<?php

namespace Database\Seeders\Presensi;

use App\Models\DataKaryawan;
use App\Models\Jadwal;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Presensi;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
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
        $absensi = ['Hadir', 'Izin', 'Sakit'];

        $lat_default = 33.7490;
        $long_default = -84.3880;

        $user_ids = User::pluck('id')->all();
        $data_karyawan_ids = DataKaryawan::pluck('id')->all();
        $jadwal_ids = Jadwal::pluck('id')->all();

        for ($i = 0; $i < 30; $i++) {
            if (count($user_ids) <= $i) {
                break; // break if we run out of unique user_ids
            }
            $user_id = $user_ids[$i];
            $data_karyawan_id = $data_karyawan_ids[array_rand($data_karyawan_ids)];
            $jadwal_id = $jadwal_ids[array_rand($jadwal_ids)];

            $lat = $faker->latitude(
                $min = ($lat_default * 10000 - rand(0, 50)) / 10000,
                $max = ($lat_default * 10000 + rand(0, 50)) / 10000
            );
            $long = $faker->longitude(
                $min = ($long_default * 10000 - rand(0, 50)) / 10000,
                $max = ($long_default * 10000 + rand(0, 50)) / 10000
            );

            $jam_masuk = Carbon::now()->subDays(rand(1, 30))->subHours(rand(1, 8));
            $jam_keluar = (clone $jam_masuk)->addHours(rand(1, 8));

            $presensi = new Presensi([
                'user_id' => $user_id,
                'data_karyawan_id' => $data_karyawan_id,
                'jadwal_id' => $jadwal_id,
                'jam_masuk' => $jam_masuk,
                'jam_keluar' => $jam_keluar,
                'durasi' => $jam_keluar->diffInHours($jam_masuk),
                'lat' => $lat,
                'long' => $long,
                'foto' => 'storage/link/' . $i . '/foto_hadir/users' . $user_id . '.jpg',
                'absensi' => $absensi[array_rand($absensi)],
                'kategori' => $kategori[array_rand($kategori)],
            ]);
            $presensi->save();
        }
    }
}
