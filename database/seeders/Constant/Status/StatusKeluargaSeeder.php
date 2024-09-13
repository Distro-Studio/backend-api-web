<?php

namespace Database\Seeders\Constant\Status;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusKeluargaSeeder extends Seeder
{
    public function run(): void
    {
        $status = ['Menunggu', 'Disetujui', 'Ditolak'];

        foreach ($status as $status) {
            DB::table('status_keluargas')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
