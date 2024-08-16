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
        $cutiData = [
            [
                'nama' => 'Cuti Tahunan',
                'kuota' => 12,
                'is_need_requirement' => false,
                'keterangan' => 'Maksimal cuti 12 hari',
                'cuti_administratif' => true
            ],
            [
                'nama' => 'Cuti Melahirkan',
                'kuota' => 3 * 30, // 3 bulan
                'is_need_requirement' => false,
                'keterangan' => 'Maksimal cuti 3 bulan setelah melahirkan',
                'cuti_administratif' => false
            ],
            [
                'nama' => 'Cuti Sakit',
                'kuota' => 30,
                'is_need_requirement' => true,
                'keterangan' => 'Cuti membutuhkan surat dokter',
                'cuti_administratif' => false
            ],
            [
                'nama' => 'Cuti Luar Tanggungan',
                'kuota' => 30,
                'is_need_requirement' => false,
                'keterangan' => 'Maksimal cuti 30 hari dalam 1 tahun',
                'cuti_administratif' => false
            ],
            [
                'nama' => 'Cuti Besar',
                'kuota' => 12,
                'is_need_requirement' => true,
                'keterangan' => 'Maksimal cuti 12 hari, jika masa kerja lebih dari 8 tahun',
                'cuti_administratif' => true
            ],
            [
                'nama' => 'Cuti Nikah',
                'kuota' => 3,
                'is_need_requirement' => false,
                'keterangan' => 'Maksimal cuti 3 hari (untuk pernikahan pertama)',
                'cuti_administratif' => false
            ],
            [
                'nama' => 'Cuti Kematian',
                'kuota' => 2,
                'is_need_requirement' => false,
                'keterangan' => 'Maksimal cuti 2 hari (sejak tanggal kematian keluarga/saudara/kerabat)',
                'cuti_administratif' => false
            ],
        ];

        foreach ($cutiData as $data) {
            DB::table('tipe_cutis')->insert($data);
        }
    }
}
