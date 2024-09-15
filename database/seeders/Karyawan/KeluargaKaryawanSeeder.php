<?php

namespace Database\Seeders\Karyawan;

use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\StatusKeluarga;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KeluargaKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pendidikan = ['SMA', 'SMK', 'D4', 'D3', 'D2', 'D1', 'S1', 'S2', 'S3'];
        $data_karyawan_ids = DataKaryawan::pluck('id')->all();

        foreach ($data_karyawan_ids as $karyawan_id) {
            // Ayah
            DataKeluarga::create([
                'data_karyawan_id' => $karyawan_id,
                'nama_keluarga' => 'Nama Bapak ' . $karyawan_id,
                'hubungan' => 'Bapak',
                'pendidikan_terakhir' => $pendidikan[array_rand($pendidikan)],
                'status_hidup' => rand(0, 1),
                'pekerjaan' => 'Pekerjaan Bapak ' . $karyawan_id,
                'no_hp' => rand(1214, 5000000),
                'email' => 'bapak' . $karyawan_id . '@example.com',
                'status_keluarga_id' => 1
            ]);

            // Ibu
            DataKeluarga::create([
                'data_karyawan_id' => $karyawan_id,
                'nama_keluarga' => 'Nama Ibu ' . $karyawan_id,
                'hubungan' => 'Ibu',
                'pendidikan_terakhir' => $pendidikan[array_rand($pendidikan)],
                'status_hidup' => rand(0, 1),
                'pekerjaan' => 'Pekerjaan Ibu ' . $karyawan_id,
                'no_hp' => rand(1214, 5000000),
                'email' => 'ibu' . $karyawan_id . '@example.com',
                'status_keluarga_id' => 1
            ]);

            // Tambahkan anggota keluarga lainnya
            for ($i = 0; $i < rand(0, 4); $i++) {
                DataKeluarga::create([
                    'data_karyawan_id' => $karyawan_id,
                    'nama_keluarga' => 'Nama Keluarga ' . $karyawan_id . ' ' . $i,
                    'hubungan' => ['Suami', 'Istri', 'Anak Ke-1', 'Anak Ke-2', 'Anak Ke-3', 'Anak Ke-4', 'Anak Ke-5', 'Bapak', 'Ibu', 'Bapak Mertua', 'Ibu Mertua'][array_rand(['Suami', 'Istri', 'Anak Ke-1', 'Anak Ke-2', 'Anak Ke-3', 'Anak Ke-4', 'Anak Ke-5', 'Bapak', 'Ibu', 'Bapak Mertua', 'Ibu Mertua'])],
                    'pendidikan_terakhir' => $pendidikan[array_rand($pendidikan)],
                    'status_hidup' => rand(0, 1),
                    'pekerjaan' => 'Pekerjaan ' . $karyawan_id . ' ' . $i,
                    'no_hp' => rand(1214, 5000000),
                    'email' => 'keluarga' . $karyawan_id . $i . '@example.com',
                    'status_keluarga_id' => 1,
                ]);
            }
        }
    }
}
