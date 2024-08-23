<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use App\Models\StatusPerubahan;
use Illuminate\Database\Seeder;
use App\Models\RiwayatPerubahan;
use App\Models\PerubahanPersonal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PerubahanPersonalSeeder extends Seeder
{
    public function run(): void
    {
        $dataKaryawans = DataKaryawan::pluck('id')->all();
        $statusPerubahans = StatusPerubahan::pluck('id')->all();

        for ($i = 0; $i < 15; $i++) {
            $riwayatPerubahan = RiwayatPerubahan::create([
                'data_karyawan_id' => $dataKaryawans[array_rand($dataKaryawans)],
                'jenis_perubahan' => 'Personal',
                'kolom' => ['tempat_lahir', 'tgl_lahir', 'no_telp'][array_rand(['tempat_lahir', 'tgl_lahir', 'no_telp'])],
                'original_data' => 'Old Value',
                'updated_data' => 'New Value',
                'status_perubahan_id' => $statusPerubahans[array_rand($statusPerubahans)],
                'verifikator_1' => null,
                'alasan' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            PerubahanPersonal::create([
                'riwayat_perubahan_id' => $riwayatPerubahan->id,
                'tempat_lahir' => ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan'][array_rand(['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan'])],
                'tgl_lahir' => Carbon::now()->subYears(rand(20, 50))->format('Y-m-d'),
                'no_telp' => '08123456789' . $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
