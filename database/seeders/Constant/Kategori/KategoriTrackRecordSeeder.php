<?php

namespace Database\Seeders\Constant\Kategori;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriTrackRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['Perubahan Data', 'Mutasi Pegawai', 'Promosi Karyawan', 'Feedback'];

        foreach ($kategori as $kategori) {
            DB::table('kategori_track_records')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
