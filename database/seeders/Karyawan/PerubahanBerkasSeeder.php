<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\Berkas;
use App\Models\DataKaryawan;
use App\Models\PerubahanBerkas;
use App\Models\StatusPerubahan;
use Illuminate\Database\Seeder;
use App\Models\RiwayatPerubahan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PerubahanBerkasSeeder extends Seeder
{
    public function run(): void
    {
        $dataKaryawans = DataKaryawan::pluck('id')->all();
        $statusPerubahans = StatusPerubahan::pluck('id')->all();
        $berkasList = Berkas::pluck('id')->all();

        for ($i = 0; $i < 15; $i++) {
            // Create RiwayatPerubahan
            $riwayatPerubahan = RiwayatPerubahan::create([
                'data_karyawan_id' => $dataKaryawans[array_rand($dataKaryawans)],
                'jenis_perubahan' => 'Berkas', // Assuming we are seeding Berkas changes here
                'kolom' => ['file_id', 'nama', 'path', 'tgl_upload', 'nama_file', 'ext', 'size'][array_rand(['file_id', 'nama', 'path', 'tgl_upload', 'nama_file', 'ext', 'size'])],
                'original_data' => 'Sophia Bowen',
                'updated_data' => 'Oscar Cohen',
                'status_perubahan_id' => $statusPerubahans[array_rand($statusPerubahans)],
                'verifikator_1' => null,
                'alasan' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Get a random Berkas record
            $berkas = Berkas::find($berkasList[array_rand($berkasList)]);

            // Create PerubahanBerkas linked to the RiwayatPerubahan
            PerubahanBerkas::create([
                'riwayat_perubahan_id' => $riwayatPerubahan->id,
                'berkas_id' => $berkas->id,
                'file_id' => $berkas->file_id,
                'nama' => $berkas->nama,
                'path' => $berkas->path,
                'tgl_upload' => $berkas->tgl_upload,
                'nama_file' => $berkas->nama_file,
                'ext' => $berkas->ext,
                'size' => $berkas->size,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
