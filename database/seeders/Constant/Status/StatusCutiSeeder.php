<?php

namespace Database\Seeders\Constant\Status;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusCutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $status = ['Menunggu', 'Disetujui', 'Ditolak'];
        $status = ['Menunggu Verifikasi', 'Verifikasi 1 Disetujui', 'Verifikasi 1 Ditolak', 'Verifikasi 2 Disetujui', 'Verifikasi 2 Ditolak'];

        foreach ($status as $status) {
            DB::table('status_cutis')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
