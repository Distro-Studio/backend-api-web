<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Jawaban;
use App\Models\Pertanyaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JawabanSeeder extends Seeder
{
    public function run()
    {
        // Dapatkan semua user yang ada
        $users = User::where('nama', '!=', 'Super Admin')->pluck('id')->toArray();

        // Dapatkan semua pertanyaan yang ada
        $pertanyaans = DB::table('pertanyaans')->pluck('id')->toArray();

        // Buat seeder untuk jawaban
        foreach ($pertanyaans as $pertanyaan_id) {
            foreach ($users as $user_penilai) {
                // Hindari penilaian diri sendiri
                $user_dinilai = $this->getRandomUserDinilai($users, $user_penilai);

                // Random jawaban dari list jawaban
                $jawaban = rand(1, 5);

                DB::table('jawabans')->insert([
                    'user_penilai' => $user_penilai,
                    // 'user_dinilai' => $user_dinilai,
                    'pertanyaan_id' => $pertanyaan_id,
                    'jawaban' => $jawaban,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Dapatkan user dinilai yang berbeda dari user penilai
     *
     * @param array $users
     * @param int $user_penilai
     * @return int
     */
    private function getRandomUserDinilai(array $users, int $user_penilai): int
    {
        $filtered_users = array_filter($users, function ($user) use ($user_penilai) {
            return $user !== $user_penilai;
        });

        return $filtered_users[array_rand($filtered_users)];
    }
}
