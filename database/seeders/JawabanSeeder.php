<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Jawaban;
use App\Models\Penilaian;
use App\Models\Pertanyaan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JawabanSeeder extends Seeder
{
    public function run(): void
    {
        $penilaians = Penilaian::all();
        $pertanyaans = Pertanyaan::all();

        foreach ($penilaians as $penilaian) {
            $totalNilai = 0;
            $pertanyaanForJabatan = $pertanyaans->where('jabatan_id', $penilaian->jabatan_dinilai);

            foreach ($pertanyaanForJabatan as $pertanyaan) {
                $nilaiJawaban = rand(1, 5); // Nilai jawaban fiktif antara 1 dan 5
                $totalNilai += $nilaiJawaban;

                Jawaban::create([
                    'penilaian_id' => $penilaian->id,
                    'pertanyaan_id' => $pertanyaan->id,
                    'jawaban' => $nilaiJawaban,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Update rata-rata pada penilaian setelah semua jawaban dimasukkan
            $rataRata = $penilaian->total_pertanyaan > 0 ? $totalNilai / $penilaian->total_pertanyaan : 0;
            $penilaian->update(['rata_rata' => $rataRata]);
        }
    }
}
