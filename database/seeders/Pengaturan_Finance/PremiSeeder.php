<?php

namespace Database\Seeders\Pengaturan_Finance;

use Carbon\Carbon;
use App\Models\Premi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PremiSeeder extends Seeder
{
    public function run(): void
    {
        Premi::create([
            'nama_premi' => 'BPJS Kesehatan',
            'sumber_potongan' => 'Gaji Bruto',
            'jenis_premi' => 0,
            'besaran_premi' => 1,
            'minimal_rate' => null,
            'maksimal_rate' => 12000000
        ]);

        Premi::create([
            'nama_premi' => 'BPJS Ketenagakerjaan',
            'sumber_potongan' => 'Gaji Pokok',
            'jenis_premi' => 1,
            'besaran_premi' => 12000,
            'minimal_rate' => 520000,
            'maksimal_rate' => 700000
        ]);

        Premi::create([
            'nama_premi' => 'Iuran Pensiun',
            'sumber_potongan' => 'Gaji Pokok',
            'jenis_premi' => 1,
            'besaran_premi' => 150000,
            'minimal_rate' => null,
            'maksimal_rate' => null
        ]);

        Premi::create([
            'nama_premi' => 'Jaminan Hari Tua',
            'sumber_potongan' => 'Gaji Pokok',
            'jenis_premi' => 0,
            'besaran_premi' => 1,
            'minimal_rate' => null,
            'maksimal_rate' => null
        ]);
    }
}
