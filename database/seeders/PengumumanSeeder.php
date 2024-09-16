<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PengumumanSeeder extends Seeder
{
    public function run()
    {
        $currentDate = Carbon::now('Asia/Jakarta');
        $user_ids = User::where('nama', '!=', 'Super Admin')->pluck('id')->all();

        $pengumumans = [
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 1',
                'konten' => 'Konten pengumuman 1',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(1)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 2',
                'konten' => 'Konten pengumuman 2',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(2)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 3',
                'konten' => 'Konten pengumuman 3',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(3)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 4',
                'konten' => 'Konten pengumuman 4',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(4)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 5',
                'konten' => 'Konten pengumuman 5',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(5)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 6',
                'konten' => 'Konten pengumuman 6',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(6)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 7',
                'konten' => 'Konten pengumuman 7',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(7)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 8',
                'konten' => 'Konten pengumuman 8',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(8)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 9',
                'konten' => 'Konten pengumuman 9',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(9)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
            [
                'user_id' => $user_ids[array_rand($user_ids)],
                'judul' => 'Pengumuman 10',
                'konten' => 'Konten pengumuman 10',
                'is_read' => false,
                'tgl_berakhir' => $currentDate->copy()->addDays(10)->format('Y-m-d'),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ],
        ];

        DB::table('pengumumans')->insert($pengumumans);
    }
}
