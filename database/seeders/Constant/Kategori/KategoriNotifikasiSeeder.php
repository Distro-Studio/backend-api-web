<?php

namespace Database\Seeders\Constant\Kategori;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriNotifikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = ['Cuti', 'Tukar Jadwal', 'Lembur', 'Event & Diklat', 'Slip Gajiku', 'Dokumen', 'Feedback', 'Laporan', 'Koperasi'];
        foreach ($kategori as $kategori) {
            DB::table('kategori_notifikasis')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
