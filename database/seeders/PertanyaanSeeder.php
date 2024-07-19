<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Jabatan;
use App\Models\Pertanyaan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PertanyaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = Jabatan::all();
        foreach ($jabatans as $index => $jabatan) {
            Pertanyaan::create([
                'pertanyaan' => 'Pertanyaan untuk jabatan ' . $jabatan->nama_jabatan . ' - [' . ($index + 1) . ']',
                'jabatan_id' => $jabatan->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
