<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Karyawan\KaryawanSeeder;
use Database\Seeders\Karyawan\KeluargaKaryawanSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\Pengaturan_Finance\TERSeeder;
use Database\Seeders\Pengaturan_Finance\PremiSeeder;
use Database\Seeders\Pengaturan_Managemen_Waktu\CutiSeeder;
use Database\Seeders\Pengaturan_Managemen_Waktu\HariLiburSeeder;
use Database\Seeders\Pengaturan_Managemen_Waktu\ShiftSeeder;

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

            /* ==== Pengaturan Finance ==== */
            KategoriTERSeeder::class,
            PTKPSeeder::class,
            PremiSeeder::class,
            TERSeeder::class,

            /* ==== Pengaturan Managemen Waktu ==== */
            ShiftSeeder::class,
            HariLiburSeeder::class,
            CutiSeeder::class,

            /* ==== Karyawan ==== */
            KaryawanSeeder::class,
            KeluargaKaryawanSeeder::class,
        ]);
    }
}
