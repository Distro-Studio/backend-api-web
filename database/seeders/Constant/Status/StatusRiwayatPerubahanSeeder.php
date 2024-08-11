<?php

namespace Database\Seeders\Constant\Status;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusRiwayatPerubahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['Menunggu', 'Diverifikasi', 'Ditolak'];

        foreach ($status as $status) {
            DB::table('status_perubahans')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
