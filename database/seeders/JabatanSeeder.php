<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $namaJabatan = [
            'Dokter Spesialis',
            'Dokter Umum',
            'Perawat',
            'Bidan',
            'Asisten Dokter',
            'Apoteker',
            'Tenaga Administrasi',
            'Tenaga Laboratorium',
            'Tenaga Radiologi',
            'Tenaga Kebersihan',
            'Satpam',
            'Pekerja Sosial',
            'Ahli Gizi',
            'Fisioterapis',
            'Tenaga Medis Darurat',
            'Petugas Ambulans',
            'Tenaga IT',
            'HRD',
            'Keuangan',
            'Kepala Rumah Sakit',
            'Wakil Direktur',
            'Kabid Keperawatan',
            'Kabid Kedokteran',
            'Kabid Pelayanan Penunjang',
            'Kabid Pendidikan dan Penelitian',
            'Kabid SDM dan Umum',
            'Sekretaris Direksi',
            'Humas',
            'Bendahara',
            'Staf Tata Usaha',
        ];

        for ($i = 0; $i < 30; $i++) {
            $nama_jabatan = $namaJabatan[rand(0, count($namaJabatan) - 1)];
            $is_struktural = rand(0, 1) == 1 ? true : false;
            $tunjangan = rand(500000, 5000000);
            $created_at = Carbon::now()->subDays(rand(0, 365));
            $updated_at = Carbon::now();

            // Periksa duplikasi nama unit
            if (!DB::table('jabatans')->where('nama_jabatan', $nama_jabatan)->exists()) {
                DB::table('jabatans')->insert([
                    'nama_jabatan' => $nama_jabatan,
                    'is_struktural' => $is_struktural,
                    'tunjangan_jabatan' => $tunjangan,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at,
                ]);
            }
        }
    }
}
