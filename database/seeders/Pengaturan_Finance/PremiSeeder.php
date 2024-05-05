<?php

namespace Database\Seeders\Pengaturan_Finance;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PremiSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $jenisPremi = [
                "Nominal",
                "Presentase",
            ];

            $randomPremi = [
                "Asuransi Jiwa Term Life (10 tahun)",
                "Asuransi Jiwa dengan Manfaat Unit Link",
                "Asuransi Kesehatan - Santunan Rawat Inap",
                "Asuransi Kesehatan - Rawat Jalan & Rawat Inap",
                "Asuransi Kendaraan - Mobil Full Coverage",
                "Asuransi Kendaraan - Motor Full Coverage",
                "Asuransi Kecelakaan Diri",
                "Asuransi Perjalanan (Eropa 10 hari)",
                "Asuransi Gagal Panen (per hektar)",
                "Asuransi Kebakaran Rumah",
                "Asuransi Pendidikan - Unit Link (5 tahun)",
                "Asuransi Gadget (smartphone)",
            ];

            $JenisPremi = rand(0, count($jenisPremi) - 1);
            $randomJenisPremi = $jenisPremi[$JenisPremi];

            $randomIndex = rand(0, count($randomPremi) - 1);
            $randomCharacter = $randomPremi[$randomIndex];

            $premi = [
                'nama_premi' => $randomCharacter,
                'jenis_premi' => $randomJenisPremi,
                'besaran_premi' => ($randomJenisPremi === 'Presentase') ? rand(0, 100) : rand(500000, 5000000),
                'created_at' => Carbon::now()->subDays(rand(0, 365)),
                'updated_at' => Carbon::now(),
            ];

            DB::table('premis')->insert($premi);
        }
    }
}
