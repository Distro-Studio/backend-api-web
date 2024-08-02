<?php

use App\Http\Controllers\Dashboard\Jadwal\CutiJadwalController;
use App\Http\Controllers\Dashboard\Jadwal\JadwalController;
use App\Http\Controllers\Dashboard\Jadwal\LemburJadwalController;
use App\Http\Controllers\Dashboard\Jadwal\TukarJadwalController;
use App\Http\Controllers\Dashboard\Karyawan\AkunKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\DataKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\DataTransferKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\KaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\KeluargaKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\PekerjaKontrakController;
use App\Http\Controllers\Dashboard\Karyawan\RekamJejakController;
use App\Http\Controllers\Dashboard\Karyawan\TransferKaryawanController;
use App\Http\Controllers\Dashboard\Keuangan\PenggajianController;
use App\Http\Controllers\Dashboard\Keuangan\PenyesuaianGajiController;
use App\Http\Controllers\Dashboard\Keuangan\THRPenggajianController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\PermissionsController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\RolesController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\UserPasswordController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\JadwalPenggajianController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\PremiController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\TER21Controller;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\THRController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\JabatanController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\KelompokGajiController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\KompetensiController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\PertanyaanController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\UnitKerjaController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\CutiController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\HariLiburController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\LokasiKantorController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\ShiftController;
use App\Http\Controllers\Dashboard\Presensi\DataPresensiController;
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
    // ! Global Request ===========>
    Route::get('/get-list-user', [DataKaryawanController::class, 'getAllDataUser']);
    Route::get('/get-list-unit-kerja', [DataKaryawanController::class, 'getAllDataUnitKerja']);
    Route::get('/get-list-jabatan', [DataKaryawanController::class, 'getAllDataJabatan']);
    Route::get('/get-list-status-karyawan', [DataKaryawanController::class, 'getAllDataStatusKaryawan']);
    Route::get('/get-list-kompetensi', [DataKaryawanController::class, 'getAllDataKompetensi']);
    Route::get('/get-list-role', [DataKaryawanController::class, 'getAllDataRole']);
    Route::get('/get-list-kelompok-gaji', [DataKaryawanController::class, 'getAllDataKelompokGaji']);
    Route::get('/get-list-ptkp', [DataKaryawanController::class, 'getAllDataPTKP']);
    Route::get('/get-list-kategori-transfer', [DataTransferKaryawanController::class, 'getAllKategoriTransfer']);
    Route::get('/get-lokasi-kantor', [DataPresensiController::class, 'getLokasiKantor']);
    Route::get('/get-list-premi', [DataKaryawanController::class, 'getAllDataPremi']);
    Route::get('/get-list-pph21', [TER21Controller::class, 'getAllTer']);
    Route::get('/get-list-tipecuti', [CutiController::class, 'getAllTipeCuti']);
    Route::get('/get-list-harilibur', [HariLiburController::class, 'getAllHariLibur']);
    Route::get('/get-list-shift', [ShiftController::class, 'getAllShift']);
    Route::get('/get-list-pertanyaan', [PertanyaanController::class, 'getAllPertanyaan']);

    Route::get('/get-list-premi', [DataKaryawanController::class, 'getAllDataPremi']);
    Route::get('/get-list-premi', [DataKaryawanController::class, 'getAllDataPremi']);

    Route::group(['prefix' => 'rski/dashboard'], function () {
        Route::get('/logout', [LoginController::class, 'logout'])->middleware('web');
        Route::get('/user-info', [LoginController::class, 'getInfoUserLogin']);

        // TODO: aktifkan send email di create & transfer karyawan
        // TODO: ganti email di create & transfer karyawan

        // TODO: 1. ketika create karyawan, status_aktif tetap false --done--
        // TODO: 2. setelah data_completion_step berubah menjadi true maka lakukan persetujuan dengan update status_aktif menjadi true
        // TODO: 3. ketika persetujuan ditolak, ubah data_completion_step dan status_aktif menjadi false

        // ! Ubah export berdasarkan filter
        // ! Buat fitur detail karyawan
        Route::group(['prefix' => '/karyawan'], function () {
            // ! Data Karyawan ===========>
            Route::post('/get-data-karyawan', [DataKaryawanController::class, 'index']);
            Route::get('/export', [DataKaryawanController::class, 'exportKaryawan']);
            Route::post('/import', [DataKaryawanController::class, 'importKaryawan']);
            Route::post('/{id}/non-aktif', [DataKaryawanController::class, 'deactivateKaryawan']);
            Route::get('/detail-karyawan-user/{user_id}', [DataKaryawanController::class, 'showByUserId']);
            Route::get('/detail-karyawan/{data_karyawan_id}', [DataKaryawanController::class, 'showByDataKaryawanId']);
            Route::get('/detail-karyawan-presensi/{data_karyawan_id}', [DataKaryawanController::class, 'getDataPresensi']);
            Route::apiResource('/data-karyawan', DataKaryawanController::class);

            // ! Transfer Karyawan ===========>
            Route::post('/transfer/get-data-trasnfer', [DataTransferKaryawanController::class, 'index']);
            Route::get('/transfer/export', [DataTransferKaryawanController::class, 'exportTransferKaryawan']);
            Route::apiResource('/transfer', DataTransferKaryawanController::class);
        });

        Route::group(['prefix' => '/presensi'], function () {
            // ! Presensi Tabel ===========>
            Route::post('/get-data-presensi', [DataPresensiController::class, 'index']);
            Route::post('/filter', [DataPresensiController::class, 'index']);
            Route::post('/search', [DataPresensiController::class, 'index']);
            Route::post('/export', [DataPresensiController::class, 'exportPresensi']);
            Route::post('/import', [DataPresensiController::class, 'importPresensi']);
            Route::get('/download-template', [DataPresensiController::class, 'downloadPresensiTemplate']);
            Route::apiResource('/data-presensi', DataPresensiController::class);

            Route::get('/calculated', [DataPresensiController::class, 'calculatedPresensi']);
        });

        // TODO: Tipe data di lemburs masih rancu, apakah tipe cuti atau yg lainnya
        // TODO: test jadwal
        Route::group(['prefix' => '/jadwal-karyawan'], function () {
            // ! Jadwal ===========>
            Route::get('/all-users-unitkerja', [JadwalController::class, 'getAllKaryawanUnitKerja']);
            Route::get('/all-shift', [ShiftController::class, 'getAllShift']);

            Route::get('/all-data-jadwal', [JadwalController::class, 'getAllJadwalKaryawan']);
            Route::post('/jadwal-filter', [JadwalController::class, 'index']);
            Route::post('/jadwal-search', [JadwalController::class, 'index']);
            Route::post('/data-jadwal/create-shift/{userId}', [JadwalController::class, 'createShiftKaryawan']);
            Route::get('/jadwal-export', [JadwalController::class, 'exportJadwalKaryawan']);
            Route::post('/jadwal-import', [JadwalController::class, 'importJadwalKaryawan']);
            Route::apiResource('/data-jadwal', JadwalController::class);

            // ! Tukar Jadwal ===========>
            Route::get('/get-karyawan-jadwal-unit', [TukarJadwalController::class, 'getKaryawanJadwal']);
            Route::get('/get-shift-by-date', [TukarJadwalController::class, 'getShiftbyDate']);
            Route::get('/get-karyawan-by-shift-and-date', [TukarJadwalController::class, 'getKaryawanByShiftAndDate']);

            Route::get('/all-tukar-jadwal', [TukarJadwalController::class, 'getAllJadwalTukar']);
            Route::post('/tukar-jadwal-filter', [TukarJadwalController::class, 'index']);
            Route::post('/tukar-jadwal-search', [TukarJadwalController::class, 'index']);
            Route::get('/tukar-jadwal-export', [TukarJadwalController::class, 'exportJadwalTukar']);
            Route::apiResource('/tukar-jadwal', TukarJadwalController::class);

            // ! Lembur ===========>
            Route::get('/all-users-unitkerja', [JadwalController::class, 'getAllKaryawanUnitKerja']);

            Route::get('/all-data-lembur', [LemburJadwalController::class, 'getAllJadwalLembur']);
            Route::post('/lembur-filter', [LemburJadwalController::class, 'index']);
            Route::post('/lembur-search', [LemburJadwalController::class, 'index']);
            Route::get('/lembur-export', [LemburJadwalController::class, 'exportJadwalLembur']);
            Route::apiResource('/data-lembur', LemburJadwalController::class);

            // ! Cuti ===========>
            Route::get('/all-users-unitkerja', [JadwalController::class, 'getAllKaryawanUnitKerja']);
            Route::get('/all-cuti', [CutiController::class, 'getAllTipeCuti']);

            Route::get('/all-data-jadwal-cuti', [CutiJadwalController::class, 'getAllJadwalCuti']);
            Route::post('/jadwal-cuti-filter', [CutiJadwalController::class, 'index']);
            Route::post('/jadwal-cuti-search', [CutiJadwalController::class, 'index']);
            Route::get('/jadwal-cuti-export', [CutiJadwalController::class, 'exportJadwalCuti']);
            Route::apiResource('/data-jadwal-cuti', CutiJadwalController::class);
        });

        // TODO: buat view restore data
        Route::group(['prefix' => '/keuangan'], function () {
            // ! Penggajian ===========>
            Route::get('/calculated-info-penggajian', [PenggajianController::class, 'calculatedInfo']);

            Route::post('/penggajian-filter', [PenggajianController::class, 'index']);
            Route::post('/penggajian-search', [PenggajianController::class, 'index']);
            Route::get('/penggajian-export-riwayat', [PenggajianController::class, 'exportRiwayatPenggajian']);
            Route::get('/penggajian-export-penerimaan', [PenggajianController::class, 'exportRekapPenerimaanGaji']);
            Route::get('/penggajian-export-potongan', [PenggajianController::class, 'exportRekapPotonganGaji']);
            Route::get('/penggajian-export-bank', [PenggajianController::class, 'exportLaporanGajiBank']);
            Route::get('/data-penggajian/detail/{penggajian_id}', [PenggajianController::class, 'showDetailGajiUser']);
            Route::post('/data-penggajian/detail/{penggajian_id}/create-penambah-gaji', [PenyesuaianGajiController::class, 'storePenyesuaianGajiPenambah']);
            Route::post('/data-penggajian/detail/{penggajian_id}/create-pengurang-gaji', [PenyesuaianGajiController::class, 'storePenyesuaianGajiPengurang']);
            Route::apiResource('/data-penggajian', PenggajianController::class);

            // ! Penyesuaian Gaji ===========>
            Route::get('/all-karyawan-penggajian', [PenyesuaianGajiController::class, 'getAllKaryawanPenggajian']);
            Route::post('/penyesuaian-gaji-filter', [PenyesuaianGajiController::class, 'index']);
            Route::post('/penyesuaian-gaji-search', [PenyesuaianGajiController::class, 'index']);
            Route::get('/penyesuaian-gaji-export', [PenyesuaianGajiController::class, 'exportPenyesuaianGaji']);
            Route::apiResource('/data-penyesuaian-gaji', PenyesuaianGajiController::class);

            // ! THR Penggajian ===========>
            Route::get('/all-karyawan-tetap', [THRPenggajianController::class, 'getDataKaryawan']);
            Route::post('/thr-filter', [THRPenggajianController::class, 'index']);
            Route::post('/thr-search', [THRPenggajianController::class, 'index']);
            Route::get('/thr-export', [THRPenggajianController::class, 'exportTHRPenggajian']);
            Route::apiResource('/data-thr-penggajian', THRPenggajianController::class);
        });

        // TODO: Diklat validasi dari permission
        // TODO: export diklat base on karyawan yg sudah diklat pada diklat tertentu
        Route::group(['prefix' => '/perusahaan'], function () {
        });

        Route::group(['prefix' => '/pengaturan'], function () {
            /* ==================================== Setting Akun ==================================== */
            // ! Roles ===========>
            Route::post('/role/filter', [RolesController::class, 'index']);
            Route::post('/role/search', [RolesController::class, 'index']);
            Route::post('/role/restore/{id}', [RolesController::class, 'restore']);
            Route::get('/role/export', [RolesController::class, 'exportRoles']);
            Route::post('/role/import', [RolesController::class, 'importRoles']);
            Route::apiResource('/role', RolesController::class);

            // ! Roles Permission ===========>
            Route::get('/all-permissions', [PermissionsController::class, 'getAllPermissions']);
            Route::put('/permissions/{role}', [PermissionsController::class, 'updatePermissions']);

            // ! Change Password ===========>
            Route::post('/users/change-passwords', [UserPasswordController::class, 'updatePassword']);
            /* ==================================== Setting Akun ==================================== */


            /* ==================================== Setting Karyawan ==================================== */
            // ! Jabatan ===========>
            Route::post('/jabatan/filter', [JabatanController::class, 'index']);
            Route::post('/jabatan/search', [JabatanController::class, 'index']);
            Route::post('/jabatan/restore/{id}', [JabatanController::class, 'restore']);
            Route::get('/jabatan/export', [JabatanController::class, 'exportJabatan']);
            Route::post('/jabatan/import', [JabatanController::class, 'importJabatan']);
            Route::apiResource('/jabatan', JabatanController::class);

            // ! Kelompok Gaji ===========>
            Route::post('/kelompok-gaji/filter', [KelompokGajiController::class, 'index']);
            Route::post('/kelompok-gaji/search', [KelompokGajiController::class, 'index']);
            Route::post('/kelompok-gaji/restore/{id}', [KelompokGajiController::class, 'restore']);
            Route::get('/kelompok-gaji/export', [KelompokGajiController::class, 'exportKelompokGaji']);
            Route::post('/kelompok-gaji/import', [KelompokGajiController::class, 'importKelompokGaji']);
            Route::apiResource('/kelompok-gaji', KelompokGajiController::class);

            // ! Kompetensi ===========>
            Route::post('/kompetensi/filter', [KompetensiController::class, 'index']);
            Route::post('/kompetensi/search', [KompetensiController::class, 'index']);
            Route::post('/kompetensi/restore/{id}', [KompetensiController::class, 'restore']);
            Route::get('/kompetensi/export', [KompetensiController::class, 'exportKompetensi']);
            Route::post('/kompetensi/import', [KompetensiController::class, 'importKompetensi']);
            Route::apiResource('/kompetensi', KompetensiController::class);

            // ! Unit Kerja ===========>
            Route::post('/unit-kerja/filter', [UnitKerjaController::class, 'index']);
            Route::post('/unit-kerja/search', [UnitKerjaController::class, 'index']);
            Route::post('/unit-kerja/restore/{id}', [UnitKerjaController::class, 'restore']);
            Route::get('/unit-kerja/export', [UnitKerjaController::class, 'exportUnitKerja']);
            Route::post('/unit-kerja/import', [UnitKerjaController::class, 'importUnitKerja']);
            Route::apiResource('/unit-kerja', UnitKerjaController::class);

            // ! Pertanyaan ===========>
            Route::get('/all-pertanyaan', [PertanyaanController::class, 'getAllPertanyaan']);
            Route::post('/pertanyaan/search', [PertanyaanController::class, 'index']);
            Route::post('/pertanyaan/restore/{id}', [PertanyaanController::class, 'restore']);
            Route::get('/pertanyaan/export', [PertanyaanController::class, 'exportPertanyaan']);
            Route::post('/pertanyaan/import', [PertanyaanController::class, 'importPertanyaan']);
            Route::apiResource('/pertanyaan', PertanyaanController::class);
            /* ==================================== Setting Karyawan ==================================== */


            /* ==================================== Setting Finance ==================================== */
            // ! Premi ===========>
            Route::get('/all-premi', [PremiController::class, 'getAllPremi']);
            Route::post('/premi/filter', [PremiController::class, 'index']);
            Route::post('/premi/search', [PremiController::class, 'index']);
            Route::post('/premi/restore/{id}', [PremiController::class, 'restore']);
            Route::get('/premi/export', [PremiController::class, 'exportPremi']);
            Route::post('/premi/import', [PremiController::class, 'importPremi']);
            Route::apiResource('/premi', PremiController::class);

            // ! TER21 ===========>
            Route::get('/all-ter-pph-21', [TER21Controller::class, 'getAllTer']);
            Route::post('/ter-pph-21/filter', [TER21Controller::class, 'index']);
            Route::post('/ter-pph-21/search', [TER21Controller::class, 'index']);
            Route::post('/ter-pph-21/restore/{id}', [TER21Controller::class, 'restore']);
            Route::get('/ter-pph-21/export', [TER21Controller::class, 'exportTER']);
            Route::post('/ter-pph-21/import', [TER21Controller::class, 'importTER']);
            Route::apiResource('/ter-pph-21', TER21Controller::class);

            // ! Jadwal Penggajian ===========>
            Route::get('/get-jadwal-penggajian/{id}', [JadwalPenggajianController::class, 'getJadwalPenggajian']);
            Route::post('/jadwal-penggajian', [JadwalPenggajianController::class, 'createJadwalPenggajian']);

            // ! THR ===========>
            // Route::get('/all-tunjangan-hari-raya', [THRController::class, 'getAllTHRSetting']);
            // Route::apiResource('/tunjangan-hari-raya', THRController::class);
            /* ==================================== Setting Finance ==================================== */


            /* ==================================== Setting Managemen Waktu ==================================== */
            // ! Shift ===========>
            Route::get('/all-shift', [ShiftController::class, 'getAllShift']);
            Route::post('/shift/filter', [ShiftController::class, 'index']);
            Route::post('/shift/search', [ShiftController::class, 'index']);
            Route::post('/shift/restore/{id}', [ShiftController::class, 'restore']);
            Route::get('/shift/export', [ShiftController::class, 'exportShift']);
            Route::post('/shift/import', [ShiftController::class, 'importShift']);
            Route::apiResource('/shift', ShiftController::class);

            // ! Hari Libur ===========>
            Route::get('/hari-libur/nasional', [HariLiburController::class, 'getNasionalHariLibur']);
            Route::post('/hari-libur/filter', [HariLiburController::class, 'index']);
            Route::post('/hari-libur/search', [HariLiburController::class, 'index']);
            Route::post('/hari-libur/restore/{id}', [HariLiburController::class, 'restore']);
            Route::post('/hari-libur/bulk-delete', [HariLiburController::class, 'bulkDelete']);
            Route::get('/hari-libur/export', [HariLiburController::class, 'exportHariLibur']);
            Route::post('/hari-libur/import', [HariLiburController::class, 'importHariLibur']);
            Route::apiResource('/hari-libur', HariLiburController::class);

            // ! Cuti ===========>
            Route::get('/all-cuti', [CutiController::class, 'getAllTipeCuti']);
            Route::post('/cuti/filter', [CutiController::class, 'index']);
            Route::post('/cuti/search', [CutiController::class, 'index']);
            Route::post('/cuti/restore/{id}', [CutiController::class, 'restore']);
            Route::get('/cuti/export', [CutiController::class, 'exportCuti']);
            Route::post('/cuti/import', [CutiController::class, 'importCuti']);
            Route::apiResource('/cuti', CutiController::class);

            // ! Lokasi Presensi ===========>
            Route::get('/get-lokasi-kantor/{id}', [LokasiKantorController::class, 'getLokasiKantor']);
            Route::post('/lokasi-kantor', [LokasiKantorController::class, 'editLokasiKantor']);
            /* ==================================== Setting Managemen Waktu ==================================== */
        });
    });
});
