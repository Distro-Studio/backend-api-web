<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\InboxController;
use App\Http\Controllers\Dashboard\Jadwal\DataCutiController;
use App\Http\Controllers\Dashboard\Jadwal\DataJadwalController;
use App\Http\Controllers\Dashboard\Jadwal\DataLemburController;
use App\Http\Controllers\Dashboard\Jadwal\DataTukarJadwalController;
use App\Http\Controllers\Dashboard\Karyawan\DataKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\DataRiwayatPerubahanController;
use App\Http\Controllers\Dashboard\Karyawan\DataTransferKaryawanController;
use App\Http\Controllers\Dashboard\Keuangan\PenggajianController;
use App\Http\Controllers\Dashboard\Keuangan\PenyesuaianGajiController;
use App\Http\Controllers\Dashboard\Keuangan\THRPenggajianController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\PermissionsController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\RolesController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\UserPasswordController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\JadwalPenggajianController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\KategoriTER21Controller;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\PremiController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\PTKPController;
use App\Http\Controllers\Dashboard\Pengaturan\Finance\TER21Controller;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\JabatanController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\KelompokGajiController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\KompetensiController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\PertanyaanController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\UnitKerjaController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\CutiController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\HariLiburController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\LokasiKantorController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\NonShiftController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\ShiftController;
use App\Http\Controllers\Dashboard\PengumumanController;
use App\Http\Controllers\Dashboard\Perusahaan\DiklatController;
use App\Http\Controllers\Dashboard\Perusahaan\JenisPenilaianController;
use App\Http\Controllers\Dashboard\Perusahaan\PelaporanController;
use App\Http\Controllers\Dashboard\Perusahaan\PenilaianController;
use App\Http\Controllers\Dashboard\Presensi\DataPresensiController;
use App\Http\Controllers\Publik\Auth\LoginController;
use App\Models\JenisPenilaian;
use App\Models\NonShift;
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
    Route::get('/get-list-user-shift', [DataKaryawanController::class, 'getAllDataUserShift']);
    Route::get('/get-list-user-non-shift', [DataKaryawanController::class, 'getAllDataUserNonShift']);
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
    Route::get('/get-list-kategori-ter', [TER21Controller::class, 'getAllKategoriTER']);
    Route::get('/get-list-tipecuti', [CutiController::class, 'getAllTipeCuti']);
    Route::get('/get-list-harilibur', [HariLiburController::class, 'getAllHariLibur']);
    Route::get('/get-list-shift', [ShiftController::class, 'getAllShift']);
    Route::get('/get-list-non-shift', [NonShiftController::class, 'getAllNonShift']);
    Route::get('/get-list-tipe-cuti', [CutiController::class, 'getAllTipeCuti']);
    Route::get('/get-list-pertanyaan', [PertanyaanController::class, 'getAllPertanyaan']);
    Route::get('/get-list-jenis-penilaian', [JenisPenilaianController::class, 'getAllPenilaian']);

    Route::group(['prefix' => 'rski/dashboard'], function () {
        Route::get('/logout', [LoginController::class, 'logout'])->middleware('web');
        Route::get('/user-info', [LoginController::class, 'getInfoUserLogin']);

        Route::get('/calculated-header', [DashboardController::class, 'calculatedHeader']);
        Route::get('/calculated-jenis-kelamin', [DashboardController::class, 'calculatedKelamin']);
        Route::get('/calculated-jabatan', [DashboardController::class, 'calculatedJabatan']);
        Route::get('/calculated-kepegawaian', [DashboardController::class, 'calculatedKepegawaian']);
        Route::get('/get-lembur-today', [DashboardController::class, 'getLemburToday']);
        Route::apiResource('/pengumuman', PengumumanController::class);
        Route::get('/get-unread-notifikasi', [InboxController::class, 'calculatedUnread']);
        Route::get('/notifikasi', [InboxController::class, 'index']);
        Route::get('/notifikasi/{id}', [InboxController::class, 'show']);
        Route::delete('/notifikasi/delete-read-notifikasi', [InboxController::class, 'destroyRead']);
        Route::get('/download-template-jadwal', [DataJadwalController::class, 'downloadJadwalTemplate']);
        Route::get('/download-template-karyawan', [DataKaryawanController::class, 'downloadKaryawanTemplate']);
        Route::get('/download-template-presensi', [DataPresensiController::class, 'downloadPresensiTemplate']);

        // TODO: aktifkan send email di create & transfer karyawan
        // TODO: ganti email di create & transfer karyawan
        // TODO: ganti send email dengan email create akun pada validasi jika email berubah
        Route::group(['prefix' => '/karyawan'], function () {
            // ! Data Karyawan ===========>
            Route::post('/get-data-karyawan', [DataKaryawanController::class, 'index']);
            Route::post('/export', [DataKaryawanController::class, 'exportKaryawan']);
            Route::post('/import', [DataKaryawanController::class, 'importKaryawan']);
            Route::post('/{data_karyawan_id}/status-karyawan', [DataKaryawanController::class, 'toggleStatusUser']);
            Route::get('/detail-karyawan-user/{user_id}', [DataKaryawanController::class, 'showByUserId']);
            Route::get('/detail-karyawan/{data_karyawan_id}', [DataKaryawanController::class, 'showByDataKaryawanId']);

            Route::get('/detail-karyawan-presensi/{data_karyawan_id}', [DataKaryawanController::class, 'getDataPresensi']);
            Route::get('/detail-karyawan-jadwal/{data_karyawan_id}', [DataKaryawanController::class, 'getDataJadwal']);
            Route::get('/detail-karyawan-rekam-jejak/{data_karyawan_id}', [DataKaryawanController::class, 'getDataRekamJejak']);
            Route::get('/detail-karyawan-keluarga/{data_karyawan_id}', [DataKaryawanController::class, 'getDataKeluarga']);

            Route::get('/detail-karyawan-dokumen/{data_karyawan_id}', [DataKaryawanController::class, 'getDataDokumen']);
            Route::post('/detail-karyawan-dokumen/{berkasId}/verifikasi', [DataKaryawanController::class, 'verifikasiBerkas']);

            Route::get('/detail-karyawan-cuti/{data_karyawan_id}', [DataKaryawanController::class, 'getDataCuti']);
            Route::get('/detail-karyawan-tukar-jadwal/{data_karyawan_id}', [DataKaryawanController::class, 'getDataTukarJadwal']);
            Route::get('/detail-karyawan-lembur/{data_karyawan_id}', [DataKaryawanController::class, 'getDataLembur']);
            Route::get('/detail-karyawan-feedback-penilaian/{data_karyawan_id}', [DataKaryawanController::class, 'getDataFeedbackPenilaian']);

            Route::apiResource('/data-karyawan', DataKaryawanController::class);

            // ! Transfer Karyawan ===========>
            Route::post('/transfer/get-data-trasnfer', [DataTransferKaryawanController::class, 'index']);
            Route::get('/transfer/export', [DataTransferKaryawanController::class, 'exportTransferKaryawan']);
            Route::apiResource('/transfer', DataTransferKaryawanController::class);

            // ! Riwayat Perubahan Karyawan ===========>
            Route::post('/riwayat-perubahan/get-riwayat-perubahan-karyawan', [DataRiwayatPerubahanController::class, 'index']);
            Route::post('/riwayat-perubahan/verifikasi-data/{id}', [DataRiwayatPerubahanController::class, 'verifikasiPerubahan']);
        });

        Route::group(['prefix' => '/presensi'], function () {
            // ! Presensi Tabel ===========>
            Route::post('/get-data-presensi', [DataPresensiController::class, 'index']);
            Route::post('/export', [DataPresensiController::class, 'exportPresensi']);
            Route::post('/import', [DataPresensiController::class, 'importPresensi']);
            Route::get('/data-presensi/{id}', [DataPresensiController::class, 'detail_list']);
            Route::get('/data-presensi/{id}/detail', [DataPresensiController::class, 'detail_data_v2']);
            Route::apiResource('/data-presensi', DataPresensiController::class);

            Route::get('/calculated', [DataPresensiController::class, 'calculatedPresensi']);
        });

        Route::group(['prefix' => '/jadwal-karyawan'], function () {
            // ! Jadwal ===========>
            Route::post('/get-data-jadwal', [DataJadwalController::class, 'index']);
            Route::post('/create-shift/{userId}', [DataJadwalController::class, 'createShiftByDate']);
            Route::get('/export-shift', [DataJadwalController::class, 'exportJadwalKaryawanShift']);
            Route::get('/export-non-shift', [DataJadwalController::class, 'exportJadwalKaryawanNonShift']);
            Route::post('/import', [DataJadwalController::class, 'importJadwalKaryawan']);
            Route::apiResource('/data-jadwal', DataJadwalController::class);

            // ! Tukar Jadwal ===========>
            Route::post('/get-tukar-jadwal', [DataTukarJadwalController::class, 'index']);
            Route::get('/get-tukar-jadwal/jadwal-pengajuan/{userId}', [DataTukarJadwalController::class, 'getJadwalPengajuan']);
            Route::get('/get-tukar-jadwal/user-ditukar/{jadwalId}', [DataTukarJadwalController::class, 'getUserDitukar']);
            Route::get('/get-tukar-jadwal/jadwal-ditukar/{userId}', [DataTukarJadwalController::class, 'getJadwalDitukar']);

            Route::get('/tukar-jadwal/export', [DataTukarJadwalController::class, 'exportJadwalTukar']);
            Route::apiResource('/tukar-jadwal', DataTukarJadwalController::class);

            // ! Lembur ===========>
            Route::post('/get-lembur', [DataLemburController::class, 'index']);
            Route::get('/get-jadwal-user-lembur/{userId}', [DataLemburController::class, 'getJadwalPengajuanLembur']);
            Route::get('/lembur/export', [DataLemburController::class, 'exportJadwalLembur']);
            Route::apiResource('/lembur', DataLemburController::class);

            // ! Cuti ===========>
            Route::post('/cuti/{cutiId}/verifikasi-tahap-1', [DataCutiController::class, 'verifikasiTahap1']);
            Route::post('/cuti/{cutiId}/verifikasi-tahap-2', [DataCutiController::class, 'verifikasiTahap2']);
            Route::post('/get-cuti', [DataCutiController::class, 'index']);
            Route::get('/cuti/export', [DataCutiController::class, 'exportJadwalCuti']);
            Route::apiResource('/cuti', DataCutiController::class);

            // ! Izin ===========>
            Route::post('/izin/{izinId}/verifikasi-perizinan', [DataCutiController::class, 'verifikasiRiwayatIzin']);
        });

        Route::group(['prefix' => '/keuangan'], function () {
            // ! Penggajian ===========>
            Route::get('/calculated-info-penggajian', [PenggajianController::class, 'calculatedInfo']);
            Route::post('/get-penggajian', [PenggajianController::class, 'index']);

            Route::post('/penggajian/export-penerimaan', [PenggajianController::class, 'exportRekapPenerimaanGaji']);
            Route::post('/penggajian/export-potongan', [PenggajianController::class, 'exportRekapPotonganGaji']);
            Route::post('/penggajian/export-bank', [PenggajianController::class, 'exportLaporanGajiBank']);
            Route::get('/penggajian/detail/{penggajian_id}', [PenggajianController::class, 'showDetailGajiUser']);

            Route::post('/penggajian/detail/{penggajian_id}/create-penambah-gaji', [PenyesuaianGajiController::class, 'storePenyesuaianGajiPenambah']);
            Route::post('/penggajian/detail/{penggajian_id}/create-pengurang-gaji', [PenyesuaianGajiController::class, 'storePenyesuaianGajiPengurang']);

            Route::apiResource('/penggajian', PenggajianController::class);
            Route::post('/publikasi-penggajian', [PenggajianController::class, 'publikasiPenggajian']);

            // ! Penyesuaian Gaji ===========>
            Route::post('/get-penyesuaian-gaji', [PenyesuaianGajiController::class, 'index']);
            Route::get('/penyesuaian-gaji/export', [PenyesuaianGajiController::class, 'exportPenyesuaianGaji']);
            Route::apiResource('/penyesuaian-gaji', PenyesuaianGajiController::class);

            // ! THR Penggajian ===========>
            Route::post('/get-thr', [THRPenggajianController::class, 'index']);
            Route::get('/run-thr/export', [THRPenggajianController::class, 'exportTHRPenggajian']);
            Route::apiResource('/run-thr', THRPenggajianController::class);
        });

        Route::group(['prefix' => '/perusahaan'], function () {
            // ! Diklat ===========>
            Route::post('/get-data-diklat', [DiklatController::class, 'index']);
            Route::post('/diklat', [DiklatController::class, 'store']);
            Route::get('/diklat/{diklatId}', [DiklatController::class, 'show']);
            Route::get('/diklat/export', [DiklatController::class, 'exportDiklat']);
            Route::post('/diklat/{diklatId}/verifikasi-step-1', [DiklatController::class, 'verifikasiTahap1']);
            Route::post('/diklat/{diklatId}/verifikasi-step-2', [DiklatController::class, 'verifikasiTahap2']);
            Route::post('/diklat/{diklatId}/verifikasi-diklat-eksternal', [DiklatController::class, 'verifikasiDiklatExternal']);

            // ! Pelaporan ===========>
            // Route::post('/get-data-pelaporan', [PelaporanController::class, 'index']);
            // Route::get('/pelaporan/export', [PelaporanController::class, 'exportPelaporan']);

            // ! Jenis Penilaian ===========>
            Route::post('/jenis-penilaian/restore/{jenis_penilaian}', [JenisPenilaianController::class, 'restore']);
            Route::get('/jenis-penilaian/export', [JenisPenilaianController::class, 'exportJenisPenilaian']);
            Route::apiResource('/jenis-penilaian', JenisPenilaianController::class);

            // ! Penilaian ===========>
            Route::get('/get-user-dinilai', [PenilaianController::class, 'getUserDinilai']);
            Route::get('/get-user-penilai', [PenilaianController::class, 'getUserPenilai']);
            Route::post('/get-user-belum-dinilai', [PenilaianController::class, 'getKaryawanBelumDinilai']);
            Route::post('/get-data-penilaian', [PenilaianController::class, 'index']);
            Route::get('/penilaian/export', [PenilaianController::class, 'exportPenilaian']);
            Route::apiResource('/penilaian', PenilaianController::class);
        });

        Route::group(['prefix' => '/pengaturan'], function () {
            // ! Roles ===========>
            Route::post('/role/restore/{id}', [RolesController::class, 'restore']);
            Route::apiResource('/role', RolesController::class);

            // ! Roles Permission ===========>
            Route::get('/get-permissions', [PermissionsController::class, 'getAllPermissions']);
            Route::post('/permissions/{role}', [PermissionsController::class, 'updatePermissions']);
            Route::post('/permissions/remove/{role}', [PermissionsController::class, 'removeAllPermissions']);

            // ! Change Password ===========>
            Route::post('/users/change-passwords', [UserPasswordController::class, 'updatePassword']);

            // ! Jabatan ===========>
            Route::post('/jabatan/restore/{id}', [JabatanController::class, 'restore']);
            Route::apiResource('/jabatan', JabatanController::class);

            // ! Kelompok Gaji ===========>
            Route::post('/kelompok-gaji/restore/{id}', [KelompokGajiController::class, 'restore']);
            Route::apiResource('/kelompok-gaji', KelompokGajiController::class);

            // ! Kompetensi ===========>
            Route::post('/kompetensi/restore/{id}', [KompetensiController::class, 'restore']);
            Route::apiResource('/kompetensi', KompetensiController::class);

            // ! Unit Kerja ===========>
            Route::post('/unit-kerja/restore/{id}', [UnitKerjaController::class, 'restore']);
            Route::apiResource('/unit-kerja', UnitKerjaController::class);

            // ! Pertanyaan ===========>
            Route::post('/pertanyaan/restore/{id}', [PertanyaanController::class, 'restore']);
            Route::apiResource('/pertanyaan', PertanyaanController::class);

            // ! Premi ===========>
            Route::post('/premi/restore/{id}', [PremiController::class, 'restore']);
            Route::apiResource('/premi', PremiController::class);

            // ! TER21 ===========>
            Route::post('/pph-21/restore/{id}', [TER21Controller::class, 'restore']);
            Route::apiResource('/pph-21', TER21Controller::class);

            // ! Kategori TER21 ===========>
            Route::post('/kategori-ter/restore/{id}', [KategoriTER21Controller::class, 'restore']);
            Route::apiResource('/kategori-ter', KategoriTER21Controller::class);

            // ! PTKP ===========>
            Route::post('/ptkp/restore/{id}', [PTKPController::class, 'restore']);
            Route::apiResource('/ptkp', PTKPController::class);

            // ! Jadwal Penggajian ===========>
            Route::get('/get-jadwal-penggajian/{id}', [JadwalPenggajianController::class, 'getJadwalPenggajian']);
            Route::post('/jadwal-penggajian', [JadwalPenggajianController::class, 'createJadwalPenggajian']);

            // ! THR ===========>
            // Route::get('/all-tunjangan-hari-raya', [THRController::class, 'getAllTHRSetting']);
            // Route::apiResource('/tunjangan-hari-raya', THRController::class);

            // ! Shift ===========>
            Route::post('/shift/restore/{id}', [ShiftController::class, 'restore']);
            Route::apiResource('/shift', ShiftController::class);

            // ! Non-Shift ===========>
            // Route::post('/non-shift/restore/{id}', [NonShiftController::class, 'restore']);
            // Route::apiResource('/non-shift', NonShift::class);
            Route::post('/non-shift', [NonShiftController::class, 'edit']);
            Route::get('/non-shift/{non_shift}', [NonShiftController::class, 'show']);

            // ! Hari Libur ===========>
            Route::get('/hari-libur/nasional', [HariLiburController::class, 'getNasionalHariLibur']);
            Route::post('/hari-libur/restore/{id}', [HariLiburController::class, 'restore']);
            Route::apiResource('/hari-libur', HariLiburController::class);

            // ! Cuti ===========>
            Route::post('/cuti/restore/{id}', [CutiController::class, 'restore']);
            Route::apiResource('/cuti', CutiController::class);

            // ! Lokasi Presensi ===========>
            Route::get('/get-lokasi-kantor/{id}', [LokasiKantorController::class, 'getLokasiKantor']);
            Route::post('/lokasi-kantor', [LokasiKantorController::class, 'editLokasiKantor']);
        });
    });
});
