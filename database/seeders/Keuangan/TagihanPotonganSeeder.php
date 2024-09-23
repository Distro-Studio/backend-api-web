<?php

namespace Database\Seeders\Keuangan;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TagihanPotonganSeeder extends Seeder
{
    public function run()
    {
        $local_ind = Carbon::now('Asia/Jakarta');
        DB::table('tagihan_potongans')->insert([
            [
                'data_karyawan_id' => 6,
                'kategori_tagihan_id' => 1, // Obat
                'status_tagihan_id' => 1, // Belum Tertagih
                'besaran' => 150000,
                'tenor' => 4,
                'sisa_tenor' => 3,
                'sisa_tagihan' => 112500,
                'bulan_mulai' => Carbon::createFromFormat('d-m-Y', '01-08-2024')->format('d-m-Y'),
                'bulan_selesai' => Carbon::createFromFormat('d-m-Y', '01-12-2024')->format('d-m-Y'),
                'created_at' => $local_ind,
                'updated_at' => $local_ind,
            ],
            [
                'data_karyawan_id' => 2,
                'kategori_tagihan_id' => 2, // Koperasi
                'status_tagihan_id' => 2, // Tertagih
                'besaran' => 2800000,
                'tenor' => 2,
                'sisa_tenor' => 1,
                'sisa_tagihan' => 1400000,
                'bulan_mulai' => Carbon::createFromFormat('d-m-Y', '01-08-2024')->format('d-m-Y'),
                'bulan_selesai' => Carbon::createFromFormat('d-m-Y', '01-12-2024')->format('d-m-Y'),
                'created_at' => $local_ind,
                'updated_at' => $local_ind,
            ],
            [
                'data_karyawan_id' => 2,
                'kategori_tagihan_id' => 1, // Koperasi
                'status_tagihan_id' => 2, // Tertagih
                'besaran' => 200000,
                'tenor' => 3,
                'sisa_tenor' => 2,
                'sisa_tagihan' => 133333,
                'bulan_mulai' => Carbon::createFromFormat('d-m-Y', '01-08-2024')->format('d-m-Y'),
                'bulan_selesai' => Carbon::createFromFormat('d-m-Y', '01-11-2024')->format('d-m-Y'),
                'created_at' => $local_ind,
                'updated_at' => $local_ind,
            ],
            [
                'data_karyawan_id' => 3,
                'kategori_tagihan_id' => 1, // Obat
                'status_tagihan_id' => 3, // Terbayar
                'besaran' => 5000000,
                'tenor' => 3,
                'sisa_tenor' => 2,
                'sisa_tagihan' => 3333333,
                'bulan_mulai' => Carbon::createFromFormat('d-m-Y', '01-08-2024')->format('d-m-Y'),
                'bulan_selesai' => Carbon::createFromFormat('d-m-Y', '01-11-2024')->format('d-m-Y'),
                'created_at' => $local_ind,
                'updated_at' => $local_ind,
            ],
            [
                'data_karyawan_id' => 4,
                'kategori_tagihan_id' => 2, // Koperasi
                'status_tagihan_id' => 1, // Belum Tertagih
                'besaran' => 3000000,
                'tenor' => 8,
                'sisa_tenor' => 7,
                'sisa_tagihan' => 2625000,
                'bulan_mulai' => Carbon::createFromFormat('d-m-Y', '01-08-2024')->format('d-m-Y'),
                'bulan_selesai' => Carbon::createFromFormat('d-m-Y', '01-04-2025')->format('d-m-Y'),
                'created_at' => $local_ind,
                'updated_at' => $local_ind,
            ],
            [
                'data_karyawan_id' => 5,
                'kategori_tagihan_id' => 1, // Obat
                'status_tagihan_id' => 2, // Tertagih
                'besaran' => 1000000,
                'tenor' => 5,
                'sisa_tenor' => 4,
                'sisa_tagihan' => 800000,
                'bulan_mulai' => Carbon::createFromFormat('d-m-Y', '01-08-2024')->format('d-m-Y'),
                'bulan_selesai' => Carbon::createFromFormat('d-m-Y', '01-01-2025')->format('d-m-Y'),
                'created_at' => $local_ind,
                'updated_at' => $local_ind,
            ],
        ]);
    }
}
