<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\StatusPerubahan;
use Illuminate\Database\Seeder;
use App\Models\RiwayatPerubahan;
use App\Models\PerubahanKeluarga;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PerubahanKeluargaSeeder extends Seeder
{
    public function run(): void
    {
        $dataKaryawans = DataKaryawan::pluck('id')->all();
        $statusPerubahans = StatusPerubahan::pluck('id')->all();
        $dataKeluargas = DataKeluarga::pluck('id')->all();

        for ($i = 0; $i < 15; $i++) {
            // Create RiwayatPerubahan
            $riwayatPerubahan = RiwayatPerubahan::create([
                'data_karyawan_id' => $dataKaryawans[array_rand($dataKaryawans)],
                'jenis_perubahan' => 'Keluarga', // Focusing on family data changes
                'kolom' => ['nama_keluarga', 'hubungan', 'pendidikan_terakhir', 'status_hidup', 'pekerjaan', 'no_hp', 'email'][array_rand(['nama_keluarga', 'hubungan', 'pendidikan_terakhir', 'status_hidup', 'pekerjaan', 'no_hp', 'email'])],
                'original_data' => 'Old Value',
                'updated_data' => 'New Value',
                'status_perubahan_id' => $statusPerubahans[array_rand($statusPerubahans)],
                'verifikator_1' => null,
                'alasan' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $dataKeluargaId = $dataKeluargas[array_rand($dataKeluargas)];

            PerubahanKeluarga::create([
                'riwayat_perubahan_id' => $riwayatPerubahan->id,
                'data_keluarga_id' => $dataKeluargaId,
                'nama_keluarga' => 'Nama Keluarga ' . $i,
                'hubungan' => ['Ayah', 'Ibu', 'Anak', 'Suami', 'Istri', 'Nenek', 'Kakek', 'Ayah Suami', 'Ibu Suami', 'Ayah Istri', 'Ibu Istri'][array_rand(['Ayah', 'Ibu', 'Anak', 'Suami', 'Istri', 'Nenek', 'Kakek', 'Ayah Suami', 'Ibu Suami', 'Ayah Istri', 'Ibu Istri'])],
                'pendidikan_terakhir' => 'S1',
                'status_hidup' => rand(0, 1), // 0 = meninggal, 1 = hidup
                'pekerjaan' => 'Pekerjaan ' . $i,
                'no_hp' => '08123456789' . $i,
                'email' => 'keluarga' . $i . '@example.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
