<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Jawaban;
use App\Models\Pertanyaan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JawabanSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua pengguna dari tabel `users`
        $users = User::where('nama', '!=', 'Super Admin')->get();

        // Ambil semua pertanyaan dari tabel `pertanyaans`
        $pertanyaans = Pertanyaan::all();

        // Looping melalui setiap pengguna
        foreach ($users as $user) {
            // Looping melalui setiap pertanyaan untuk memberikan jawaban
            foreach ($pertanyaans as $pertanyaan) {
                Jawaban::create([
                    'user_id' => $user->id, // ID pengguna
                    'pertanyaan_id' => $pertanyaan->id, // ID pertanyaan
                    'jawaban' => rand(1, 5), // Menghasilkan nilai acak dalam range 1-5
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
