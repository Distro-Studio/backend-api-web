<?php

namespace Database\Seeders\Constant\Status;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusTukarJadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $status = ['Menunggu', 'Disetujui', 'Tidak Disetujui'];
        $status = ['Menunggu Verifikasi', 'Verifikasi 1 Disetujui', 'Verifikasi 1 Ditolak', 'Verifikasi 2 Disetujui', 'Verifikasi 2 Ditolak'];

        foreach ($status as $status) {
            DB::table('status_tukar_jadwals')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
