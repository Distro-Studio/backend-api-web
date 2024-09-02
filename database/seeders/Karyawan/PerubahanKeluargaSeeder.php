<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\KategoriPendidikan;
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
        $kategoriPendidikans = KategoriPendidikan::pluck('id')->all();

        for ($i = 0; $i < 10; $i++) {
            // Pilih data_karyawan_id, data_keluarga_id, dan pendidikan_terakhir secara acak
            $dataKaryawanId = $dataKaryawans[array_rand($dataKaryawans)];
            $dataKeluargaId1 = $dataKeluargas[array_rand($dataKeluargas)];
            $dataPendidikanId = $kategoriPendidikans[array_rand($kategoriPendidikans)];

            // Buat contoh data perubahan untuk keluarga
            $oldFamilyData = [
                [
                    'data_keluarga_id' => $dataKeluargaId1,
                    'nama_keluarga' => 'Nama lama ' . $i,
                    'hubungan' => 'Ayah',
                    'pendidikan_terakhir' => $dataPendidikanId,
                    'status_hidup' => rand(0, 1),
                    'pekerjaan' => 'Pekerjaan lama ' . $i,
                    'no_hp' => '08123456790' . $i,
                    'email' => 'keluarga_lama' . $i . '@example.com'
                ]
            ];

            $newFamilyData = [
                [
                    'data_keluarga_id' => $dataKeluargaId1,
                    'nama_keluarga' => 'Nama Baru ' . $i,
                    'hubungan' => 'Ayah',
                    'pendidikan_terakhir' => $dataPendidikanId,
                    'status_hidup' => rand(0, 1),
                    'pekerjaan' => 'Pekerjaan Baru ' . $i,
                    'no_hp' => '08123456790' . $i,
                    'email' => 'keluarga_baru' . $i . '@example.com'
                ]
            ];

            // Buat RiwayatPerubahan dengan original_data dan updated_data dalam format JSON
            $riwayatPerubahan = RiwayatPerubahan::create([
                'data_karyawan_id' => $dataKaryawanId,
                'jenis_perubahan' => 'Keluarga',
                'kolom' => ['nama_keluarga', 'hubungan', 'pendidikan_terakhir', 'status_hidup', 'pekerjaan', 'no_hp', 'email'][array_rand(['nama_keluarga', 'hubungan', 'pendidikan_terakhir', 'status_hidup', 'pekerjaan', 'no_hp', 'email'])],
                'original_data' => json_encode($oldFamilyData),
                'updated_data' => json_encode($newFamilyData),
                'status_perubahan_id' => $statusPerubahans[array_rand($statusPerubahans)],
                'verifikator_1' => null,
                'alasan' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Simpan PerubahanKeluarga untuk setiap perubahan
            foreach ($newFamilyData as $data) {
                PerubahanKeluarga::create([
                    'riwayat_perubahan_id' => $riwayatPerubahan->id,
                    'data_keluarga_id' => $data['data_keluarga_id'],
                    'nama_keluarga' => $data['nama_keluarga'],
                    'hubungan' => $data['hubungan'],
                    'pendidikan_terakhir' => $data['pendidikan_terakhir'],
                    'status_hidup' => $data['status_hidup'],
                    'pekerjaan' => $data['pekerjaan'],
                    'no_hp' => $data['no_hp'],
                    'email' => $data['email'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
