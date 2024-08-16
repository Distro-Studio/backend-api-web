<?php

namespace Database\Seeders\Karyawan;

use App\Models\User;
use App\Models\Berkas;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\TrackRecord;
use Illuminate\Support\Str;
use App\Models\KategoriBerkas;
use App\Models\KategoriTrackRecord;
use Illuminate\Database\Seeder;
use App\Models\TransferKaryawan;
use App\Models\KategoriTransferKaryawan;
use App\Models\KelompokGaji;
use App\Models\StatusBerkas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;

class TransferKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unit_kerja_ids = UnitKerja::pluck('id')->all();
        $jabatan_ids = Jabatan::pluck('id')->all();
        $role_ids = Role::pluck('id')->all();
        $kelompok_gaji_ids = KelompokGaji::pluck('id')->all();
        $user_ids = User::where('nama', '!=', 'Super Admin')->pluck('id')->all();
        $kategori_transfer_ids = KategoriTransferKaryawan::pluck('id')->all();
        $kategori_record_ids = KategoriTrackRecord::pluck('id')->all();
        $kategoriBerkas = KategoriBerkas::where('label', 'System')->first();
        $statusBerkas = StatusBerkas::where('label', 'Menunggu')->first();

        for ($i = 0; $i < 50; $i++) {
            if (count($user_ids) <= $i) {
                break; // break if we run out of unique user_ids
            }

            $user_id = $user_ids[$i];
            $unit_kerja_asal = $unit_kerja_ids[array_rand($unit_kerja_ids)];
            $unit_kerja_tujuan = $unit_kerja_ids[array_rand($unit_kerja_ids)];

            // Ensure unit_kerja_asal and unit_kerja_tujuan are different
            while ($unit_kerja_tujuan == $unit_kerja_asal) {
                $unit_kerja_tujuan = $unit_kerja_ids[array_rand($unit_kerja_ids)];
            }

            $jabatan_asal = $jabatan_ids[array_rand($jabatan_ids)];
            $jabatan_tujuan = $jabatan_ids[array_rand($jabatan_ids)];

            // Ensure jabatan_asal and jabatan_tujuan are different
            while ($jabatan_tujuan == $jabatan_asal) {
                $jabatan_tujuan = $jabatan_ids[array_rand($jabatan_ids)];
            }

            $role_asal = $role_ids[array_rand($role_ids)];
            $role_tujuan = $role_ids[array_rand($role_ids)];

            // Ensure role_asal and role_tujuan are different
            while ($role_tujuan == $role_asal) {
                $role_tujuan = $role_ids[array_rand($role_ids)];
            }

            $kelompok_gaji_asal = $kelompok_gaji_ids[array_rand($kelompok_gaji_ids)];
            $kelompok_gaji_tujuan = $kelompok_gaji_ids[array_rand($kelompok_gaji_ids)];

            // Ensure kelompok_gaji_asal and kelompok_gaji_tujuan are different
            while ($kelompok_gaji_tujuan == $kelompok_gaji_asal) {
                $kelompok_gaji_tujuan = $kelompok_gaji_ids[array_rand($kelompok_gaji_ids)];
            }

            $kategori_transfer_id = $kategori_transfer_ids[array_rand($kategori_transfer_ids)];
            $kategori_records_id = $kategori_record_ids[array_rand($kategori_record_ids)];
            $dokumenPath = '/berkas/karyawan/karyawan-transfer/dokumen_' . $user_id;

            TransferKaryawan::create([
                'user_id' => $user_id,
                'tgl_mulai' => date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2024), mktime(0, 0, 0, 12, 31, 2024))),
                'unit_kerja_asal' => $unit_kerja_asal,
                'unit_kerja_tujuan' => $unit_kerja_tujuan,
                'jabatan_asal' => $jabatan_asal,
                'jabatan_tujuan' => $jabatan_tujuan,
                'kelompok_gaji_asal' => $kelompok_gaji_asal,
                'kelompok_gaji_tujuan' => $kelompok_gaji_tujuan,
                'role_asal' => $role_asal,
                'role_tujuan' => $role_tujuan,
                'kategori_transfer_id' => $kategori_transfer_id,
                'alasan' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quos rerum unde, culpa corporis impedit id sequi in tenetur laboriosam odit provident vel temporibus fugiat excepturi ex eum at? Rem, totam!',
                'dokumen' => $dokumenPath,
            ]);

            // Store in 'berkas' table
            Berkas::create([
                'user_id' => $user_id,
                'nama' => 'Berkas Transfer - ' . User::find($user_id)->nama,
                'kategori_berkas_id' => $kategoriBerkas->id,
                'status_berkas_id' => $statusBerkas->id,
                'path' => $dokumenPath,
                'tgl_upload' => now(),
                'nama_file' => 'dokumen_' . $user_id,
                'ext' => 'application/pdf',
                'size' => rand(2300, 4800),
                'file_id' => (string) Str::uuid(), // Generate random UUID for file_id
            ]);

            // Create track record
            // TrackRecord::create([
            //     'user_id' => $user_id,
            //     'kategori_record_id' => $kategori_records_id,
            //     'tgl_masuk' => date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2021), mktime(0, 0, 0, 12, 31, 2023))), // Example tgl_masuk
            //     'tgl_keluar' => now(), // Example tgl_keluar
            // ]);
        }
    }
}
