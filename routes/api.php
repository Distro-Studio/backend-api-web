<?php

use App\Http\Controllers\Publik\Auth\LoginController;
use App\Http\Controllers\SuperAdmin\Pengaturan\Karyawan\Jabatan\SA_JabatanController;
use App\Http\Controllers\SuperAdmin\Pengaturan\Karyawan\KelompokGaji\SA_KelompokGajiController;
use App\Http\Controllers\SuperAdmin\Pengaturan\Karyawan\Kompetensi\SA_KompetensiController;
use App\Http\Controllers\SuperAdmin\Pengaturan\Karyawan\UnitKerja\SA_UnitKerjaController;
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
    Route::get('/logout', [LoginController::class, 'logout'])->middleware('web');

    Route::middleware(['role:Super Admin'])->group(function () {
        /* ==================================== User ==================================== */
        /* ==================================== User ==================================== */


        /* ==================================== Setting Akun ==================================== */
        // ! Roles ===========>
        Route::get('super-admin/all-jabatans', [SA_JabatanController::class, 'getAllJabatan']);
        Route::post('super-admin/jabatans/bulk-delete', [SA_JabatanController::class, 'bulkDelete']);
        Route::get('super-admin/jabatans/export', [SA_JabatanController::class, 'exportJabatan']);
        Route::post('super-admin/jabatans/import', [SA_JabatanController::class, 'importJabatan']);
        Route::apiResource('super-admin/jabatans', SA_JabatanController::class);
        
        // ! Change Password User ===========>
        /* ==================================== Setting Akun ==================================== */


        /* ==================================== Setting Karyawan ==================================== */
        // ! Jabatan ===========>
        Route::get('super-admin/all-jabatans', [SA_JabatanController::class, 'getAllJabatan']);
        Route::post('super-admin/jabatans/bulk-delete', [SA_JabatanController::class, 'bulkDelete']);
        Route::get('super-admin/jabatans/export', [SA_JabatanController::class, 'exportJabatan']);
        Route::post('super-admin/jabatans/import', [SA_JabatanController::class, 'importJabatan']);
        Route::apiResource('super-admin/jabatans', SA_JabatanController::class);

        // ! Kelompok Gaji ===========>
        Route::get('super-admin/all-kelompok-gajis', [SA_KelompokGajiController::class, 'getAllKelompokGaji']);
        Route::post('super-admin/kelompok-gajis/bulk-delete', [SA_KelompokGajiController::class, 'bulkDelete']);
        Route::get('super-admin/kelompok-gajis/export', [SA_KelompokGajiController::class, 'exportKelompokGaji']);
        Route::post('super-admin/kelompok-gajis/import', [SA_KelompokGajiController::class, 'importKelompokGaji']);
        Route::apiResource('super-admin/kelompok-gajis', SA_KelompokGajiController::class);

        // ! Kompetensi ===========>
        Route::get('super-admin/all-kompetensis', [SA_KompetensiController::class, 'getAllKompetensi']);
        Route::post('super-admin/kompetensis/bulk-delete', [SA_KompetensiController::class, 'bulkDelete']);
        Route::get('super-admin/kompetensis/export', [SA_KompetensiController::class, 'exportKompetensi']);
        Route::post('super-admin/kompetensis/import', [SA_KompetensiController::class, 'importKompetensi']);
        Route::apiResource('super-admin/kompetensis', SA_KompetensiController::class);

        // ! Unit Kerja ===========>
        Route::get('super-admin/all-unit-kerjas', [SA_UnitKerjaController::class, 'getAllUnitKerja']);
        Route::post('super-admin/unit-kerjas/bulk-delete', [SA_UnitKerjaController::class, 'bulkDelete']);
        Route::get('super-admin/unit-kerjas/export', [SA_UnitKerjaController::class, 'exportUnitKerja']);
        Route::post('super-admin/unit-kerjas/import', [SA_UnitKerjaController::class, 'importUnitKerja']);
        Route::apiResource('super-admin/unit-kerjas', SA_UnitKerjaController::class);
        /* ==================================== Setting Karyawan ==================================== */


        /* ==================================== Setting Finance ==================================== */
        /* ==================================== Setting Finance ==================================== */


        /* ==================================== Setting Managemen Waktu ==================================== */
        /* ==================================== Setting Managemen Waktu ==================================== */
    });

    Route::middleware(['role: Direktur'])->group(function () {
    });

    Route::middleware(['role: Admin'])->group(function () {
    });
});
