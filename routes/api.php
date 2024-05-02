<?php

use App\Http\Controllers\Publik\Auth\LoginController;
use App\Http\Controllers\SuperAdmin\Pengaturan\Akun\Password\SA_UserController;
use App\Http\Controllers\SuperAdmin\Pengaturan\Akun\Permissions\SA_PermissionsController;
use App\Http\Controllers\SuperAdmin\Pengaturan\Akun\Roles\SA_RolesController;
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
        Route::group(['prefix' => 'super-admin'], function () {
            /* ==================================== User ==================================== */
            /* ==================================== User ==================================== */


            /* ==================================== Setting Akun ==================================== */
            // ! Roles ===========>
            Route::get('/all-roles', [SA_RolesController::class, 'getAllRoles']);
            Route::post('/roles/bulk-delete', [SA_RolesController::class, 'bulkDelete']);
            Route::get('/roles/export', [SA_RolesController::class, 'exportRoles']);
            Route::post('/roles/import', [SA_RolesController::class, 'importRoles']);
            Route::apiResource('/roles', SA_RolesController::class);

            // ! Roles Permission ===========>
            Route::get('/all-permissions', [SA_PermissionsController::class, 'getAllPermissions']);
            Route::put('/permissions/{role}', [SA_PermissionsController::class, 'updatePermissions']);

            // ! Change Password User ===========>
            Route::post('/users/change-passwords', [SA_UserController::class, 'updatePassword']);
            /* ==================================== Setting Akun ==================================== */


            /* ==================================== Setting Karyawan ==================================== */
            // ! Jabatan ===========>
            Route::get('/all-jabatans', [SA_JabatanController::class, 'getAllJabatan']);
            Route::post('/jabatans/bulk-delete', [SA_JabatanController::class, 'bulkDelete']);
            Route::get('/jabatans/export', [SA_JabatanController::class, 'exportJabatan']);
            Route::post('/jabatans/import', [SA_JabatanController::class, 'importJabatan']);
            Route::apiResource('/jabatans', SA_JabatanController::class);

            // ! Kelompok Gaji ===========>
            Route::get('/all-kelompok-gajis', [SA_KelompokGajiController::class, 'getAllKelompokGaji']);
            Route::post('/kelompok-gajis/bulk-delete', [SA_KelompokGajiController::class, 'bulkDelete']);
            Route::get('/kelompok-gajis/export', [SA_KelompokGajiController::class, 'exportKelompokGaji']);
            Route::post('/kelompok-gajis/import', [SA_KelompokGajiController::class, 'importKelompokGaji']);
            Route::apiResource('/kelompok-gajis', SA_KelompokGajiController::class);

            // ! Kompetensi ===========>
            Route::get('/all-kompetensis', [SA_KompetensiController::class, 'getAllKompetensi']);
            Route::post('/kompetensis/bulk-delete', [SA_KompetensiController::class, 'bulkDelete']);
            Route::get('/kompetensis/export', [SA_KompetensiController::class, 'exportKompetensi']);
            Route::post('/kompetensis/import', [SA_KompetensiController::class, 'importKompetensi']);
            Route::apiResource('/kompetensis', SA_KompetensiController::class);

            // ! Unit Kerja ===========>
            Route::get('/all-unit-kerjas', [SA_UnitKerjaController::class, 'getAllUnitKerja']);
            Route::post('/unit-kerjas/bulk-delete', [SA_UnitKerjaController::class, 'bulkDelete']);
            Route::get('/unit-kerjas/export', [SA_UnitKerjaController::class, 'exportUnitKerja']);
            Route::post('/unit-kerjas/import', [SA_UnitKerjaController::class, 'importUnitKerja']);
            Route::apiResource('/unit-kerjas', SA_UnitKerjaController::class);
            /* ==================================== Setting Karyawan ==================================== */


            /* ==================================== Setting Finance ==================================== */
            /* ==================================== Setting Finance ==================================== */


            /* ==================================== Setting Managemen Waktu ==================================== */
            /* ==================================== Setting Managemen Waktu ==================================== */
        });
    });
});
