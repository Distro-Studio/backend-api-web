<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\JadwalKaryawan\CutiJadwalSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\Karyawan\KaryawanSeeder;
use Database\Seeders\Presensi\PresensiSeeder;
use Database\Seeders\JadwalKaryawan\JadwalSeeder;
use Database\Seeders\JadwalKaryawan\LemburSeeder;
use Database\Seeders\JadwalKaryawan\TukarJadwalSeeder;
use Database\Seeders\Pengaturan_Finance\TERSeeder;
use Database\Seeders\Pengaturan_Finance\PremiSeeder;
use Database\Seeders\Karyawan\KeluargaKaryawanSeeder;
use Database\Seeders\Karyawan\TrackRecordSeeder;
use Database\Seeders\Karyawan\TransferKaryawanSeeder;
use Database\Seeders\Keuangan\PenggajianSeeder;
use Database\Seeders\Keuangan\RiwayatPenggajianSeeder;
use Database\Seeders\Keuangan\TanggalGajiSeeder;
use Database\Seeders\Keuangan\THRPenggajianSeeder;
use Database\Seeders\Pengaturan_Managemen_Waktu\ShiftSeeder;
use Database\Seeders\Pengaturan_Managemen_Waktu\HariLiburSeeder;
use Database\Seeders\Pengaturan_Managemen_Waktu\TipeCutiSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AccountSeeder::class,

            /* ==== Pengaturan Karyawan ==== */
            JabatanSeeder::class,
            KelompokGajiSeeder::class,
            KompetensiSeeder::class,
            UnitKerjaSeeder::class,
            PertanyaanSeeder::class,

            /* ==== Pengaturan Finance ==== */
            KategoriTERSeeder::class,
            PTKPSeeder::class,
            PremiSeeder::class,
            TERSeeder::class,

            /* ==== Pengaturan Managemen Waktu ==== */
            ShiftSeeder::class,
            HariLiburSeeder::class,
            TipeCutiSeeder::class,

            /* ==== Karyawan ==== */
            KaryawanSeeder::class,
            KeluargaKaryawanSeeder::class,
            TransferKaryawanSeeder::class,
            TrackRecordSeeder::class,

            /* ==== Jadwals ==== */
            JadwalSeeder::class,
            LemburSeeder::class,
            CutiJadwalSeeder::class,
            TukarJadwalSeeder::class,

            /* ==== Presensi ==== */
            PresensiSeeder::class,

            /* ==== Keuangan ==== */
            TanggalGajiSeeder::class,
            PenggajianSeeder::class,
            RiwayatPenggajianSeeder::class,
            THRPenggajianSeeder::class,
        ]);
    }
}
