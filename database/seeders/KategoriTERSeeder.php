<?php

namespace Database\Seeders;

use App\Models\KategoriTer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriTERSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $created_at = Carbon::now()->subDays(rand(0, 365));
        $updated_at = Carbon::now();

        KategoriTer::create([
            'nama_kategori_ter' => 'TER Kategori A',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        KategoriTer::create([
            'nama_kategori_ter' => 'TER Kategori B',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        KategoriTer::create([
            'nama_kategori_ter' => 'TER Kategori C',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);
    }
}
