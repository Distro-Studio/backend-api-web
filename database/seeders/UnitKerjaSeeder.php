<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UnitKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $namaUnit = [
            // Nama Unit Kerja
            "Unit Gawat Darurat (UGD)",
            "Rawat Jalan",
            "Rawat Inap",
            "Kebidanan dan Kandungan",
            "Kesehatan Anak",
            "Bedah",
            "Penyakit Dalam",
            "Jantung",
            "Saraf",
            "Mata",
            "Telinga Hidung Tenggorokan (THT)",
            "Kulit dan Kelamin",
            "Gigi dan Mulut",
            "Patologi Klinik",
            "Radiologi",
            "Fisioterapi",
            "Rehabilitasi Medik",
            "Onkologi",
            "Psikiatri",
            // Unit Penunjang Medis
            "Apotek",
            "Laboratorium",
            "Radiologi",
            "Perpustakaan",
            "Gizi",
            "Rekam Medis",
            "Teknologi Informasi dan Komunikasi (TIK)",
            "Pemeliharaan Sarana Prasarana",
            "Transportasi",
            "Laundry",
            "Kebersihan",
            "Keamanan",
            // Unit Administrasi
            "Direktur",
            "Keuangan",
            "Sumber Daya Manusia (SDM)",
            "Hukum dan Kerjasama",
            "Pendidikan dan Pelatihan",
            "Pengembangan Mutu",
            "Promosi dan Humas",
            // Unit Lainnya
            "Badan Penyelenggara Jaminan Sosial (BPJS)",
            "Asuransi Kesehatan",
            "Penjualan Alat Kesehatan",
            "Kantin",
            "Mushola"
        ];

        // Generate 30 data Unit Kerja random
        for ($i = 0; $i < 30; $i++) {
            $nama_unit = $namaUnit[rand(0, count($namaUnit) - 1)];
            $jenis_karyawan = rand(0, 1) == 1 ? true : false;
            $created_at = Carbon::now()->subDays(rand(0, 365));
            $updated_at = Carbon::now();

            // Periksa duplikasi nama unit
            if (!DB::table('unit_kerjas')->where('nama_unit', $nama_unit)->exists()) {
                DB::table('unit_kerjas')->insert([
                    'nama_unit' => $nama_unit,
                    'jenis_karyawan' => $jenis_karyawan,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at,
                ]);
            }
        }
    }
}
