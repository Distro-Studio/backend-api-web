<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use App\Models\StatusPerubahan;
use Illuminate\Database\Seeder;
use App\Models\RiwayatPerubahan;
use Illuminate\Support\Facades\DB;

class RiwayatPerubahanSeeder extends Seeder
{
    public function run(): void
    {
        $dataKaryawans = DataKaryawan::pluck('id')->all();
        $statusPerubahans = StatusPerubahan::pluck('id')->all();

        for ($i = 0; $i < 15; $i++) {
            RiwayatPerubahan::create([
                'data_karyawan_id' => $dataKaryawans[array_rand($dataKaryawans)],
                'kolom' => ['Nama', 'Alamat', 'Email', 'NIK', 'Foto Profile'][array_rand(['Nama', 'Alamat', 'Email', 'NIK', 'Foto Profile'])],
                'original_data' => 'Sophia Bowen',
                'updated_data' => 'Oscar Cohen',
                'status_perubahan_id' => $statusPerubahans[array_rand($statusPerubahans)],
                'verifikator_1' => null,
                'alasan' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
