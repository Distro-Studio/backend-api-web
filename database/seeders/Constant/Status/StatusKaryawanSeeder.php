<?php

namespace Database\Seeders\Constant\Status;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['Tetap', 'Kontrak', 'Magang', 'Outsourcing', 'Paruh Waktu'];

        foreach ($status as $status) {
            DB::table('status_karyawans')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
