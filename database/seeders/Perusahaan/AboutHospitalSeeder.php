<?php

namespace Database\Seeders\Perusahaan;

use Carbon\Carbon;
use App\Models\AboutHospital;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AboutHospitalSeeder extends Seeder
{
    public function run()
    {
        $berkasA1 = DB::table('berkas')->insertGetId([
            'user_id' => 1,
            'file_id' => '123456', // Sesuaikan dengan data yang valid atau gunakan generator acak
            'nama' => 'rumah_sakit_a_1',
            'kategori_berkas_id' => 5, // Asumsikan kategori untuk berkas hospital
            'status_berkas_id' => 2, // Diverifikasi
            'path' => 'about_hospitals/rumah_sakit_a_1.jpg',
            'tgl_upload' => now(),
            'nama_file' => 'rumah_sakit_a_1',
            'ext' => 'jpg',
            'size' => '500KB',
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        $berkasA2 = DB::table('berkas')->insertGetId([
            'user_id' => 1,
            'file_id' => '123457',
            'nama' => 'rumah_sakit_a_2',
            'kategori_berkas_id' => 5,
            'status_berkas_id' => 2,
            'path' => 'about_hospitals/rumah_sakit_a_2.jpg',
            'tgl_upload' => now(),
            'nama_file' => 'rumah_sakit_a_2',
            'ext' => 'jpg',
            'size' => '500KB',
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        $berkasA3 = DB::table('berkas')->insertGetId([
            'user_id' => 1,
            'file_id' => '123458',
            'nama' => 'rumah_sakit_a_3',
            'kategori_berkas_id' => 5,
            'status_berkas_id' => 2,
            'path' => 'about_hospitals/rumah_sakit_a_3.jpg',
            'tgl_upload' => now(),
            'nama_file' => 'rumah_sakit_a_3',
            'ext' => 'jpg',
            'size' => '500KB',
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        $berkasB1 = DB::table('berkas')->insertGetId([
            'user_id' => 1,
            'file_id' => '123459',
            'nama' => 'rumah_sakit_b_1',
            'kategori_berkas_id' => 5,
            'status_berkas_id' => 2,
            'path' => 'about_hospitals/rumah_sakit_b_1.jpg',
            'tgl_upload' => now(),
            'nama_file' => 'rumah_sakit_b_1',
            'ext' => 'jpg',
            'size' => '500KB',
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        $berkasB2 = DB::table('berkas')->insertGetId([
            'user_id' => 1,
            'file_id' => '123460',
            'nama' => 'rumah_sakit_b_2',
            'kategori_berkas_id' => 5,
            'status_berkas_id' => 2,
            'path' => 'about_hospitals/rumah_sakit_b_2.jpg',
            'tgl_upload' => now(),
            'nama_file' => 'rumah_sakit_b_2',
            'ext' => 'jpg',
            'size' => '500KB',
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        $berkasB3 = DB::table('berkas')->insertGetId([
            'user_id' => 1,
            'file_id' => '123461',
            'nama' => 'rumah_sakit_b_3',
            'kategori_berkas_id' => 5,
            'status_berkas_id' => 2,
            'path' => 'about_hospitals/rumah_sakit_b_3.jpg',
            'tgl_upload' => now(),
            'nama_file' => 'rumah_sakit_b_3',
            'ext' => 'jpg',
            'size' => '500KB',
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        // Insert ke tabel about_hospitals
        DB::table('about_hospitals')->insert([
            [
                'konten' => 'Rumah Sakit A adalah fasilitas kesehatan terkemuka di Jakarta dengan pelayanan medis yang berkualitas.',
                'about_hospital_1' => $berkasA1,
                'about_hospital_2' => $berkasA2,
                'about_hospital_3' => $berkasA3,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta')
            ],
            [
                'konten' => 'Rumah Sakit B menawarkan layanan kesehatan modern dengan peralatan medis canggih dan tim medis yang profesional.',
                'about_hospital_1' => $berkasB1,
                'about_hospital_2' => $berkasB2,
                'about_hospital_3' => $berkasB3,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta')
            ],
        ]);
    }
}
