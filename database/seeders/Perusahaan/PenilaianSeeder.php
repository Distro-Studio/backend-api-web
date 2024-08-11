<?php

namespace Database\Seeders\Perusahaan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Penilaian;
use App\Models\UnitKerja;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PenilaianSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('nama', '!=', 'Super Admin')->get();

        foreach ($users as $user) {
            for ($i = 0; $i < 10; $i++) {
                $userPenilai = $users->random();

                // Pastikan user penilai dan user dinilai tidak sama
                if ($userPenilai->id == $user->id) {
                    continue;
                }

                // Ambil unit kerja dan jabatan yang sama dengan user dinilai
                $unitKerjaDinilai = $user->data_karyawans->unit_kerjas->id ?? null;
                $jabatanDinilai = $user->data_karyawans->jabatans->id ?? null;

                // Ambil unit kerja dan jabatan yang sama dengan user penilai
                $unitKerjaPenilai = $userPenilai->data_karyawans->unit_kerjas->id ?? null;
                $jabatanPenilai = $userPenilai->data_karyawans->jabatans->id ?? null;

                // Jika tidak ditemukan unit kerja atau jabatan, gunakan nilai random
                if (!$unitKerjaDinilai) {
                    $unitKerjaDinilai = UnitKerja::inRandomOrder()->first()->id;
                }

                if (!$jabatanDinilai) {
                    $jabatanDinilai = Jabatan::inRandomOrder()->first()->id;
                }

                if (!$unitKerjaPenilai) {
                    $unitKerjaPenilai = UnitKerja::inRandomOrder()->first()->id;
                }

                if (!$jabatanPenilai) {
                    $jabatanPenilai = Jabatan::inRandomOrder()->first()->id;
                }

                $totalPertanyaan = rand(5, 10); // Random total pertanyaan untuk penilaian ini

                Penilaian::create([
                    'user_dinilai' => $user->id,
                    'user_penilai' => $userPenilai->id,
                    'unit_kerja_dinilai' => $unitKerjaDinilai,
                    'unit_kerja_penilai' => $unitKerjaPenilai,
                    'jabatan_dinilai' => $jabatanDinilai,
                    'jabatan_penilai' => $jabatanPenilai,
                    'total_pertanyaan' => $totalPertanyaan,
                    'rata_rata' => 0, // Placeholder, akan dihitung nanti setelah jawaban di-input
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
