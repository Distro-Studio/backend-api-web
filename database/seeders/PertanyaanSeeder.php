<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Jabatan;
use App\Models\Pertanyaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PertanyaanSeeder extends Seeder
{
    public function run()
    {
        // Dapatkan data jenis penilaian yang ada di tabel jenis_penilaians
        $jenisPenilaianIds = DB::table('jenis_penilaians')->pluck('id')->toArray();

        // Daftar pertanyaan yang akan dimasukkan
        $pertanyaans = [
            'Bagaimana kualitas kerja karyawan ini?',
            'Seberapa baik karyawan ini dalam bekerja sama dengan tim?',
            'Apakah karyawan ini menunjukkan inisiatif dalam pekerjaannya?',
            'Seberapa baik karyawan ini dalam menyelesaikan tugas tepat waktu?',
            'Bagaimana karyawan ini menangani tekanan kerja?',
            'Apakah karyawan ini menunjukkan kemampuan komunikasi yang baik?',
            'Seberapa baik karyawan ini dalam mengikuti instruksi dan prosedur?',
            'Bagaimana karyawan ini beradaptasi dengan perubahan di tempat kerja?',
            'Apakah karyawan ini menunjukkan sikap yang positif di tempat kerja?',
            'Seberapa baik karyawan ini dalam belajar hal-hal baru?'
        ];

        // Iterasi untuk memasukkan pertanyaan ke dalam tabel pertanyaans
        foreach ($jenisPenilaianIds as $jenisPenilaianId) {
            foreach ($pertanyaans as $pertanyaan) {
                DB::table('pertanyaans')->insert([
                    'pertanyaan' => $pertanyaan,
                    'jenis_penilaian_id' => $jenisPenilaianId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
