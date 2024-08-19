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
        DB::table('hari_liburs')->insert([
            'nama' => 'Minggu',
            'tanggal' => Carbon::now()->subDays(rand(0, 365))->format('Y-m-d H:i:s')
        ]);
    }
}
