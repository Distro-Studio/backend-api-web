<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use App\Models\KategoriPendidikan;
use Illuminate\Database\Seeder;
use App\Models\RiwayatPerubahan;
use App\Models\PerubahanPersonal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PerubahanPersonalSeeder extends Seeder
{
    public function run(): void
    {
        $dataKaryawans = DataKaryawan::all();
        $kategori_agama_id = KategoriAgama::pluck('id')->all();
        $kategori_darah_id = KategoriDarah::pluck('id')->all();
        $pendidikan_terakhir_option = KategoriPendidikan::pluck('id')->all();

        $kolomPilihan = [
            'tempat_lahir',
            'tgl_lahir',
            'no_hp',
            'jenis_kelamin',
            'nik_ktp',
            'no_kk',
            'kategori_agama_id',
            'kategori_darah_id',
            'tinggi_badan',
            'berat_badan',
            'alamat',
            'no_ijasah',
            'tahun_lulus',
            'pendidikan_terakhir',
            'gelar_depan'
        ];

        $tempat_lahir = [
            'Jakarta',
            'Bandung',
            'Surabaya',
            'Yogyakarta',
            'Medan'
        ];

        for ($i = 0; $i < 15; $i++) {
            // Pilih data karyawan secara acak
            $dataKaryawan = $dataKaryawans->random();

            // Pilih kolom secara acak untuk perubahan
            $kolom = $kolomPilihan[array_rand($kolomPilihan)];

            // Ambil nilai original dari data_karyawans berdasarkan kolom yang dipilih
            $originalData = $dataKaryawan->{$kolom};

            // Cek jika originalData adalah null, lewati iterasi ini
            if (is_null($originalData)) {
                $i--; // kurangi iterator agar jumlah iterasi tetap 15
                continue; // lanjutkan ke iterasi berikutnya
            }

            // Buat entry riwayat perubahan
            $riwayatPerubahan = RiwayatPerubahan::create([
                'data_karyawan_id' => $dataKaryawan->id,
                'jenis_perubahan' => 'Personal',
                'kolom' => $kolom,
                'original_data' => $originalData,
                'updated_data' => 'updated_data',
                'status_perubahan_id' => 1,
                'verifikator_1' => null,
                'alasan' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Buat entry perubahan personal
            $perubahanPersonal = PerubahanPersonal::create([
                'riwayat_perubahan_id' => $riwayatPerubahan->id,
                'tempat_lahir' => $tempat_lahir[array_rand($tempat_lahir)],
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
                'pendidikan_terakhir' => $pendidikan_terakhir_option[array_rand($pendidikan_terakhir_option)],
                'gelar_depan' => 'Dr.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Ambil nilai updated dari perubahan_personals berdasarkan kolom yang dipilih
            $updatedData = $perubahanPersonal->{$kolom};

            // Update updated_data di riwayat perubahan
            $riwayatPerubahan->updated_data = $updatedData;
            $riwayatPerubahan->save();
        }
    }
}
