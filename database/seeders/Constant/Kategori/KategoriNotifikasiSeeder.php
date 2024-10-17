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
        $kategori = ['Cuti', 'Tukar Jadwal', 'Lembur', 'Sertifikat Diklat', 'Slip Gajiku', 'Dokumen', 'Feedback', 'Laporan', 'Koperasi', 'Perizinan', 'Perubahan Data', 'Data Keluarga Karyawan', 'Diklat Internal', 'Diklat Eksternal', 'Peringatan Masa SIP'];
        foreach ($kategori as $kategori) {
            DB::table('kategori_notifikasis')->insert([
                'label' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
