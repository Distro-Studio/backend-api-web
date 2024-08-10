<?php

namespace Database\Seeders\Constant\Status;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusDiklatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['Menunggu Verifikasi', 'Verifikasi 1 Disetujui', 'Verifikasi 1 Ditolak', 'Verifikasi 2 Disetujui', 'Verifikasi 2 Ditolak'];
        foreach ($status as $status) {
            DB::table('status_diklats')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
