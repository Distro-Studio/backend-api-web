<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Database\Seeders\Pengaturan_Finance\TERSeeder;
use Database\Seeders\Pengaturan_Finance\PremiSeeder;

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
        ]);
    }
}
