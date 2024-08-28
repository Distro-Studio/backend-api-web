<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use App\Models\StatusPerubahan;
use Illuminate\Database\Seeder;
use App\Models\RiwayatPerubahan;
use App\Models\PerubahanPersonal;
use App\Models\KategoriPendidikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PerubahanPersonalSeeder extends Seeder
{
    public function run(): void
    {
        $dataKaryawans = DataKaryawan::pluck('id')->all();
        $statusPerubahans = StatusPerubahan::pluck('id')->all();
        $kategori_agama_id = KategoriAgama::pluck('id')->all();
        $kategori_darah_id = KategoriDarah::pluck('id')->all();
        $kategori_pendidikan_id = KategoriPendidikan::pluck('id')->all();

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
                'no_hp' => '08123456789' . $i,
                'jenis_kelamin' => rand(0, 1),
                'nik_ktp' => '123456789' . $i,
                'no_kk' => '123456789' . $i,
                'kategori_agama_id' => $kategori_agama_id[array_rand($kategori_agama_id)],
                'kategori_darah_id' => $kategori_darah_id[array_rand($kategori_darah_id)],
                'tinggi_badan' => rand(160, 190),
                'berat_badan' => rand(50, 100),
                'alamat' => 'Jalan ' . $i,
                'no_ijasah' => '123456789' . $i,
                'tahun_lulus' => rand(1900, 2022),
                'pendidikan_terakhir' => $kategori_pendidikan_id[array_rand($kategori_pendidikan_id)],
                'gelar_depan' => 'Dr.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
