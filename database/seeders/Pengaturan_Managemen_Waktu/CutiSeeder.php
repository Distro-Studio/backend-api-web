<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisCuti = [
            'Cuti Tahunan',
            'Cuti Sakit',
            'Cuti Melahirkan',
            'Cuti Kematian',
            'Cuti Nikah',
        ];

        for ($i = 0; $i < 20; $i++) {
            DB::table('tipe_cutis')->insert([
                'nama' => $jenisCuti[rand(0, count($jenisCuti) - 1)],
                'durasi' => rand(2, 31),
                'created_at' => Carbon::now()->subDays(rand(0, 365)),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
