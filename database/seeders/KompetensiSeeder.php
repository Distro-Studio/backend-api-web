<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KompetensiSeeder extends Seeder
{

    public function run(): void
    {
        $namaKompetensi = [
            'Dokter Spesialis Jantung',
            'Dokter Spesialis Penyakit Dalam',
            'Dokter Spesialis Anak',
            'Dokter Spesialis Bedah',
            'Perawat',
            'Bidan',
            'Ahli Gizi',
            'Tenaga Farmasi',
            'Petugas Laboratorium',
            'Ahli Radiologi',
            'Fisioterapis',
            'Petugas Administrasi',
            'Petugas Kebersihan',
        ];

        foreach ($namaKompetensi as $nama_kompetensi) {
            $total_tunjangan = rand(500000, 3000000);
            $created_at = Carbon::now()->subDays(rand(0, 365));
            $updated_at = Carbon::now();

            DB::table('kompetensis')->insert([
                'nama_kompetensi' => $nama_kompetensi,
                'jenis_kompetensi' => rand(0, 1),
                'total_tunjangan' => $total_tunjangan,
                'nilai_bor' => 120000,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ]);
        }
    }
}
