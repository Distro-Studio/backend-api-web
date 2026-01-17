<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\InboxController;
use App\Http\Controllers\Dashboard\Jadwal\DataCutiBesarTahunanController;
use App\Http\Controllers\Dashboard\Jadwal\DataCutiController;
use App\Http\Controllers\Dashboard\Jadwal\DataHakCutiController;
use App\Http\Controllers\Dashboard\Jadwal\DataJadwalController;
use App\Http\Controllers\Dashboard\Jadwal\DataLemburController;
use App\Http\Controllers\Dashboard\Jadwal\DataRiwayatPerizinanController;
use App\Http\Controllers\Dashboard\Jadwal\DataTukarJadwalController;
use App\Http\Controllers\Dashboard\Karyawan\DataKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\DataKaryawanMedisController;
use App\Http\Controllers\Dashboard\Karyawan\DataRiwayatPerubahanController;
use App\Http\Controllers\Dashboard\Karyawan\DataTransferKaryawanController;
use App\Http\Controllers\Dashboard\Karyawan\DetailKaryawan\Berkas\Karyawan_BerkasController;
use App\Http\Controllers\Dashboard\Karyawan\DetailKaryawan\Karyawan_DetailController;
use App\Http\Controllers\Dashboard\Karyawan\DetailKaryawan\Keluarga\Karyawan_KeluargaController;
use App\Http\Controllers\Dashboard\Karyawan\PembatalanRewardController;
use App\Http\Controllers\Dashboard\Karyawan\TambahanDataController;
use App\Http\Controllers\Dashboard\Keuangan\PenggajianController;
use App\Http\Controllers\Dashboard\Keuangan\PenggajianKemenkeuController;
use App\Http\Controllers\Dashboard\Keuangan\PenyesuaianGajiController;
use App\Http\Controllers\Dashboard\Keuangan\TagihanPotonganController;
use App\Http\Controllers\Dashboard\Keuangan\THRPenggajianController;
use App\Http\Controllers\Dashboard\Pengaturan\Akun\MasterVerificationController;
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
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\MateriPelatihanController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\PendidikanTerakhirController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\PertanyaanController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\SpesialisasiController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\StatusKaryawanController;
use App\Http\Controllers\Dashboard\Pengaturan\Karyawan\UnitKerjaController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\CutiController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\HariLiburController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\LokasiKantorController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\NonShiftController;
use App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu\ShiftController;
use App\Http\Controllers\Dashboard\PengumumanController;
use App\Http\Controllers\Dashboard\Perusahaan\AboutHospitalController;
use App\Http\Controllers\Dashboard\Perusahaan\DiklatController;
use App\Http\Controllers\Dashboard\Perusahaan\JenisPenilaianController;
use App\Http\Controllers\Dashboard\Perusahaan\MasaDiklatController;
use App\Http\Controllers\Dashboard\Perusahaan\PenilaianController;
use App\Http\Controllers\Dashboard\Presensi\AnulirPresensiController;
use App\Http\Controllers\Dashboard\Presensi\DataPresensiController;
use App\Http\Controllers\Publik\Auth\ForgotPasswordController;
use App\Http\Controllers\Publik\Auth\LoginController;
use App\Http\Controllers\Publik\Auth\ResetPasswordController;
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

// Untuk case nambah data
// Route::post('/add-data-karyawan', [TambahanDataController::class, 'insertDataKaryawan']);
// Route::post('/add-str-sip', [TambahanDataController::class, 'insertSTRSIP']);
// Route::post('/add-data-keluarga', [TambahanDataController::class, 'insertDataKeluarga']);
// Route::post('/cek-data-pendidikan', [TambahanDataController::class, 'cekDataPendidikanFromDataKeluarga']);
// Route::post('/cek-nik-karyawan', [TambahanDataController::class, 'cekNIK']);
// Route::post('/cek-agama-karyawan', [TambahanDataController::class, 'cekAgama']);
// Route::post('/cek-darah-karyawan', [TambahanDataController::class, 'cekGolonganDarah']);
// Route::post('/cek-hubungan-keluarga', [TambahanDataController::class, 'cekHubunganKeluarga']);
// Route::post('/test-unit', [TambahanDataController::class, 'cekUnitKerja']);
// Route::post('/add-data-shifts', [TambahanDataController::class, 'insertMasterShift']);
// Route::post('/add-data-master-spesialisasi', [TambahanDataController::class, 'insertMasterSpesialisasi']);
// Route::post('/add-data-spesialisasi', [TambahanDataController::class, 'insertSpesialiasaiKaryawan']);

