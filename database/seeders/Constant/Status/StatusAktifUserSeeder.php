<?php

namespace Database\Seeders\Constant\Status;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusAktifUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['Belum Aktif', 'Aktif', 'Tidak Aktif'];
        foreach ($status as $status) {
            DB::table('status_aktifs')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
