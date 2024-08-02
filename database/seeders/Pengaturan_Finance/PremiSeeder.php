<?php

namespace Database\Seeders\Pengaturan_Finance;

use App\Models\Premi;
use Illuminate\Database\Seeder;

class PremiSeeder extends Seeder
{
    public function run(): void
    {
        Premi::create([
            'nama_premi' => 'BPJS Kesehatan',
            'kategori_potongan_id' => 1,
            'jenis_premi' => 0,
            'besaran_premi' => 1,
            'minimal_rate' => null,
            'maksimal_rate' => 12000000
        ]);

        Premi::create([
            'nama_premi' => 'BPJS Ketenagakerjaan',
            'kategori_potongan_id' => 2,
            'jenis_premi' => 1,
            'besaran_premi' => 12000,
            'minimal_rate' => 520000,
            'maksimal_rate' => 700000
        ]);

        Premi::create([
            'nama_premi' => 'Iuran Pensiun',
            'kategori_potongan_id' => 2,
            'jenis_premi' => 1,
            'besaran_premi' => 150000,
            'minimal_rate' => null,
            'maksimal_rate' => null
        ]);

        Premi::create([
            'nama_premi' => 'Jaminan Hari Tua',
            'kategori_potongan_id' => 2,
            'jenis_premi' => 0,
            'besaran_premi' => 1,
            'minimal_rate' => null,
            'maksimal_rate' => null
        ]);
    }
}
