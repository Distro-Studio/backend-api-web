<?php

use App\Http\Controllers\Dashboard\Karyawan\AkunKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\KaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\KeluargaKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\PekerjaKontrakController;
use App\Http\Controllers\Dashboard\Karyawan\RekamJejakController;
use App\Http\Controllers\Dashboard\Karyawan\TransferKaryawanController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\PermissionsController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\RolesController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\UserPasswordController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\JadwalPenggajianController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\PremiController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\TER21Controller;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\JabatanController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\KelompokGajiController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\KompetensiController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\UnitKerjaController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\CutiController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\HariLiburController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\ShiftController;
use App\Http\Controllers\Dashboard\Presensi\PresensiController;
use App\Http\Controllers\Publik\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::group(['prefix' => 'rski/dashboard'], function () {
        Route::get('/logout', [LoginController::class, 'logout'])->middleware('web');
        Route::get('/user-info', [LoginController::class, 'getInfoUserLogin']);

        Route::group(['prefix' => '/karyawan'], function () {
            // ! Data Karyawan ===========>
            Route::get('/all-karyawan', [KaryawanController::class, 'getAllKaryawan']);
            Route::post('/karyawan-filter', [KaryawanController::class, 'index']);
            Route::post('/karyawan-search', [KaryawanController::class, 'index']);
            Route::post('/karyawan-bulk-delete', [KaryawanController::class, 'bulkDelete']);
            Route::get('/karyawan-export', [KaryawanController::class, 'exportKaryawan']);
            Route::post('/karyawan-import', [KaryawanController::class, 'importKaryawan']);
            Route::apiResource('/data-karyawan', KaryawanController::class);

            // ! Akun Karyawan ===========>
            Route::get('/all-akun-karyawan', [AkunKaryawanController::class, 'getAllAkunKaryawan']);
            Route::post('/akun-karyawan/filter', [AkunKaryawanController::class, 'index']);
            Route::post('/akun-karyawan/search', [AkunKaryawanController::class, 'index']);
            // Route::post('/akun-karyawan/bulk-delete', [AkunKaryawanController::class, 'bulkDelete']);
            Route::get('/akun-karyawan/export', [AkunKaryawanController::class, 'exportAkunKaryawan']);
            Route::apiResource('/akun-karyawan', AkunKaryawanController::class);

            // ! Keluarga Karyawan ===========>
            Route::get('/all-keluarga-karyawan', [KeluargaKaryawanController::class, 'getAllKeluargaKaryawan']);
            Route::post('/keluarga-karyawan/filter', [KeluargaKaryawanController::class, 'index']);
            Route::post('/keluarga-karyawan/search', [KeluargaKaryawanController::class, 'index']);
            // Route::post('/keluarga-karyawan/bulk-delete', [KeluargaKaryawanController::class, 'bulkDelete']);
            Route::get('/keluarga-karyawan/export', [KeluargaKaryawanController::class, 'exportKeluargaKaryawan']);
            Route::apiResource('/keluarga-karyawan', KeluargaKaryawanController::class);

            // ! Pekerja Kontrak ===========>
            Route::get('/all-pekerja-kontrak', [PekerjaKontrakController::class, 'getAllPekerjaKontrak']);
            Route::post('/pekerja-kontrak/filter', [PekerjaKontrakController::class, 'index']);
            Route::post('/pekerja-kontrak/search', [PekerjaKontrakController::class, 'index']);
            Route::get('/pekerja-kontrak/export', [PekerjaKontrakController::class, 'exportPekerjaKontrak']);
            Route::apiResource('/pekerja-kontrak', PekerjaKontrakController::class);

            // ! Rekam Jejak ===========>
            Route::get('/all-rekam-jejak', [RekamJejakController::class, 'getAllRekamJejak']);
            Route::post('/rekam-jejak/filter', [RekamJejakController::class, 'index']);
            Route::post('/rekam-jejak/search', [RekamJejakController::class, 'index']);
            Route::post('/rekam-jejak/bulk-delete', [RekamJejakController::class, 'bulkDelete']);
            Route::get('/rekam-jejak/export', [RekamJejakController::class, 'exportRekamJejak']);
            Route::post('/rekam-jejak/import', [RekamJejakController::class, 'importRekamJejak']);
            Route::apiResource('/rekam-jejak', RekamJejakController::class);

            // ! Transfer Karyawan ===========>
            Route::get('/all-transfer-karyawan', [TransferKaryawanController::class, 'getAllTransferKaryawan']);
            Route::post('/transfer-karyawan/filter', [TransferKaryawanController::class, 'index']);
            Route::post('/transfer-karyawan/search', [TransferKaryawanController::class, 'index']);
            Route::get('/transfer-karyawan/export', [TransferKaryawanController::class, 'exportTransferKaryawan']);
            Route::apiResource('/transfer-karyawan', TransferKaryawanController::class);
        });

        // ! export import presensi belum di test untuk gambar
        Route::group(['prefix' => '/presensi'], function () {
            // ! Presensi Tabel ===========>
            Route::get('/all-presensi', [PresensiController::class, 'getAllPresensi']);
            Route::post('/presensi-filter', [PresensiController::class, 'index']);
            Route::post('/presensi-search', [PresensiController::class, 'index']);
            Route::post('/presensi-bulk-delete', [PresensiController::class, 'bulkDelete']);
            Route::get('/presensi-export', [PresensiController::class, 'exportPresensi']);
            Route::post('/presensi-import', [PresensiController::class, 'importPresensi']);
            Route::apiResource('/data-presensi', PresensiController::class);

            Route::get('/calculated', [PresensiController::class, 'calculatedPresensi']);
        });

        Route::group(['prefix' => '/jadwal'], function () {
            // ! Jadwal ===========>
            // ! Tukar Jadwal ===========>
            // ! Lembur ===========>
            // ! Cuti ===========>
        });

        Route::group(['prefix' => '/keuangan'], function () {
        });

        Route::group(['prefix' => '/perusahaan'], function () {
        });

        Route::group(['prefix' => '/pengaturan'], function () {
            /* ==================================== Setting Akun ==================================== */
            // ! Roles ===========>
            Route::get('/all-role', [RolesController::class, 'getAllRoles']);
            Route::post('/role/filter', [RolesController::class, 'index']);
            Route::post('/role/search', [RolesController::class, 'index']);
            Route::post('/role/bulk-delete', [RolesController::class, 'bulkDelete']);
            Route::get('/role/export', [RolesController::class, 'exportRoles']);
            Route::post('/role/import', [RolesController::class, 'importRoles']);
            Route::apiResource('/role', RolesController::class);

            // ! Roles Permission ===========>
            Route::get('/all-permissions', [PermissionsController::class, 'getAllPermissions']);
            Route::put('/permissions/{role}', [PermissionsController::class, 'updatePermissions']);

            // ! Change Password User ===========>
            Route::post('/users/change-passwords', [UserPasswordController::class, 'updatePassword']);
            /* ==================================== Setting Akun ==================================== */


            /* ==================================== Setting Karyawan ==================================== */
            // ! Jabatan ===========>
            Route::get('/all-jabatan', [JabatanController::class, 'getAllJabatan']);
            Route::post('/jabatan/filter', [JabatanController::class, 'index']);
            Route::post('/jabatan/search', [JabatanController::class, 'index']);
            Route::post('/jabatan/bulk-delete', [JabatanController::class, 'bulkDelete']);
            Route::get('/jabatan/export', [JabatanController::class, 'exportJabatan']);
            Route::post('/jabatan/import', [JabatanController::class, 'importJabatan']);
            Route::apiResource('/jabatan', JabatanController::class);

            // ! Kelompok Gaji ===========>
            Route::get('/all-kelompok-gaji', [KelompokGajiController::class, 'getAllKelompokGaji']);
            Route::post('/kelompok-gaji/filter', [KelompokGajiController::class, 'index']);
            Route::post('/kelompok-gaji/search', [KelompokGajiController::class, 'index']);
            Route::post('/kelompok-gaji/bulk-delete', [KelompokGajiController::class, 'bulkDelete']);
            Route::get('/kelompok-gaji/export', [KelompokGajiController::class, 'exportKelompokGaji']);
            Route::post('/kelompok-gaji/import', [KelompokGajiController::class, 'importKelompokGaji']);
            Route::apiResource('/kelompok-gaji', KelompokGajiController::class);

            // ! Kompetensi ===========>
            Route::get('/all-kompetensi', [KompetensiController::class, 'getAllKompetensi']);
            Route::post('/kompetensi/filter', [KompetensiController::class, 'index']);
            Route::post('/kompetensi/search', [KompetensiController::class, 'index']);
            Route::post('/kompetensi/bulk-delete', [KompetensiController::class, 'bulkDelete']);
            Route::get('/kompetensi/export', [KompetensiController::class, 'exportKompetensi']);
            Route::post('/kompetensi/import', [KompetensiController::class, 'importKompetensi']);
            Route::apiResource('/kompetensi', KompetensiController::class);

            // ! Unit Kerja ===========>
            Route::get('/all-unit-kerja', [UnitKerjaController::class, 'getAllUnitKerja']);
            Route::post('/unit-kerja/filter', [UnitKerjaController::class, 'index']);
            Route::post('/unit-kerja/search', [UnitKerjaController::class, 'index']);
            Route::post('/unit-kerja/bulk-delete', [UnitKerjaController::class, 'bulkDelete']);
            Route::get('/unit-kerja/export', [UnitKerjaController::class, 'exportUnitKerja']);
            Route::post('/unit-kerja/import', [UnitKerjaController::class, 'importUnitKerja']);
            Route::apiResource('/unit-kerja', UnitKerjaController::class);
            /* ==================================== Setting Karyawan ==================================== */


            /* ==================================== Setting Finance ==================================== */
            // ! Premi ===========>
            Route::get('/all-premi', [PremiController::class, 'getAllPremi']);
            Route::post('/premi/filter', [PremiController::class, 'index']);
            Route::post('/premi/search', [PremiController::class, 'index']);
            Route::post('/premi/bulk-delete', [PremiController::class, 'bulkDelete']);
            Route::get('/premi/export', [PremiController::class, 'exportPremi']);
            Route::post('/premi/import', [PremiController::class, 'importPremi']);
            Route::apiResource('/premi', PremiController::class);

            // ! TER21 ===========>
            Route::get('/all-ter-pph-21', [TER21Controller::class, 'getAllTer']);
            Route::post('/ter-pph-21/filter', [TER21Controller::class, 'index']);
            Route::post('/ter-pph-21/search', [TER21Controller::class, 'index']);
            Route::post('/ter-pph-21/bulk-delete', [TER21Controller::class, 'bulkDelete']);
            Route::get('/ter-pph-21/export', [TER21Controller::class, 'exportTER']);
            Route::post('/ter-pph-21/import', [TER21Controller::class, 'importTER']);
            Route::apiResource('/ter-pph-21', TER21Controller::class);

            // ! Jadwal Penggajian ===========>
            Route::post('/jadwal-penggajian', [JadwalPenggajianController::class, 'createJadwalPenggajian']);
            // Route::post('/jadwal-penggajian/reset/{jadwalPenggajian}', [JadwalPenggajianController::class, 'resetJadwalPenggajian']);

            // ! THR ===========>
            // TODO: Belum THR
            /* ==================================== Setting Finance ==================================== */


            /* ==================================== Setting Managemen Waktu ==================================== */
            // ! Shift ===========>
            Route::get('/all-shift', [ShiftController::class, 'getAllShift']);
            Route::post('/shift/filter', [ShiftController::class, 'index']);
            Route::post('/shift/search', [ShiftController::class, 'index']);
            Route::post('/shift/bulk-delete', [ShiftController::class, 'bulkDelete']);
            Route::get('/shift/export', [ShiftController::class, 'exportShift']);
            Route::post('/shift/import', [ShiftController::class, 'importShift']);
            Route::apiResource('/shift', ShiftController::class);

            // ! Hari Libur ===========>
            Route::get('/hari-libur/nasional', [HariLiburController::class, 'getNasionalHariLibur']);
            Route::post('/hari-libur/filter', [HariLiburController::class, 'index']);
            Route::post('/hari-libur/search', [HariLiburController::class, 'index']);
            Route::get('/all-hari-libur', [HariLiburController::class, 'getAllHariLibur']);
            Route::post('/hari-libur/bulk-delete', [HariLiburController::class, 'bulkDelete']);
            Route::get('/hari-libur/export', [HariLiburController::class, 'exportHariLibur']);
            Route::post('/hari-libur/import', [HariLiburController::class, 'importHariLibur']);
            Route::apiResource('/hari-libur', HariLiburController::class);

            // ! Cuti ===========>
            Route::get('/all-cuti', [CutiController::class, 'getAllCuti']);
            Route::post('/cuti/filter', [CutiController::class, 'index']);
            Route::post('/cuti/search', [CutiController::class, 'index']);
            Route::post('/cuti/bulk-delete', [CutiController::class, 'bulkDelete']);
            Route::get('/cuti/export', [CutiController::class, 'exportCuti']);
            Route::post('/cuti/import', [CutiController::class, 'importCuti']);
            Route::apiResource('/cuti', CutiController::class);
            /* ==================================== Setting Managemen Waktu ==================================== */
        });
    });
});
