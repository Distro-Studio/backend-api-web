<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\TrackRecord;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TrackRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::has('data_karyawans')->with('data_karyawans')->get();
        $totalRecords = 50;
        $createdRecords = 0;

        while ($createdRecords < $totalRecords) {
            foreach ($users as $user) {
                $dataKaryawan = $user->data_karyawans;

                if ($dataKaryawan) {
                    // Tentukan jumlah record yang akan dibuat untuk user ini
                    $recordsToCreate = rand(1, 4);

                    for ($j = 0; $j < $recordsToCreate && $createdRecords < $totalRecords; $j++) {
                        TrackRecord::create([
                            'user_id' => $user->id,
                            'tgl_masuk' => $dataKaryawan->tgl_masuk,
                            'tgl_keluar' => $dataKaryawan->tgl_keluar ? $dataKaryawan->tgl_keluar : null,
                            'promosi' => 'Promosi ' . $user->id . ' ke-' . ($j + 1),
                            'mutasi' => 'Mutasi ' . $user->id . ' ke-' . ($j + 1),
                            'penghargaan' => 'Penghargaan ' . $user->id . ' ke-' . ($j + 1),
                        ]);
                        $createdRecords++;
                    }

                    if ($createdRecords >= $totalRecords) {
                        break;
                    }
                }
            }
        }
    }
}
