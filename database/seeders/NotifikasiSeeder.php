<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NotifikasiSeeder extends Seeder
{
    public function run()
    {
        $kategoriNotifikasiId = DB::table('kategori_notifikasis')->pluck('id')->all();
        $notifications = [
            [
                'kategori_notifikasi_id' => $kategoriNotifikasiId[array_rand($kategoriNotifikasiId)],
                'user_id' => 1,
                'message' => 'Pengumuman penting untuk semua karyawan.',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kategori_notifikasi_id' => $kategoriNotifikasiId[array_rand($kategoriNotifikasiId)],
                'user_id' => 1,
                'message' => 'Jadwal rapat diubah menjadi pukul 10:00.',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kategori_notifikasi_id' => $kategoriNotifikasiId[array_rand($kategoriNotifikasiId)],
                'user_id' => 1,
                'message' => 'Tugas baru telah ditambahkan ke proyek Anda.',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kategori_notifikasi_id' => $kategoriNotifikasiId[array_rand($kategoriNotifikasiId)],
                'user_id' => 1,
                'message' => 'Silakan perbarui informasi pribadi Anda di sistem.',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kategori_notifikasi_id' => $kategoriNotifikasiId[array_rand($kategoriNotifikasiId)],
                'user_id' => 1,
                'message' => 'Anda memiliki pesan baru dari manajer Anda.',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('notifikasis')->insert($notifications);
    }
}
