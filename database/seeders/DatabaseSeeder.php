<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Constant\Kategori\KategoriActivityLogSeeder;
use Database\Seeders\Constant\Kategori\KategoriAgamaSeeder;
use Database\Seeders\Constant\Kategori\KategoriBerkasSeeder;
use Database\Seeders\Constant\Kategori\KategoriDarahSeeder;
use Database\Seeders\Constant\Kategori\KategoriGajiSeeder;
use Database\Seeders\Constant\Kategori\KategoriKompensasiSeeder;
use Database\Seeders\Constant\Kategori\KategoriNotifikasiSeeder;
use Database\Seeders\Constant\Kategori\KategoriPotonganSeeder;
use Database\Seeders\Constant\Kategori\KategoriPresensiSeeder;
use Database\Seeders\Constant\Kategori\KategoriTrackRecordSeeder;
use Database\Seeders\Constant\Kategori\KategoriTransferKaryawanSeeder;
use Database\Seeders\Constant\Kategori\KategoriTukarJadwalSeeder;
use Database\Seeders\Constant\Status\StatusCutiSeeder;
use Database\Seeders\Constant\Status\StatusGajiSeeder;
use Database\Seeders\Constant\Status\StatusKaryawanSeeder;
use Database\Seeders\Constant\Status\StatusLemburSeeder;
use Database\Seeders\Constant\Status\StatusPresensiSeeder;
use Database\Seeders\Constant\Status\StatusTukarJadwalSeeder;
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
use Database\Seeders\Pengaturan_Managemen_Waktu\LokasiKantorSeeder;
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

            /* ==== Kategori & Status ==== */
            KategoriActivityLogSeeder::class,
            KategoriAgamaSeeder::class,
            KategoriBerkasSeeder::class,
            KategoriGajiSeeder::class,
            KategoriNotifikasiSeeder::class,
            KategoriPresensiSeeder::class,
            KategoriTukarJadwalSeeder::class,
            KategoriTransferKaryawanSeeder::class,
            KategoriDarahSeeder::class,
            KategoriTrackRecordSeeder::class,
            KategoriKompensasiSeeder::class,
            KategoriPotonganSeeder::class,
            StatusCutiSeeder::class,
            StatusGajiSeeder::class,
            StatusKaryawanSeeder::class,
            StatusLemburSeeder::class,
            StatusTukarJadwalSeeder::class,

            LokasiKantorSeeder::class,

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

            // /* ==== Jadwals ==== */
            JadwalSeeder::class,
            // LemburSeeder::class,
            // CutiJadwalSeeder::class,
            // TukarJadwalSeeder::class,

            // /* ==== Presensi ==== */
            PresensiSeeder::class,

            // /* ==== Keuangan ==== */
            // TanggalGajiSeeder::class,
            // PenggajianSeeder::class,
            // RiwayatPenggajianSeeder::class,
            // THRPenggajianSeeder::class,
        ]);
    }
}
