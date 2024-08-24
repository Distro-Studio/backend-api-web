<?php

namespace Database\Seeders\JadwalKaryawan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\KategoriTukarJadwal;
use App\Models\StatusTukarJadwal;
use App\Models\TukarJadwal;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TukarJadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('nama', '!=', 'Super Admin')->pluck('id')->all();
        $jadwals = Jadwal::pluck('id')->all();

        for ($i = 0; $i < 20; $i++) {
            $user_pengajuan = $users[array_rand($users)];
            $jadwal_pengajuan = $jadwals[array_rand($jadwals)];

            // Find another user for swapping
            $user_ditukar = $users[array_rand($users)];
            while ($user_ditukar == $user_pengajuan) {
                $user_ditukar = $users[array_rand($users)];
            }
            $jadwal_ditukar = $jadwals[array_rand($jadwals)];

            TukarJadwal::create([
                'user_pengajuan' => $user_pengajuan,
                'user_ditukar' => $user_ditukar,
                'jadwal_pengajuan' => $jadwal_pengajuan,
                'jadwal_ditukar' => $jadwal_ditukar,
                'status_penukaran_id' => 1, // Assuming you have a status field with 3 statuses
                'kategori_penukaran_id' => rand(1, 2), // Assuming you have a category field with 2 categories
            ]);
        }
    }
}
