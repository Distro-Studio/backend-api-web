<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HariLiburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $randomHari = [
            'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
        ];

        for ($i = 0; $i < 30; $i++) {
            DB::table('hari_liburs')->insert([
                'nama' => $randomHari[rand(0, count($randomHari) - 1)],
                'tanggal' => Carbon::now()->subDays(rand(0, 365))->format('Y-m-d H:i:s'),
                'created_at' => Carbon::now()->subDays(rand(0, 365)),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