Route::post('/login', [LoginController::class, 'login']);
Route::post('/forgot-password-sendOtp', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/forgot-password-verifyOtp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);

Route::middleware(['auth:sanctum'])->group(function () {
    // ! Global Request ===========>
    Route::get('/get-list-user', [DataKaryawanController::class, 'getAllDataUser']);
    Route::get('/get-list-user-shift', [DataKaryawanController::class, 'getAllDataUserShift']);
    Route::get('/get-list-user-non-shift', [DataKaryawanController::class, 'getAllDataUserNonShift']);
    Route::get('/get-list-unit-kerja', [DataKaryawanController::class, 'getAllDataUnitKerja']);
    Route::get('/get-list-spesialisasi', [DataKaryawanController::class, 'getAllDataSpesialisasi']);
    Route::get('/get-list-kategori-unit-kerja', [DataKaryawanController::class, 'getAllDataKategoriUnitKerja']);
    Route::get('/get-list-jabatan', [DataKaryawanController::class, 'getAllDataJabatan']);
    Route::get('/get-list-kategori-status-karyawan', [DataKaryawanController::class, 'getAllDataKategoriStatusKaryawan']);
    Route::get('/get-list-status-karyawan', [DataKaryawanController::class, 'getAllDataStatusKaryawan']);
    Route::get('/get-list-kompetensi', [DataKaryawanController::class, 'getAllDataKompetensi']);
    Route::get('/get-list-role', [DataKaryawanController::class, 'getAllDataRole']);
    Route::get('/get-list-kelompok-gaji', [DataKaryawanController::class, 'getAllDataKelompokGaji']);
    Route::get('/get-list-ptkp', [DataKaryawanController::class, 'getAllDataPTKP']);
    Route::get('/get-list-kategori-transfer', [DataTransferKaryawanController::class, 'getAllKategoriTransfer']);
    Route::get('/get-list-karyawan-verifikasi/{karyawan_diverifikasi}', [MasterVerificationController::class, 'getAllKaryawanDiverifikasi']);
    Route::post('/get-list-karyawan-verifikator', [MasterVerificationController::class, 'getAllKaryawanVerifikator']);
    Route::get('/get-lokasi-kantor', [DataPresensiController::class, 'getLokasiKantor']);
    Route::get('/get-list-premi', [DataKaryawanController::class, 'getAllDataPremi']);
    Route::get('/get-list-pph21', [TER21Controller::class, 'getAllTer']);
    Route::get('/get-list-kategori-ter', [TER21Controller::class, 'getAllKategoriTER']);
    Route::get('/get-list-tipecuti', [CutiController::class, 'getAllTipeCuti']);
    Route::get('/get-list-harilibur', [HariLiburController::class, 'getAllHariLibur']);
    Route::get('/get-list-shift', [ShiftController::class, 'getAllShift']);
    Route::get('/get-list-shift/{data_karyawan_id}', [ShiftController::class, 'getAllShiftUnitKerja']);
    Route::get('/get-list-non-shift', [NonShiftController::class, 'getAllNonShift']);
    Route::get('/get-list-tipe-cuti', [CutiController::class, 'getAllTipeCuti']);
    Route::get('/get-list-pertanyaan', [PertanyaanController::class, 'getAllPertanyaan']);
    Route::get('/get-list-jenis-penilaian', [JenisPenilaianController::class, 'getAllPenilaian']);
    Route::get('/get-list-pendidikan', [DataKaryawanController::class, 'getAllPendidikan']);
    Route::get('/get-list-kategori-tagihan-potongan', [DataKaryawanController::class, 'getAllDataTagihanPotongan']);

    Route::group(['prefix' => 'rski/dashboard'], function () {
        Route::get('/logout', [LoginController::class, 'logout'])->middleware('web');
        Route::get('/user-info', [LoginController::class, 'getInfoUserLogin']);

        Route::get('/calculated-header', [DashboardController::class, 'calculatedHeader']);
        Route::get('/calculated-jenis-kelamin', [DashboardController::class, 'calculatedKelamin']);
        Route::get('/calculated-jabatan', [DashboardController::class, 'calculatedJabatan']);
        Route::get('/calculated-profesi', [DashboardController::class, 'calculatedKompetensi']);
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
        Route::get('/download-template-tagihan-potongan', [TagihanPotonganController::class, 'downloadTagihanPotonganTemplate']);

        Route::group(['prefix' => '/karyawan'], function () {
            // ! Data Karyawan ===========>
            Route::post('/get-data-karyawan', [DataKaryawanController::class, 'index']);
            Route::post('/export', [DataKaryawanController::class, 'exportKaryawan']);
            Route::post('/import', [DataKaryawanController::class, 'importKaryawan']);
            Route::post('/reset-credentials', [DataKaryawanController::class, 'resetCredentials']);
            Route::post('/{data_karyawan_id}/status-karyawan', [DataKaryawanController::class, 'toggleStatusUser']);
            Route::get('/detail-karyawan-user/{user_id}', [DataKaryawanController::class, 'showByUserId']);
            Route::get('/detail-karyawan/{data_karyawan_id}', [DataKaryawanController::class, 'showByDataKaryawanId']);
            Route::post('/upload-photo-profile/{data_karyawan_id}', [DataKaryawanController::class, 'uploadPhotoProfile']);
            Route::post('/update-reward-presensi/{data_karyawan_id}', [DataKaryawanController::class, 'updateRewardPresensi']);

            Route::post('/detail-karyawan-presensi/{data_karyawan_id}', [Karyawan_DetailController::class, 'getDataPresensi']);
            Route::post('/detail-karyawan-presensi/{data_karyawan_id}/export', [Karyawan_DetailController::class, 'exportDataPresensi']);
            Route::get('/detail-karyawan-jadwal/{data_karyawan_id}', [Karyawan_DetailController::class, 'getDataJadwal']);
            Route::get('/detail-karyawan-rekam-jejak/{data_karyawan_id}', [Karyawan_DetailController::class, 'getDataRekamJejak']);

            // ! Data Karyawan Medis Section ===========>
            Route::post('/get-karyawan-medis', [DataKaryawanMedisController::class, 'index']);
            Route::post('/export-karyawan-medis', [DataKaryawanMedisController::class, 'exportKaryawanMedis']);

            // ! Data Keluarga Section ===========>
            Route::get('/detail-karyawan-keluarga/{data_karyawan_id}', [Karyawan_KeluargaController::class, 'getDataKeluarga']);
            Route::post('/detail-karyawan-keluarga/{data_karyawan_id}/create-keluarga', [Karyawan_KeluargaController::class, 'storeDataKeluarga']);
            Route::post('/detail-karyawan-keluarga/{data_karyawan_id}/update-keluarga/{keluarga_id}', [Karyawan_KeluargaController::class, 'updateDataKeluarga']);
            Route::post('/detail-karyawan-keluarga/{data_karyawan_id}/verifikasi', [Karyawan_KeluargaController::class, 'verifikasiKeluarga']);

            // ! Data Berkas Section ===========>
            Route::get('/detail-karyawan-dokumen/{data_karyawan_id}', [Karyawan_BerkasController::class, 'getDataDokumen']);
            Route::post('/detail-karyawan-dokumen/{data_karyawan_id}/verifikasi', [Karyawan_BerkasController::class, 'verifikasiBerkas']);
            Route::post('/detail-karyawan-dokumen/{data_karyawan_id}/create-berkas', [Karyawan_BerkasController::class, 'createPersonalFile']);
            Route::post('/detail-karyawan-dokumen/{data_karyawan_id}/delete-berkas', [Karyawan_BerkasController::class, 'deletePersonalFile']);

            Route::get('/detail-karyawan-cuti/{data_karyawan_id}', [Karyawan_DetailController::class, 'getDataCuti']);
            Route::get('/detail-karyawan-tukar-jadwal/{data_karyawan_id}', [Karyawan_DetailController::class, 'getDataTukarJadwal']);
            Route::get('/detail-karyawan-lembur/{data_karyawan_id}', [Karyawan_DetailController::class, 'getDataLembur']);
            Route::get('/detail-karyawan-feedback-penilaian/{data_karyawan_id}', [Karyawan_DetailController::class, 'getDataFeedbackPenilaian']);

            Route::get('/detail-karyawan-diklat/{data_karyawan_id}', [Karyawan_DetailController::class, 'getDataDiklat']);

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
            // ! Presensi ===========>
            Route::post('/get-data-presensi', [DataPresensiController::class, 'index']);
            Route::post('/export', [DataPresensiController::class, 'exportPresensi']);
            Route::post('/import', [DataPresensiController::class, 'importPresensi']);
            Route::apiResource('/data-presensi', DataPresensiController::class);
            Route::get('/calculated', [DataPresensiController::class, 'calculatedPresensi']);

            // ! Anulir Presensi ===========>
            Route::post('/get-data-anulir-presensi', [AnulirPresensiController::class, 'index']);
            Route::apiResource('/anulir-presensi', AnulirPresensiController::class);

            // ! Riwayat Pembatalan Reward ===========>
            Route::post('/get-data-history-reward', [PembatalanRewardController::class, 'index']);
            Route::post('/history-reward/export', [PembatalanRewardController::class, 'exportPembatalanReward']);
        });

        Route::group(['prefix' => '/jadwal-karyawan'], function () {
            // ! Jadwal ===========>
            Route::post('/get-data-jadwal', [DataJadwalController::class, 'index']);
            Route::post('/create-shift/{userId}', [DataJadwalController::class, 'createShiftByDate']);
            Route::post('/export-shift', [DataJadwalController::class, 'exportJadwalKaryawanShift']);
            Route::post('/export-non-shift', [DataJadwalController::class, 'exportJadwalKaryawanNonShift']);
            Route::post('/import', [DataJadwalController::class, 'importJadwalKaryawan']);
            Route::apiResource('/data-jadwal', DataJadwalController::class);

            // ! Tukar Jadwal ===========>
            Route::post('/get-tukar-jadwal', [DataTukarJadwalController::class, 'index']);
            Route::get('/get-tukar-jadwal/jadwal-pengajuan/{userId}', [DataTukarJadwalController::class, 'getJadwalPengajuan']);
            Route::get('/get-tukar-jadwal/user-ditukar/{jadwalId}', [DataTukarJadwalController::class, 'getUserDitukar']);
            Route::get('/get-tukar-jadwal/jadwal-ditukar/{userId}', [DataTukarJadwalController::class, 'getJadwalDitukar']);

            Route::post('/tukar-jadwal/{tukarJadwalId}/verifikasi-step-1', [DataTukarJadwalController::class, 'verifikasiTahap1']);
            Route::post('/tukar-jadwal/{tukarJadwalId}/verifikasi-step-2', [DataTukarJadwalController::class, 'verifikasiTahap2']);
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
            Route::post('/delete-cuti', [DataCutiController::class, 'deleteCuti']);
            Route::post('/cuti/export', [DataCutiController::class, 'exportJadwalCuti']);
            Route::post('/cuti-besar-tahunan/export', [DataCutiBesarTahunanController::class, 'exportCutiBesarTahunan']);
            Route::apiResource('/cuti', DataCutiController::class);
            Route::apiResource('/cuti-besar-tahunan', DataCutiBesarTahunanController::class);

            // ! Izin ===========>
            Route::post('/get-perizinan', [DataRiwayatPerizinanController::class, 'index']);
            Route::post('/izin/{izinId}/verifikasi-perizinan', [DataRiwayatPerizinanController::class, 'verifikasiRiwayatIzin']);
            Route::get('/izin/{id}', [DataRiwayatPerizinanController::class, 'show']);
        });

        Route::group(['prefix' => '/keuangan'], function () {
            // ! Penggajian ===========>
            Route::get('/calculated-info-penggajian', [PenggajianController::class, 'calculatedInfo']);
            Route::post('/get-penggajian', [PenggajianController::class, 'index']);

            Route::post('/penggajian/export-penerimaan-karyawan', [PenggajianController::class, 'exportRekapPenerimaanGaji']);
            Route::post('/penggajian/export-penerimaan-unit', [PenggajianController::class, 'exportRekapPenerimaanGajiUnit']);
            Route::post('/penggajian/export-penerimaan-kompetensi', [PenggajianController::class, 'exportRekapPenerimaanGajiKompetensi']);
            Route::post('/penggajian/export-potongan', [PenggajianController::class, 'exportRekapPotonganGaji']);
            Route::post('/penggajian/export-bank', [PenggajianController::class, 'exportLaporanGajiBank']);
            Route::post('/penggajian/export-kemenkeu', [PenggajianKemenkeuController::class, 'rekapPDFKemenkeu']);
            Route::post('/penggajian/export-kemenkeu-debug', [PenggajianKemenkeuController::class, 'exportSingleKaryawanPDF']);
            Route::get('/penggajian/detail/{penggajian_id}', [PenggajianController::class, 'showDetailGajiUser']);

            // Penyesuaian gaji single
            Route::post('/penggajian/detail/{penggajian_id}/create-penambah-gaji', [PenyesuaianGajiController::class, 'storePenyesuaianGajiPenambah']);
            Route::post('/penggajian/detail/{penggajian_id}/create-pengurang-gaji', [PenyesuaianGajiController::class, 'storePenyesuaianGajiPengurang']);

            // ? Penyesuaian gaji multi user_id
            // Route::post('/penggajian/penyesuaian-penambah-gaji', [PenyesuaianGajiController::class, 'storePenyesuaianGajiPenambah']);
            // Route::post('/penggajian/penyesuaian-pengurang-gaji', [PenyesuaianGajiController::class, 'storePenyesuaianGajiPengurang']);

            Route::apiResource('/penggajian', PenggajianController::class);
            Route::post('/publikasi-penggajian', [PenggajianController::class, 'publikasiPenggajian']);

            // ! Penyesuaian Gaji ===========>
            Route::post('/get-penyesuaian-gaji', [PenyesuaianGajiController::class, 'index']);
            Route::post('/delete-gaji', [PenyesuaianGajiController::class, 'forceDeleteGaji']);
            Route::get('/penyesuaian-gaji/export', [PenyesuaianGajiController::class, 'exportPenyesuaianGaji']);
            Route::apiResource('/penyesuaian-gaji', PenyesuaianGajiController::class);

            // ! THR Penggajian ===========>
            Route::post('/get-thr', [THRPenggajianController::class, 'index']);
            Route::get('/run-thr/export', [THRPenggajianController::class, 'exportTHRPenggajian']);
            Route::apiResource('/run-thr', THRPenggajianController::class);

            // ! Tagihan Potongan ===========>
            Route::post('/get-tagihan-potongan', [TagihanPotonganController::class, 'index']);
            Route::get('/tagihan-potongan/export', [TagihanPotonganController::class, 'exportTagihanPotongan']);
            Route::post('/tagihan-potongan/import', [TagihanPotonganController::class, 'importTagihanPotongan']);
            Route::post('/tagihan-potongan/{id}/pelunasan', [TagihanPotonganController::class, 'pelunasan']);
            Route::apiResource('/tagihan-potongan', TagihanPotonganController::class);
        });

        Route::group(['prefix' => '/perusahaan'], function () {
            // ! Diklat ===========>
            Route::post('/get-diklat-internal', [DiklatController::class, 'indexInternal']);
            Route::post('/get-diklat-eksternal', [DiklatController::class, 'indexEksternal']);
            Route::delete('/delete-diklat-eksternal/{diklatId}', [DiklatController::class, 'deleteExternal']);
            Route::delete('/delete-diklat-internal/{diklatId}', [DiklatController::class, 'deleteInternal']);
            Route::post('/diklat', [DiklatController::class, 'store']);
            Route::post('/update-diklat/{diklatId}', [DiklatController::class, 'updateInternal']);
            Route::post('/diklat-eksternal-user', [DiklatController::class, 'storeExternal']);
            Route::post('/update-diklat-eksternal-user/{diklatId}', [DiklatController::class, 'updateExternal']);
            Route::get('/diklat/{diklatId}', [DiklatController::class, 'show']);
            Route::get('/diklat-internal/export', [DiklatController::class, 'exportDiklatInternal']);
            Route::get('/diklat-eksternal/export', [DiklatController::class, 'exportDiklatEksternal']);
            Route::delete('/diklat/{diklatId}/delete-peserta-diklat/{userId}', [DiklatController::class, 'fakeAssignDiklat']);
            Route::post('/diklat/{diklatId}/add-peserta-diklat', [DiklatController::class, 'assignDiklat']);
            Route::post('/diklat/{diklatId}/verifikasi-step-1', [DiklatController::class, 'verifikasiTahap1']);
            Route::post('/diklat/{diklatId}/verifikasi-step-2', [DiklatController::class, 'verifikasiTahap2']);
            Route::post('/diklat/{diklatId}/verifikasi-step-3', [DiklatController::class, 'verifikasiTahap3']);
            Route::post('/diklat/{diklatId}/certificates', [DiklatController::class, 'generateCertificate']);
            Route::post('/diklat/{diklatId}/verifikasi-diklat-eksternal-step-1', [DiklatController::class, 'verifikasiDiklatExternal_t1']);
            Route::post('/diklat/{diklatId}/verifikasi-diklat-eksternal-step-2', [DiklatController::class, 'verifikasiDiklatExternal_t2']);
            Route::post('/diklat/{diklatId}/verifikasi-diklat-eksternal-step-3', [DiklatController::class, 'verifikasiDiklatExternal_t3']);

            // ! Masa Diklat ===========>
            Route::post('/get-masa-diklat', [MasaDiklatController::class, 'index']);
            Route::post('/export-masa-diklat', [MasaDiklatController::class, 'exportMasaDiklat']);

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

            // ! Master Verification ===========>
            Route::post('/master-verifikasi/restore/{id}', [MasterVerificationController::class, 'restore']);
            Route::apiResource('/master-verifikasi', MasterVerificationController::class);

            // ! About RSKI ===========>
            Route::get('/about-hospitals/{id}', [AboutHospitalController::class, 'index']);
            Route::post('/about-hospitals/{id}', [AboutHospitalController::class, 'update']);

            // ! Jabatan ===========>
            Route::post('/jabatan/restore/{id}', [JabatanController::class, 'restore']);
            Route::apiResource('/jabatan', JabatanController::class);

            // ! Status Karyawan ===========>
            Route::post('/status-karyawan/restore/{id}', [StatusKaryawanController::class, 'restore']);
            Route::apiResource('/status-karyawan', StatusKaryawanController::class);

            // ! Materi Pelatihan
            Route::apiResource('/materi-pelatihan', MateriPelatihanController::class);

            // ! Kelompok Gaji ===========>
            Route::post('/kelompok-gaji/restore/{id}', [KelompokGajiController::class, 'restore']);
            Route::apiResource('/kelompok-gaji', KelompokGajiController::class);

            // ! Kompetensi ===========>
            Route::post('/kompetensi/restore/{id}', [KompetensiController::class, 'restore']);
            Route::apiResource('/kompetensi', KompetensiController::class);

            // ! Unit Kerja ===========>
            Route::post('/unit-kerja/restore/{id}', [UnitKerjaController::class, 'restore']);
            Route::apiResource('/unit-kerja', UnitKerjaController::class);

            // ! Spesialisasi ===========>
            Route::post('/spesialisasi/restore/{id}', [SpesialisasiController::class, 'restore']);
            Route::apiResource('/spesialisasi', SpesialisasiController::class);

            // ! Kategori Pendidikan ===========>
            Route::post('/pendidikan/restore/{id}', [PendidikanTerakhirController::class, 'restore']);
            Route::apiResource('/pendidikan', PendidikanTerakhirController::class);

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
            Route::post('/shift/import', [ShiftController::class, 'importShift']);
            Route::apiResource('/shift', ShiftController::class);

            // ! Non-Shift ===========>
            // Route::post('/non-shift/restore/{id}', [NonShiftController::class, 'restore']);
            // Route::apiResource('/non-shift', NonShiftController::class);
            Route::post('/non-shift/{non_shift}', [NonShiftController::class, 'edit']);
            Route::get('/non-shift/{non_shift}', [NonShiftController::class, 'show']);

            // ! Hari Libur ===========>
            Route::get('/hari-libur/nasional', [HariLiburController::class, 'getNasionalHariLibur']);
            Route::post('/hari-libur/restore/{id}', [HariLiburController::class, 'restore']);
            Route::apiResource('/hari-libur', HariLiburController::class);

            // ! Cuti ===========>
            Route::post('/cuti/restore/{id}', [CutiController::class, 'restore']);
            Route::apiResource('/cuti', CutiController::class)->names([
                'index'   => 'cuti.indexPengaturan',
                'store'   => 'cuti.storePengaturan',
                'show'    => 'cuti.showPengaturan',
                'update'  => 'cuti.updatePengaturan',
                'destroy' => 'cuti.destroyPengaturan',
            ]);

            // ! Hak Cuti ===========>
            Route::post('/hak-cuti/restore/{id}', [DataHakCutiController::class, 'restore']);
            Route::post('/get-hak-cuti', [DataHakCutiController::class, 'index']);
            Route::post('/hak-cuti/export', [DataHakCutiController::class, 'export']);
            Route::apiResource('/hak-cuti', DataHakCutiController::class);

            // ! Lokasi Presensi ===========>
            Route::get('/get-lokasi-kantor/{id}', [LokasiKantorController::class, 'getLokasiKantor']);
            Route::post('/lokasi-kantor', [LokasiKantorController::class, 'editLokasiKantor']);
        });
    });
});
