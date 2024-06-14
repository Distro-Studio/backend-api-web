<?php

namespace Database\Seeders\JadwalKaryawan;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\User;
use App\Models\TipeCuti;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CutiJadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::pluck('id')->all();
        $tipeCutis = TipeCuti::pluck('id')->all();

        $cutiData = [];

        for ($i = 0; $i < 20; $i++) {
            $from = Carbon::now()->subDays(rand(0, 30));
            $to = Carbon::now()->addDays(rand(1, 10));

            $selectedUserId = $users[array_rand($users)];
            $cutiData[] = [
                'user_id' => $selectedUserId,
                'tipe_cuti_id' => $tipeCutis[array_rand($tipeCutis)],
                'tgl_from' => $from,
                'tgl_to' => $to,
                'catatan' => 'Catatan cuti ' . ($i + 1) . ' dari pengguna ' . $selectedUserId,
                'durasi' => rand(1, 20),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('cutis')->insert($cutiData);
    }
}
