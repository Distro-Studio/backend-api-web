<?php

namespace Database\Seeders\Pengaturan_Managemen_Waktu;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TipeCutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisCuti = [
            'Cuti Tahunan',
            'Cuti Sakit',
            'Cuti Melahirkan',
            'Cuti Menikah',
            'Cuti Kematian',
            'Cuti Besar',
            'Cuti Haji',
            'Cuti Pendidikan',
            'Cuti Bersama',
            'Cuti Alasan Penting',
            'Cuti Adopsi Anak',
            'Cuti Umroh',
            'Cuti Tugas Negara',
            'Cuti Tugas Belajar',
            'Cuti Menunggu Pensiun',
            'Cuti Menjaga Anak Sakit',
            'Cuti Menjaga Orang Tua Sakit',
            'Cuti Keluarga Dekat Meninggal',
            'Cuti Pindah Rumah',
            'Cuti Perjalanan Dinas Luar Negeri',
        ];

        foreach ($jenisCuti as $cuti) {
            DB::table('tipe_cutis')->insert([
                'nama' => $cuti,
                'durasi' => rand(2, 31),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
