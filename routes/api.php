<?php

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
use App\Http\Controllers\Publik\Auth\LoginController;
use Illuminate\Http\Request;
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
    Route::group(['prefix' => 'rski/dahsboard'], function () {
        Route::get('/logout', [LoginController::class, 'logout'])->middleware('web');

        /* ==================================== User ==================================== */
        /* ==================================== User ==================================== */


        /* ==================================== Setting Akun ==================================== */
        // ! Roles ===========>
        Route::get('/all-roles', [RolesController::class, 'getAllRoles']);
        Route::post('/roles/bulk-delete', [RolesController::class, 'bulkDelete']);
        Route::get('/roles/export', [RolesController::class, 'exportRoles']);
        Route::post('/roles/import', [RolesController::class, 'importRoles']);
        Route::apiResource('/roles', RolesController::class);

        // ! Roles Permission ===========>
        Route::get('/all-permissions', [PermissionsController::class, 'getAllPermissions']);
        Route::put('/permissions/{role}', [PermissionsController::class, 'updatePermissions']);

        // ! Change Password User ===========>
        Route::post('/users/change-passwords', [UserPasswordController::class, 'updatePassword']);
        /* ==================================== Setting Akun ==================================== */


        /* ==================================== Setting Karyawan ==================================== */
        // ! Jabatan ===========>
        Route::get('/all-jabatans', [JabatanController::class, 'getAllJabatan']);
        Route::post('/jabatans/bulk-delete', [JabatanController::class, 'bulkDelete']);
        Route::get('/jabatans/export', [JabatanController::class, 'exportJabatan']);
        Route::post('/jabatans/import', [JabatanController::class, 'importJabatan']);
        Route::apiResource('/jabatans', JabatanController::class);

        // ! Kelompok Gaji ===========>
        Route::get('/all-kelompok-gajis', [KelompokGajiController::class, 'getAllKelompokGaji']);
        Route::post('/kelompok-gajis/bulk-delete', [KelompokGajiController::class, 'bulkDelete']);
        Route::get('/kelompok-gajis/export', [KelompokGajiController::class, 'exportKelompokGaji']);
        Route::post('/kelompok-gajis/import', [KelompokGajiController::class, 'importKelompokGaji']);
        Route::apiResource('/kelompok-gajis', KelompokGajiController::class);

        // ! Kompetensi ===========>
        Route::get('/all-kompetensis', [KompetensiController::class, 'getAllKompetensi']);
        Route::post('/kompetensis/bulk-delete', [KompetensiController::class, 'bulkDelete']);
        Route::get('/kompetensis/export', [KompetensiController::class, 'exportKompetensi']);
        Route::post('/kompetensis/import', [KompetensiController::class, 'importKompetensi']);
        Route::apiResource('/kompetensis', KompetensiController::class);

        // ! Unit Kerja ===========>
        Route::get('/all-unit-kerjas', [UnitKerjaController::class, 'getAllUnitKerja']);
        Route::post('/unit-kerjas/bulk-delete', [UnitKerjaController::class, 'bulkDelete']);
        Route::get('/unit-kerjas/export', [UnitKerjaController::class, 'exportUnitKerja']);
        Route::post('/unit-kerjas/import', [UnitKerjaController::class, 'importUnitKerja']);
        Route::apiResource('/unit-kerjas', UnitKerjaController::class);
        /* ==================================== Setting Karyawan ==================================== */


        /* ==================================== Setting Finance ==================================== */
        // ! Premi ===========>
        Route::get('/all-premis', [PremiController::class, 'getAllPremi']);
        Route::post('/premis/bulk-delete', [PremiController::class, 'bulkDelete']);
        Route::get('/premis/export', [PremiController::class, 'exportPremi']);
        Route::post('/premis/import', [PremiController::class, 'importPremi']);
        Route::apiResource('/premis', PremiController::class);

        // ! TER21 ===========>
        Route::get('/all-ter-pph-21', [TER21Controller::class, 'getAllTer']);
        Route::post('/ter-pph-21/bulk-delete', [TER21Controller::class, 'bulkDelete']);
        Route::get('/ter-pph-21/export', [TER21Controller::class, 'exportTER']);
        Route::post('/ter-pph-21/import', [TER21Controller::class, 'importTER']);
        Route::apiResource('/ter-pph-21', TER21Controller::class);

        // ! Jadwal Penggajian ===========>
        Route::post('/jadwal-penggajians', [JadwalPenggajianController::class, 'createJadwalPenggajian']);
        // Route::post('/jadwal-penggajians/reset/{jadwalPenggajian}', [JadwalPenggajianController::class, 'resetJadwalPenggajian']);

        // ! THR ===========>

        /* ==================================== Setting Finance ==================================== */


        /* ==================================== Setting Managemen Waktu ==================================== */
        // ! Shift ===========>
        Route::get('/all-shifts', [ShiftController::class, 'getAllShift']);
        Route::post('/shifts/bulk-delete', [ShiftController::class, 'bulkDelete']);
        Route::get('/shifts/export', [ShiftController::class, 'exportShift']);
        Route::post('/shifts/import', [ShiftController::class, 'importShift']);
        Route::apiResource('/shifts', ShiftController::class);

        // ! Hari Libur ===========>
        Route::get('/hari-libur/nasional', [HariLiburController::class, 'getNasionalHariLibur']);
        Route::get('/all-hari-liburs', [HariLiburController::class, 'getAllHariLibur']);
        Route::post('/hari-liburs/bulk-delete', [HariLiburController::class, 'bulkDelete']);
        Route::get('/hari-liburs/export', [HariLiburController::class, 'exportHariLibur']);
        Route::post('/hari-liburs/import', [HariLiburController::class, 'importHariLibur']);
        Route::apiResource('/hari-liburs', HariLiburController::class);


        // ! Cuti ===========>
        Route::get('/all-cutis', [CutiController::class, 'getAllCuti']);
        Route::post('/cutis/bulk-delete', [CutiController::class, 'bulkDelete']);
        Route::get('/cutis/export', [CutiController::class, 'exportCuti']);
        Route::post('/cutis/import', [CutiController::class, 'importCuti']);
        Route::apiResource('/cutis', CutiController::class);
        /* ==================================== Setting Managemen Waktu ==================================== */
    });
});
