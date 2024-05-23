<?php

namespace Database\Seeders\Karyawan;

use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KeluargaKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hubungan = ['Ayah', 'Ibu', 'Suami', 'Istri', 'Anak'];
        $pendidikan = ['SMA', 'SMK', 'D4', 'D3', 'D2', 'D1', 'S1', 'S2', 'S3'];
        $data_karyawan_id = DataKaryawan::pluck('id')->all();

        for ($i = 0; $i < 50; $i++) {
            $dataKeluarga = new DataKeluarga([
                'data_karyawan_id' => $data_karyawan_id[array_rand($data_karyawan_id)],
                'nama_keluarga' => 'Nama keluarga ' . $i,
                'hubungan' => $hubungan[array_rand($hubungan)],
                'pendidikan_terakhir' => $pendidikan[array_rand($pendidikan)],
                'status_hidup' => rand(0, 1),
                'pekerjaan' => 'Pekerjaan ' . $i,
                'no_hp' => rand(1214, 5000000),
                'email' => 'keluarga' . $i . '@example.com',
            ]);
            $dataKeluarga->save();
        }
    }
}
