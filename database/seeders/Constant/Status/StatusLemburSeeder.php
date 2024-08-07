<?php

namespace Database\Seeders\Constant\Status;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusLemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['Menunggu', 'Berlangsung', 'Selesai'];

        foreach ($status as $status) {
            DB::table('status_lemburs')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
