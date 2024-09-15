<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Exception;
use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\Ptkp;
use App\Models\User;
use App\Models\Premi;
use App\Models\Berkas;
use App\Models\Lembur;
use App\Models\Jabatan;
use App\Models\Presensi;
use App\Models\Penilaian;
use App\Models\UnitKerja;
use App\Models\Kompetensi;
use App\Models\Notifikasi;
use App\Models\TrackRecord;
use App\Models\TukarJadwal;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\KelompokGaji;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use App\Models\PesertaDiklat;
use Illuminate\Http\Response;
use App\Models\StatusKaryawan;
use App\Models\RiwayatPerubahan;
use App\Models\TransferKaryawan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateKaryawanExport;
use App\Exports\Karyawan\KaryawanExport;
use App\Imports\Karyawan\KaryawanImport;
use App\Http\Requests\StoreDataKaryawanRequest;
use App\Jobs\EmailNotification\AccountEmailJob;
use App\Http\Requests\UpdateDataKaryawanRequest;
use App\Http\Requests\Excel_Import\ImportKaryawanRequest;
use App\Http\Resources\Dashboard\Karyawan\KaryawanResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataKaryawanController extends Controller
{
  public function getAllDataUserNonShift()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $userNonShift = User::whereHas('data_karyawans.unit_kerjas', function ($query) {
      $query->where('jenis_karyawan', 0); // 0 = non shift
    })->where('nama', '!=', 'Super Admin')->where('status_aktif', 2)->get();

    if ($userNonShift->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan non shift tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    $formattedData = $userNonShift->map(function ($user) {
      $unitKerja = $user->data_karyawans->unit_kerjas ?? null;

      return [
        'id' => $user->id,
        'user' => [
          'id' => $user->id,
          'nama' => $user->nama,
          'username' => $user->username,
          'email_verified_at' => $user->email_verified_at,
          'data_karyawan_id' => $user->data_karyawan_id,
          'foto_profil' => $user->foto_profil,
          'data_completion_step' => $user->data_completion_step,
          'status_aktif' => $user->status_aktif,
          'created_at' => $user->created_at,
          'updated_at' => $user->updated_at
        ],
        'unit_kerja' => $unitKerja ? [
          'id' => $unitKerja->id,
          'nama_unit' => $unitKerja->nama_unit,
          'jenis_karyawan' => $unitKerja->jenis_karyawan
        ] : null,
      ];
    });

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all user non-shift for dropdown',
      'data' => $formattedData
    ], Response::HTTP_OK);
  }

  public function getAllDataUserShift()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $userShift = User::whereHas('data_karyawans.unit_kerjas', function ($query) {
      $query->where('jenis_karyawan', 1); // 1 = shift
    })->where('nama', '!=', 'Super Admin')->where('status_aktif', 2)->get();

    if ($userShift->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan shift tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    $formattedData = $userShift->map(function ($user) {
      $unitKerja = $user->data_karyawans->unit_kerjas ?? null;

      return [
        'id' => $user->id,
        'user' => [
          'id' => $user->id,
          'nama' => $user->nama,
          'username' => $user->username,
          'email_verified_at' => $user->email_verified_at,
          'data_karyawan_id' => $user->data_karyawan_id,
          'foto_profil' => $user->foto_profil,
          'data_completion_step' => $user->data_completion_step,
          'status_aktif' => $user->status_aktif,
          'created_at' => $user->created_at,
          'updated_at' => $user->updated_at
        ],
        'unit_kerja' => $unitKerja ? [
          'id' => $unitKerja->id,
          'nama_unit' => $unitKerja->nama_unit,
          'jenis_karyawan' => $unitKerja->jenis_karyawan
        ] : null,
      ];
    });

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all user shift for dropdown',
      'data' => $formattedData
    ], Response::HTTP_OK);
  }

  public function getAllDataUser()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $users = User::where('nama', '!=', 'Super Admin')->where('status_aktif', 2)->get();

    if ($users->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    $formattedData = $users->map(function ($user) {
      $unitKerja = $user->data_karyawans->unit_kerjas ?? null;

      return [
        'id' => $user->id,
        'user' => [
          'id' => $user->id,
          'nama' => $user->nama,
          'username' => $user->username,
          'email_verified_at' => $user->email_verified_at,
          'data_karyawan_id' => $user->data_karyawan_id,
          'foto_profil' => $user->foto_profil,
          'data_completion_step' => $user->data_completion_step,
          'status_aktif' => $user->status_aktif,
          'created_at' => $user->created_at,
          'updated_at' => $user->updated_at
        ],
        'unit_kerja' => $unitKerja ? [
          'id' => $unitKerja->id,
          'nama_unit' => $unitKerja->nama_unit,
          'jenis_karyawan' => $unitKerja->jenis_karyawan
        ] : null,
      ];
    });

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all user for dropdown',
      'data' => $formattedData
    ], Response::HTTP_OK);
  }

  public function getAllDataUnitKerja()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $unit_kerja = UnitKerja::all();
    if ($unit_kerja->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data unit kerja tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all unit kerja for dropdown',
      'data' => $unit_kerja
    ], Response::HTTP_OK);
  }

  public function getAllDataJabatan()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $jabatan = Jabatan::all();
    if ($jabatan->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data jabatan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all jabatan for dropdown',
      'data' => $jabatan
    ], Response::HTTP_OK);
  }

  public function getAllDataStatusKaryawan()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $status_karyawan = StatusKaryawan::all();
    if ($status_karyawan->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data status karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all status karyawan for dropdown',
      'data' => $status_karyawan
    ], Response::HTTP_OK);
  }

  public function getAllDataKompetensi()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $kompetensi = Kompetensi::all();
    if ($kompetensi->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data kompetensi tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all kompetensi for dropdown',
      'data' => $kompetensi
    ], Response::HTTP_OK);
  }

  public function getAllDataRole()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $user = Auth::user();
    $roles = Role::all();
    if ($roles->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data role tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    if ($user->nama !== 'Super Admin') {
      $roles = $roles->filter(function ($role) {
        return $role->name !== 'Super Admin';
      });
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all roles for dropdown',
      'data' => $roles->values()
    ], Response::HTTP_OK);
  }

  public function getAllDataKelompokGaji()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $kelompok_gaji = KelompokGaji::all();
    if ($kelompok_gaji->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data kelompok gaji tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all kelompok gaji for dropdown',
      'data' => $kelompok_gaji
    ], Response::HTTP_OK);
  }

  public function getAllDataPTKP()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $ptkp = Ptkp::all();
    if ($ptkp->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data ptkp tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all ptkp for dropdown',
      'data' => $ptkp
    ], Response::HTTP_OK);
  }

  public function getAllDataPremi()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $premi = Premi::all();
    if ($premi->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data premi tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all premi for dropdown',
      'data' => $premi
    ], Response::HTTP_OK);
  }

  public function getAllDataKaryawan()
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $dataKaryawan = DataKaryawan::where('id', '!=', 1)->get();
    if ($dataKaryawan->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all karyawan for dropdown',
      'data' => $dataKaryawan
    ], Response::HTTP_OK);
  }

  public function getDataPresensi($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Mendapatkan data presensi karyawan berdasarkan data_karyawan_id dan filter hari ini
    $presensi = Presensi::with([
      'users',
      'jadwals.shifts',
      'data_karyawans.unit_kerjas',
      'kategori_presensis'
    ])
      ->where('data_karyawan_id', $data_karyawan_id)
      // ->whereDate('jam_masuk', Carbon::today())
      ->first();

    if (!$presensi) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data presensi karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Ambil semua presensi bulan ini dari karyawan yang sama
    $presensiBulanIni = Presensi::where('data_karyawan_id', $data_karyawan_id)
      ->whereYear('jam_masuk', Carbon::now()->year)
      ->whereMonth('jam_masuk', Carbon::now()->month)
      ->orderBy('jam_masuk')
      ->get();

    // Memformat aktivitas presensi
    $aktivitasPresensi = [];
    foreach ($presensiBulanIni as $presensi) {
      if ($presensi->jam_masuk) {
        $aktivitasPresensi[] = [
          'presensi' => 'Masuk',
          'tanggal' => Carbon::parse($presensi->jam_masuk)->format('d-m-Y'),
          'jam' => Carbon::parse($presensi->jam_masuk)->format('H:i:s'),
        ];
      }
      if ($presensi->jam_keluar) {
        $aktivitasPresensi[] = [
          'presensi' => 'Keluar',
          'tanggal' => Carbon::parse($presensi->jam_keluar)->format('d-m-Y'),
          'jam' => Carbon::parse($presensi->jam_keluar)->format('H:i:s'),
        ];
      }
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail data presensi karyawan '{$presensi->users->nama}' berhasil ditampilkan.",
      'data' => [
        'id' => $presensi->id,
        'user' => $presensi->users,
        'unit_kerja' => $presensi->data_karyawans->unit_kerjas,
        'list_presensi' => $aktivitasPresensi
      ],
    ], Response::HTTP_OK);
  }

  public function getDataJadwal($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Cari data karyawan berdasarkan data_karyawan_id
    $karyawan = DataKaryawan::with(['users.jadwals.shifts', 'unit_kerjas'])
      ->where('id', '!=', 1)
      ->find($data_karyawan_id);

    if (!$karyawan) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Ambil data user yang terkait dengan karyawan
    $user = $karyawan->users;

    if (!$user) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'User tidak ditemukan untuk data karyawan ini.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Format data jadwal
    $user_schedule_array = [];
    foreach ($user->jadwals as $schedule) {
      $tglMulai = Carbon::createFromFormat('Y-m-d', $schedule->tgl_mulai)->format('d-m-Y');
      $tglSelesai = Carbon::createFromFormat('Y-m-d', $schedule->tgl_selesai)->format('d-m-Y');

      $current_date = Carbon::createFromFormat('d-m-Y', $tglMulai);
      $end_date = Carbon::createFromFormat('d-m-Y', $tglSelesai);
      while ($current_date->lte(Carbon::parse($tglSelesai))) {
        // $date = $current_date->format('Y-m-d');
        $user_schedule_array[] = [
          'id' => $schedule->id,
          // 'tanggal' => $date,
          'tgl_mulai' => $tglMulai,
          'tgl_selesai' => $tglSelesai,
          'shift' => $schedule->shifts,
          'updated_at' => $schedule->updated_at
        ];
        $current_date->addDay();
      }
    }

    if (empty($user_schedule_array)) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Jadwal karyawan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    // Format respons
    $formattedData = [
      'id' => $karyawan->id,
      'user' => [
        'id' => $user->id,
        'nama' => $user->nama,
        'username' => $user->username,
        'email_verified_at' => $user->email_verified_at,
        'data_karyawan_id' => $user->data_karyawan_id,
        'foto_profil' => $user->foto_profil,
        'data_completion_step' => $user->data_completion_step,
        'status_aktif' => $user->status_aktif,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at
      ],
      'unit_kerja' => $karyawan->unit_kerjas,
      'list_jadwal' => $user_schedule_array,
    ];

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail jadwal karyawan '{$user->nama}' berhasil ditampilkan.",
      'data' => $formattedData,
    ], Response::HTTP_OK);
  }

  public function getDataRekamJejak($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Cari karyawan berdasarkan data_karyawan_id
    $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);
    if (!$karyawan) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    // Ambil user dari karyawan
    $user = $karyawan->users;

    // Ambil semua rekam jejak dari user_id dengan kategori_record_id 2 dan 3
    $rekamJejakList = TrackRecord::where('user_id', $user->id)
      ->whereIn('kategori_record_id', [2, 3])
      ->get();

    // Ambil semua data perubahan dengan kategori_record_id 1
    $dataPerubahanList = TrackRecord::with(['users', 'kategori_track_records'])
      ->where('user_id', $user->id)
      ->where('kategori_record_id', 1)
      ->get();

    // Format data rekam jejak kategori 2 dan 3
    $formattedRekamJejak = $rekamJejakList->map(function ($item) {
      $user = $item->users;
      $transfer = TransferKaryawan::where('user_id', $user->id)->first();

      return [
        'id' => $item->id,
        'user' => [
          'id' => $user->id,
          'nama' => $user->nama,
          'username' => $user->username,
          'email_verified_at' => $user->email_verified_at,
          'data_karyawan_id' => $user->data_karyawan_id,
          'foto_profil' => $user->foto_profil,
          'data_completion_step' => $user->data_completion_step,
          'status_aktif' => $user->status_aktif,
          'created_at' => $user->created_at,
          'updated_at' => $user->updated_at
        ],
        'kategori_rekam_jejak' => $item->kategori_track_records,
        'content' => [
          'kategori_transfer' => $transfer->kategori_transfer_karyawans,
          'tgl_masuk' => $item->tgl_masuk,
          'tgl_keluar' => $item->tgl_keluar,
          'tgl_mulai' => $transfer->tgl_mulai,
          'unit_kerja_asal' => $transfer->unit_kerja_asals,
          'unit_kerja_tujuan' => $transfer->unit_kerja_tujuans,
          'jabatan_asal' => $transfer->jabatan_asals,
          'jabatan_tujuan' => $transfer->jabatan_tujuans,
          'kelompok_gaji_asal' => $transfer->kelompok_gaji_asals,
          'kelompok_gaji_tujuan' => $transfer->kelompok_gaji_tujuans,
          'role_asal' => $transfer->role_asals,
          'role_tujuan' => $transfer->role_tujuans,
          'alasan' => $transfer->alasan,
          'dokumen' => env('STORAGE_SERVER_DOMAIN') . $transfer->dokumen,
          'created_at' => $item->created_at,
          'updated_at' => $item->updated_at
        ],
        'created_at' => $item->created_at,
        'updated_at' => $item->updated_at
      ];
    });

    // Format data perubahan kategori 1
    $formattedDataPerubahan = [];
    foreach ($dataPerubahanList as $data_perubahan) {
      $relasiUser = $data_perubahan->users;
      $relasiKategori = $data_perubahan->kategori_track_records;
      $perubahanDataList = RiwayatPerubahan::where('data_karyawan_id', $user->id)->get();

      foreach ($perubahanDataList as $perubahanData) {
        $formattedDataPerubahan[] = [
          'id' => $data_perubahan->id,
          'user' => [
            'id' => $relasiUser->id,
            'nama' => $relasiUser->nama,
            'username' => $relasiUser->username,
            'email_verified_at' => $relasiUser->email_verified_at,
            'data_karyawan_id' => $relasiUser->data_karyawan_id,
            'foto_profil' => $relasiUser->foto_profil,
            'data_completion_step' => $relasiUser->data_completion_step,
            'status_aktif' => $relasiUser->status_aktif,
            'created_at' => $relasiUser->created_at,
            'updated_at' => $relasiUser->updated_at
          ],
          'kategori_rekam_jejak' => $relasiKategori,
          'content' => [
            'kolom' => $perubahanData->kolom,
            'original_data' => $perubahanData->original_data,
            'updated_data' => $perubahanData->updated_data,
            'status_perubahan' => $perubahanData->status_perubahans,
            'verifikator_1' => $perubahanData->verifikator_1_users,
            'alasan' => $perubahanData->alasan,
            'created_at' => $perubahanData->created_at,
            'updated_at' => $perubahanData->updated_at
          ]
        ];
      }
    }

    // Menggabungkan semua data yang diformat
    $allFormattedData = $formattedRekamJejak->merge($formattedDataPerubahan);

    if ($allFormattedData->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data rekam jejak karyawan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Data rekam jejak karyawan '{$user->nama}' berhasil ditampilkan.",
      'data' => [
        'id' => $karyawan->id,
        'user' => $user,
        'tgl_masuk_karyawan' => $karyawan->tgl_masuk,
        'tgl_keluar_karyawan' => $karyawan->tgl_keluar,
        // 'masa_kerja_karyawan' => $masaKerja,
        'list_rekam_jejak' => $allFormattedData
      ]
    ], Response::HTTP_OK);
  }

  // keluarga section
  public function getDataKeluarga($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Ambil data keluarga berdasarkan data_karyawan_id
    $keluarga = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)
      ->with('data_karyawans.users')
      ->get();

    if ($keluarga->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data keluarga karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Ambil data karyawan dan user dari data keluarga
    $dataKaryawan = $keluarga->first()->data_karyawans;
    $user = $dataKaryawan->users;

    $alasanDitolak = null;
    $status_keluarga = 'Diverifikasi';
    if ($keluarga->contains('status_keluarga_id', 1)) {
      $status_keluarga = 'Menunggu';
    } elseif ($keluarga->contains('status_keluarga_id', 3)) {
      $status_keluarga = 'Ditolak';
      $alasanDitolak = $keluarga->where('status_keluarga_id', 3)->sortByDesc('updated_at')->first()->alasan;
    }

    // Format data keluarga
    $formattedData = $keluarga->map(function ($item) {
      return [
        'id' => $item->id,
        'nama' => $item->nama_keluarga,
        'hubungan' => $item->hubungan,
        'pendidikan_terakhir' => $item->pendidikan_terakhir,
        'status_hidup' => $item->status_hidup,
        'pekerjaan' => $item->pekerjaan,
        'no_hp' => $item->no_hp,
        'email' => $item->email,
        'status_keluarga' => $item->status_keluargas,
        'is_bpjs' => $item->is_bpjs,
        'created_at' => $item->created_at,
        'updated_at' => $item->updated_at
      ];
    });

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail keluarga karyawan '{$user->nama}' berhasil ditampilkan.",
      'data' => [
        'id' => $dataKaryawan->id,
        'user' => [
          'id' => $user->id,
          'nama' => $user->nama,
          'username' => $user->username,
          'email_verified_at' => $user->email_verified_at,
          'data_karyawan_id' => $user->data_karyawan_id,
          'foto_profil' => $user->foto_profil,
          'data_completion_step' => $user->data_completion_step,
          'status_aktif' => $user->status_aktif,
          'created_at' => $user->created_at,
          'updated_at' => $user->updated_at,
        ],
        'jumlah_keluarga' => $keluarga->count(),
        'status_keluarga' => [
          'status' => $status_keluarga,
          'alasan' => $alasanDitolak,
          'terakhir_diperbarui' => $keluarga->sortByDesc('updated_at')->first()->updated_at
        ],
        'data_keluarga' => $formattedData,
      ],
    ], Response::HTTP_OK);
  }

  public function verifikasiKeluarga(Request $request, $data_karyawan_id)
  {
    if (!Gate::allows('edit dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $karyawan = DataKaryawan::find($data_karyawan_id);
    if (!$karyawan) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    $dataKeluargaList = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)
      ->where('status_keluarga_id', 1)
      ->get();
    if ($dataKeluargaList->isEmpty()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada anggota keluarga yang perlu diverifikasi untuk karyawan '{$karyawan->users->nama}'."), Response::HTTP_NOT_FOUND);
    }

    foreach ($dataKeluargaList as $keluarga) {
      $status_keluarga_id = $keluarga->status_keluarga_id;

      // disetujui tahap 1
      if ($request->has('verifikasi_disetujui') && $request->verifikasi_disetujui == 1) {
        if ($status_keluarga_id == 1) {
          $keluarga->status_keluarga_id = 2;
          $keluarga->verifikator_1 = Auth::id();
          $keluarga->save();

          $this->createNotifikasiKeluarga($keluarga, 'disetujui');
        } else {
          return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Anggota keluarga dari karyawan '{$karyawan->users->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
        }
      } else if ($request->has('verifikasi_ditolak') && $request->verifikasi_ditolak == 1) {
        if ($status_keluarga_id == 1) {
          $keluarga->status_keluarga_id = 3;
          $keluarga->is_bpjs = 0;
          $keluarga->verifikator_1 = Auth::id();
          $keluarga->alasan = $request->input('alasan');
          $keluarga->save();

          $this->createNotifikasiKeluarga($keluarga, 'ditolak');
        } else {
          return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Anggota keluarga karyawan '{$karyawan->users->nama}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
        }
      } else {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
      }
    }

    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi keluarga karyawan '{$karyawan->users->nama}' berhasil dilakukan."), Response::HTTP_OK);
  }

  // berkas section
  public function getDataDokumen($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Ambil data berkas berdasarkan data_karyawan_id
    $berkas = Berkas::where('user_id', function ($query) use ($data_karyawan_id) {
      $query->select('id')->from('users')->where('data_karyawan_id', $data_karyawan_id);
    })->where('kategori_berkas_id', '!=', 3) // get berkas selain 'System'
      ->get();

    if ($berkas->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data dokumen karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Ambil data user dari berkas yang pertama
    $user = $berkas->first()->users;
    $baseUrl = env('STORAGE_SERVER_DOMAIN');

    $alasanDitolak = null;
    $statusBerkas = 'Diverifikasi';
    if ($berkas->contains('status_berkas_id', 1)) {
      $statusBerkas = 'Menunggu';
    } elseif ($berkas->contains('status_berkas_id', 3)) {
      $statusBerkas = 'Ditolak';
      $alasanDitolak = $berkas->where('status_berkas_id', 3)->sortByDesc('updated_at')->first()->alasan;
    }

    // Format data berkas
    $formattedData = $berkas->map(function ($item) use ($baseUrl) {
      $fileExt = $item->ext ? StorageServerHelper::getExtensionFromMimeType($item->ext) : null;
      // $fileUrl = $baseUrl . $item->path . ($fileExt ? '.' . $fileExt : '');
      $fileUrl = $baseUrl . $item->path;

      return [
        'id' => $item->id,
        'file_id' => $item->file_id,
        'nama' => $item->nama,
        'kategori_dokumen' => $item->kategori_berkas,
        'path' => $fileUrl,
        'tgl_upload' => $item->tgl_upload,
        'nama_file' => $item->nama_file,
        'ext' => $item->ext,
        'size' => $item->size,
        'status_berkas' => $item->status_berkas,
        'created_at' => $item->created_at,
        'updated_at' => $item->updated_at,
      ];
    });

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail dokumen karyawan '{$user->nama}' berhasil ditampilkan.",
      'data' => [
        'id' => $user->data_karyawan_id,
        'user' => [
          'id' => $user->id,
          'nama' => $user->nama,
          'username' => $user->username,
          'email_verified_at' => $user->email_verified_at,
          'data_karyawan_id' => $user->data_karyawan_id,
          'foto_profil' => $user->foto_profil,
          'data_completion_step' => $user->data_completion_step,
          'status_aktif' => $user->status_aktif,
          'created_at' => $user->created_at,
          'updated_at' => $user->updated_at,
        ],
        'jumlah_dokumen' => $berkas->count(),
        'status_berkas' => [
          'status' => $statusBerkas,
          'alasan' => $alasanDitolak,
          'terakhir_diperbarui' => $berkas->sortByDesc('updated_at')->first()->updated_at
        ],
        'data_dokumen' => $formattedData,
      ],
    ], Response::HTTP_OK);
  }

  public function verifikasiBerkas(Request $request, $data_karyawan_id)
  {
    if (!Gate::allows('verifikasi1 berkas')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Cari user berdasarkan data_karyawan_id
    $user = User::where('data_karyawan_id', $data_karyawan_id)->first();

    if (!$user) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    // Ambil semua berkas terkait dengan user ini yang belum diverifikasi
    $berkasList = $user->berkas->where('status_berkas_id', 1);

    if ($berkasList->isEmpty()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada berkas yang perlu diverifikasi untuk karyawan '{$user->nama}'."), Response::HTTP_NOT_FOUND);
    }

    foreach ($berkasList as $berkas) {
      $status_berkas_id = $berkas->status_berkas_id;

      // Logika verifikasi disetujui tahap 1
      if ($request->has('verifikasi_disetujui') && $request->verifikasi_disetujui == 1) {
        if ($status_berkas_id == 1) {
          $berkas->status_berkas_id = 2; // Update status ke disetujui
          $berkas->verifikator_1 = Auth::id();
          $berkas->save();

          // Kirim notifikasi bahwa berkas telah diverifikasi
          $this->createNotifikasiBerkas($berkas, 'disetujui');
        } else {
          return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Berkas '{$berkas->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
        }
      } else if ($request->has('verifikasi_ditolak') && $request->verifikasi_ditolak == 1) {
        if ($status_berkas_id == 1) {
          $berkas->status_berkas_id = 3; // Update status ke ditolak
          $berkas->verifikator_1 = Auth::id();
          $berkas->alasan = $request->input('alasan');
          $berkas->save();

          // Kirim notifikasi bahwa berkas telah ditolak
          $this->createNotifikasiBerkas($berkas, 'ditolak');
        } else {
          return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Berkas '{$berkas->nama}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
        }
      } else {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
      }
    }

    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi berkas untuk karyawan '{$user->nama}' berhasil dilakukan."), Response::HTTP_OK);
  }

  public function getDataCuti($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Ambil data karyawan berdasarkan data_karyawan_id
    $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);
    if (!$karyawan) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    // Ambil semua data cuti yang dimiliki karyawan tersebut
    $dataCuti = Cuti::where('user_id', $karyawan->users->id)->get();

    if ($dataCuti->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data cuti karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Format list cuti
    $listCuti = $dataCuti->map(function ($cuti) {
      return [
        'id' => $cuti->id,
        'tipe_cuti' => $cuti->tipe_cutis,
        'tgl_from' => $cuti->tgl_from,
        'tgl_to' => $cuti->tgl_to,
        'catatan' => $cuti->catatan,
        'durasi' => $cuti->durasi,
        'status_cuti' => $cuti->status_cutis,
        'created_at' => $cuti->created_at,
        'updated_at' => $cuti->updated_at
      ];
    });

    // Format data user
    $formattedData = [
      'id' => $karyawan->id,
      'user' => [
        'id' => $karyawan->users->id,
        'nama' => $karyawan->users->nama,
        'username' => $karyawan->users->username,
        'email_verified_at' => $karyawan->users->email_verified_at,
        'data_karyawan_id' => $karyawan->users->data_karyawan_id,
        'foto_profil' => $karyawan->users->foto_profil,
        'data_completion_step' => $karyawan->users->data_completion_step,
        'status_aktif' => $karyawan->users->status_aktif,
        'created_at' => $karyawan->users->created_at,
        'updated_at' => $karyawan->users->updated_at
      ],
      'unit_kerja' => $karyawan->unit_kerjas,
      'list_cuti' => $listCuti
    ];

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail data cuti karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
      'data' => $formattedData
    ], Response::HTTP_OK);
  }

  public function getDataTukarJadwal($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Cari user berdasarkan data_karyawan_id
    $karyawan = DataKaryawan::find($data_karyawan_id);
    if (!$karyawan) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    // Ambil user id dari karyawan
    $userId = $karyawan->users->id;

    // Cari tukar jadwal di mana user menjadi user_pengajuan atau user_ditukar
    $tukarJadwal = TukarJadwal::where('user_pengajuan', $userId)
      ->orWhere('user_ditukar', $userId)
      ->get();

    if ($tukarJadwal->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data tukar jadwal karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Format data tukar jadwal
    $formattedData = $tukarJadwal->map(function ($item) use ($userId) {
      $isUserPengajuan = $item->user_pengajuan == $userId;
      return [
        'id' => $item->id,
        'user_pengajuan' => [
          'user' => [
            'id' => $item->user_pengajuans->id,
            'nama' => $item->user_pengajuans->nama,
            'username' => $item->user_pengajuans->username,
            'email_verified_at' => $item->user_pengajuans->email_verified_at,
            'data_karyawan_id' => $item->user_pengajuans->data_karyawan_id,
            'foto_profil' => $item->user_pengajuans->foto_profil,
            'data_completion_step' => $item->user_pengajuans->data_completion_step,
            'status_aktif' => $item->user_pengajuans->status_aktif,
            'created_at' => $item->user_pengajuans->created_at,
            'updated_at' => $item->user_pengajuans->updated_at,
          ],
          'jadwal' => $item->jadwal_pengajuans,
          'status' => $item->status_tukar_jadwals,
          'kategori' => $item->kategori_tukar_jadwals,
        ],
        'user_ditukar' => [
          'user' => [
            'id' => $item->user_ditukars->id,
            'nama' => $item->user_ditukars->nama,
            'username' => $item->user_ditukars->username,
            'email_verified_at' => $item->user_ditukars->email_verified_at,
            'data_karyawan_id' => $item->user_ditukars->data_karyawan_id,
            'foto_profil' => $item->user_ditukars->foto_profil,
            'data_completion_step' => $item->user_ditukars->data_completion_step,
            'status_aktif' => $item->user_ditukars->status_aktif,
            'created_at' => $item->user_ditukars->created_at,
            'updated_at' => $item->user_ditukars->updated_at,
          ],
          'jadwal' => $item->jadwal_ditukars,
          'status' => $item->status_tukar_jadwals,
          'kategori' => $item->kategori_tukar_jadwals,
        ],
      ];
    });

    // Menentukan data user dan unit_kerja
    $dataUser = $karyawan->users;
    $unitKerja = $karyawan->unit_kerjas;

    // Menentukan nama yang ditampilkan dalam pesan
    $message = '';
    if ($tukarJadwal->first()->user_pengajuan == $userId) {
      $message = "Detail tukar jadwal karyawan '{$tukarJadwal->first()->user_pengajuans->nama}' berhasil ditampilkan.";
    } else {
      $message = "Detail tukar jadwal karyawan '{$tukarJadwal->first()->user_ditukars->nama}' berhasil ditampilkan.";
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => $message,
      'data' => [
        'id' => $karyawan->id,
        'user' => $dataUser,
        'unit_kerja' => $unitKerja,
        'list_tukar_jadwal' => $formattedData
      ]
    ], Response::HTTP_OK);
  }

  public function getDataLembur($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Cari data karyawan berdasarkan data_karyawan_id
    $karyawan = DataKaryawan::find($data_karyawan_id);

    if (!$karyawan) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    // Ambil semua data lembur berdasarkan user_id yang terkait dengan data_karyawan_id
    $dataLembur = Lembur::whereHas('users', function ($query) use ($data_karyawan_id) {
      $query->where('data_karyawan_id', $data_karyawan_id);
    })->get();

    if ($dataLembur->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data lembur karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Format data lembur
    $formattedData = $dataLembur->map(function ($lembur) {
      return [
        'id' => $lembur->id,
        'jadwal' => [
          'id' => $lembur->jadwals->id,
          'tgl_mulai' => $lembur->jadwals->tgl_mulai,
          'tgl_selesai' => $lembur->jadwals->tgl_selesai,
          'shift' => $lembur->jadwals->shifts
        ],
        'tgl_pengajuan' => $lembur->tgl_pengajuan,
        'kompensasi_lembur' => $lembur->kategori_kompensasis,
        'durasi' => $lembur->durasi,
        'catatan' => $lembur->catatan,
        'status_lembur' => $lembur->status_lemburs,
        'created_at' => $lembur->created_at,
        'updated_at' => $lembur->updated_at
      ];
    });

    // Menentukan data user dan unit_kerja
    $dataUser = [
      'id' => $karyawan->users->id,
      'nama' => $karyawan->users->nama,
      'email_verified_at' => $karyawan->users->email_verified_at,
      'data_karyawan_id' => $karyawan->users->data_karyawan_id,
      'foto_profil' => $karyawan->users->foto_profil,
      'data_completion_step' => $karyawan->users->data_completion_step,
      'status_aktif' => $karyawan->users->status_aktif,
      'created_at' => $karyawan->users->created_at,
      'updated_at' => $karyawan->users->updated_at
    ];
    $unitKerja = $karyawan->unit_kerjas;

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail data lembur karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
      'data' => [
        'id' => $karyawan->id,
        'user' => $dataUser,
        'unit_kerja' => $unitKerja,
        'list_lembur' => $formattedData
      ]
    ], Response::HTTP_OK);
  }

  public function getDataFeedbackPenilaian($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Ambil data user yang dinilai berdasarkan data_karyawan_id
    $userDinilai = User::whereHas('data_karyawans', function ($query) use ($data_karyawan_id) {
      $query->where('id', $data_karyawan_id);
    })->with('data_karyawans.jabatans')->first();

    if (!$userDinilai) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Ambil penilaian terkait berdasarkan user_dinilai
    $penilaian = Penilaian::with(['user_penilais', 'jenis_penilaians'])
      ->where('user_dinilai', $userDinilai->id)
      ->get();

    if ($penilaian->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data penilaian untuk karyawan ini tidak ditemukan.'
      ], Response::HTTP_NOT_FOUND);
    }

    // Format list penilaian
    $listPenilaian = $penilaian->map(function ($penilaian) {
      return [
        'id' => $penilaian->id,
        'jenis_penilaian' => $penilaian->jenis_penilaians,
        'user_penilai' => $penilaian->user_penilais,
        'pertanyaan_jawaban' => $penilaian->pertanyaan_jawaban,
        'total_pertanyaan' => $penilaian->total_pertanyaan,
        'rata_rata' => $penilaian->rata_rata,
        'created_at' => $penilaian->created_at,
        'updated_at' => $penilaian->updated_at,
      ];
    });

    // Format Data Utama
    $formattedData = [
      'id' => $userDinilai->data_karyawan_id,
      'user' => [
        'id' => $userDinilai->id,
        'nama' => $userDinilai->nama,
        'username' => $userDinilai->username,
        'email_verified_at' => $userDinilai->email_verified_at,
        'data_karyawan_id' => $userDinilai->data_karyawan_id,
        'foto_profil' => $userDinilai->foto_profil,
        'data_completion_step' => $userDinilai->data_completion_step,
        'status_aktif' => $userDinilai->status_aktif,
        'created_at' => $userDinilai->created_at,
        'updated_at' => $userDinilai->updated_at
      ],
      'jabatan' => $userDinilai->data_karyawans->jabatans,
      'list_penilaian' => $listPenilaian,
    ];

    // Response dengan data detail penilaian
    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Detail penilaian berhasil ditampilkan.',
      'data' => $formattedData
    ], Response::HTTP_OK);
  }

  public function getDataDiklat($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Ambil semua peserta_diklat yang sesuai dengan data_karyawan_id tanpa filter tambahan
    $pesertaDiklats = PesertaDiklat::where('peserta', $data_karyawan_id)
      ->with('diklats', 'diklats.berkas_dokumen_eksternals', 'users')
      ->get();

    if ($pesertaDiklats->isEmpty()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data diklat yang ditemukan untuk karyawan ini.'), Response::HTTP_NOT_FOUND);
    }

    $userName = $pesertaDiklats->first()->users;
    $formattedData = $pesertaDiklats->map(function ($pesertaDiklat) {
      $diklat = $pesertaDiklat->diklats;
      $server_url = env('STORAGE_SERVER_DOMAIN');

      return [
        'id' => $diklat->id,
        'nama' => $diklat->nama,
        'kategori_diklat_id' => $diklat->kategori_diklats,
        'status_diklat_id' => $diklat->status_diklats,
        'deskripsi' => $diklat->deskripsi,
        'kuota' => $diklat->kuota,
        'total_peserta' => $diklat->total_peserta,
        'tgl_mulai' => $diklat->tgl_mulai,
        'tgl_selesai' => $diklat->tgl_selesai,
        'jam_mulai' => $diklat->jam_mulai,
        'jam_selesai' => $diklat->jam_selesai,
        'durasi' => $diklat->durasi,
        'lokasi' => $diklat->lokasi,
        'dokumen_eksternal' => $diklat->berkas_dokumen_eksternals ? [
          'id' => $diklat->berkas_dokumen_eksternals->id,
          'nama_file' => $diklat->berkas_dokumen_eksternals->nama_file,
          'path' => $server_url . $diklat->berkas_dokumen_eksternals->path,
          'ext' => $diklat->berkas_dokumen_eksternals->ext,
          'size' => $diklat->berkas_dokumen_eksternals->size,
          'tgl_upload' => $diklat->berkas_dokumen_eksternals->tgl_upload,
          'created_at' => $diklat->berkas_dokumen_eksternals->created_at,
          'updated_at' => $diklat->berkas_dokumen_eksternals->updated_at
        ] : null,
        'verifikator_1' => $diklat->verifikator_1_diklats,
        'verifikator_2' => $diklat->verifikator_2_diklats,
        'certificate_published' => $diklat->certificate_published,
        'certificate_verified_by' => $diklat->certificate_diklats,
        'alasan' => $diklat->alasan,
        'created_at' => $diklat->created_at,
        'updated_at' => $diklat->updated_at
      ];
    });

    // Response JSON dengan semua kolom dari jadwal diklat yang diikuti dan total durasi
    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Data diklat dari karyawan '{$userName->nama}' berhasil ditampilkan.",
      'data' => [
        'user' => [
          'id' => $userName->id,
          'nama' => $userName->nama,
          'username' => $userName->username,
          'email_verified_at' => $userName->email_verified_at,
          'data_karyawan_id' => $userName->data_karyawan_id,
          'foto_profil' => $userName->foto_profil,
          'data_completion_step' => $userName->data_completion_step,
          'status_aktif' => $userName->status_aktif,
          'created_at' => $userName->created_at,
          'updated_at' => $userName->updated_at
        ],
        'unit_kerja' => $userName->data_karyawans->unit_kerjas,
        'jadwal_diklat' => $formattedData
      ]
    ], Response::HTTP_OK);
  }

  public function index(Request $request)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Per page
    $limit = $request->input('limit', 10);

    $karyawan = DataKaryawan::query()->where('id', '!=', 1)->orderBy('created_at', 'desc');

    // Ambil semua filter dari request body
    $filters = $request->all();

    // Filter
    if (isset($filters['unit_kerja'])) {
      $namaUnitKerja = $filters['unit_kerja'];
      $karyawan->whereHas('unit_kerjas', function ($query) use ($namaUnitKerja) {
        if (is_array($namaUnitKerja)) {
          $query->whereIn('id', $namaUnitKerja);
        } else {
          $query->where('id', '=', $namaUnitKerja);
        }
      });
    }

    if (isset($filters['jabatan'])) {
      $namaJabatan = $filters['jabatan'];
      $karyawan->whereHas('jabatans', function ($query) use ($namaJabatan) {
        if (is_array($namaJabatan)) {
          $query->whereIn('id', $namaJabatan);
        } else {
          $query->where('id', '=', $namaJabatan);
        }
      });
    }

    if (isset($filters['status_karyawan'])) {
      $statusKaryawan = $filters['status_karyawan'];
      $karyawan->whereHas('status_karyawans', function ($query) use ($statusKaryawan) {
        if (is_array($statusKaryawan)) {
          $query->whereIn('id', $statusKaryawan);
        } else {
          $query->where('id', '=', $statusKaryawan);
        }
      });
    }

    if (isset($filters['masa_kerja'])) {
      $masaKerja = $filters['masa_kerja'];
      if (is_array($masaKerja)) {
        $karyawan->where(function ($query) use ($masaKerja) {
          foreach ($masaKerja as $masa) {
            $bulan = $masa * 12;
            $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
          }
        });
      } else {
        $bulan = $masaKerja * 12;
        $karyawan->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
      }
    }

    if (isset($filters['status_aktif'])) {
      $statusAktif = $filters['status_aktif'];
      $karyawan->whereHas('users', function ($query) use ($statusAktif) {
        if (is_array($statusAktif)) {
          $query->whereIn('status_aktif', $statusAktif);
        } else {
          $query->where('status_aktif', '=', $statusAktif);
        }
      });
    }

    if (isset($filters['tgl_masuk'])) {
      $tglMasuk = $filters['tgl_masuk'];
      if (is_array($tglMasuk)) {
        foreach ($tglMasuk as &$tgl) {
          $tgl = RandomHelper::convertToDateString($tgl);
        }
        $karyawan->whereIn('tgl_masuk', $tglMasuk);
      } else {
        $tglMasuk = RandomHelper::convertToDateString($tglMasuk);
        $karyawan->where('tgl_masuk', $tglMasuk);
      }
    }

    if (isset($filters['agama'])) {
      $namaAgama = $filters['agama'];
      $karyawan->whereHas('kategori_agamas', function ($query) use ($namaAgama) {
        if (is_array($namaAgama)) {
          $query->whereIn('id', $namaAgama);
        } else {
          $query->where('id', '=', $namaAgama);
        }
      });
    }

    if (isset($filters['jenis_kelamin'])) {
      $jenisKelamin = $filters['jenis_kelamin'];
      if (is_array($jenisKelamin)) {
        $karyawan->where(function ($query) use ($jenisKelamin) {
          foreach ($jenisKelamin as $jk) {
            $query->orWhere('jenis_kelamin', $jk);
          }
        });
      } else {
        $karyawan->where('jenis_kelamin', $jenisKelamin);
      }
    }

    if (isset($filters['pendidikan_terakhir'])) {
      $namaPendidikan = $filters['pendidikan_terakhir'];
      $karyawan->whereHas('kategori_pendidikans', function ($query) use ($namaPendidikan) {
        if (is_array($namaPendidikan)) {
          $query->whereIn('id', $namaPendidikan);
        } else {
          $query->where('id', '=', $namaPendidikan);
        }
      });
    }

    if (isset($filters['jenis_karyawan'])) {
      $jenisKaryawan = $filters['jenis_karyawan'];
      $karyawan->whereHas('unit_kerjas', function ($query) use ($jenisKaryawan) {
        if (is_array($jenisKaryawan)) {
          $query->whereIn('jenis_karyawan', $jenisKaryawan);
        } else {
          $query->where('jenis_karyawan', '=', $jenisKaryawan);
        }
      });
    }

    if (isset($filters['masa_diklat'])) {
      $masaDiklatJam = $filters['masa_diklat'];
      if (is_array($masaDiklatJam)) {
        $karyawan->where(function ($query) use ($masaDiklatJam) {
          foreach ($masaDiklatJam as $jam) {
            $detik = $jam * 3600; // Konversi dari jam ke detik
            $query->orWhere('masa_diklat', '<=', $detik);
          }
        });
      } else {
        $detik = $masaDiklatJam * 3600; // Konversi dari jam ke detik
        $karyawan->where('masa_diklat', '<=', $detik);
      }
    }

    // Search
    if (isset($filters['search'])) {
      $searchTerm = '%' . $filters['search'] . '%';
      $karyawan->where(function ($query) use ($searchTerm) {
        $query->whereHas('users', function ($query) use ($searchTerm) {
          $query->where('nama', 'like', $searchTerm);
        })->orWhere('nik', 'like', $searchTerm);
      });
    }

    if ($limit == 0) {
      $dataKaryawan = $karyawan->get();
      $paginationData = null;
    } else {
      $limit = is_numeric($limit) ? (int)$limit : 10;
      $dataKaryawan = $karyawan->paginate($limit);

      $paginationData = [
        'links' => [
          'first' => $dataKaryawan->url(1),
          'last' => $dataKaryawan->url($dataKaryawan->lastPage()),
          'prev' => $dataKaryawan->previousPageUrl(),
          'next' => $dataKaryawan->nextPageUrl(),
        ],
        'meta' => [
          'current_page' => $dataKaryawan->currentPage(),
          'last_page' => $dataKaryawan->lastPage(),
          'per_page' => $dataKaryawan->perPage(),
          'total' => $dataKaryawan->total(),
        ]
      ];
    }

    if ($dataKaryawan->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    $formattedData = $dataKaryawan->map(function ($karyawan) {
      $dataKeluargas = $karyawan->data_keluargas;
      $ayah = $dataKeluargas->where('hubungan', 'Ayah')->first();
      $ibu = $dataKeluargas->where('hubungan', 'Ibu')->first();
      $jumlahKeluarga = $dataKeluargas->count();

      $role = $karyawan->users->roles->first();

      return [
        'id' => $karyawan->id,
        'user' => [
          'id' => $karyawan->users->id,
          'nama' => $karyawan->users->nama,
          'username' => $karyawan->users->username,
          'email_verified_at' => $karyawan->users->email_verified_at,
          'data_karyawan_id' => $karyawan->users->data_karyawan_id,
          'foto_profil' => $karyawan->users->foto_profil,
          'data_completion_step' => $karyawan->users->data_completion_step,
          'status_aktif' => $karyawan->users->status_aktif,
          'created_at' => $karyawan->users->created_at,
          'updated_at' => $karyawan->users->updated_at
        ],
        'role' => [
          'id' => $role->id,
          'name' => $role->name,
          'deskripsi' => $role->deskripsi,
          'created_at' => $role->created_at,
          'updated_at' => $role->updated_at
        ], // role_id
        'email' => $karyawan->email,
        'nik' => $karyawan->nik,
        'no_rm' => $karyawan->no_rm,
        'no_manulife' => $karyawan->no_manulife,
        'tgl_masuk' => $karyawan->tgl_masuk,
        'unit_kerja' => $karyawan->unit_kerjas, // unit_kerja_id
        'jabatan' => $karyawan->jabatans, // jabatan_id
        'kompetensi' => $karyawan->kompetensis, // kompetensi_id
        'nik_ktp' => $karyawan->nik_ktp,
        'status_karyawan' => $karyawan->status_karyawans, // status_karyawan_id
        'tempat_lahir' => $karyawan->tempat_lahir,
        'tgl_lahir' => $karyawan->tgl_lahir,
        'kelompok_gaji' => $karyawan->kelompok_gajis, // kelompok_gaji_id
        'no_rekening' => $karyawan->no_rekening,
        'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
        'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
        'tunjangan_khusus' => $karyawan->tunjangan_khusus,
        'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
        'uang_lembur' => $karyawan->uang_lembur,
        'uang_makan' => $karyawan->uang_makan,
        'ptkp' => $karyawan->ptkps, // ptkp_id
        'tgl_keluar' => $karyawan->tgl_keluar,
        'no_kk' => $karyawan->no_kk,
        'alamat' => $karyawan->alamat,
        'gelar_depan' => $karyawan->gelar_depan,
        'gelar_belakang' => $karyawan->gelar_belakang,
        'no_hp' => $karyawan->no_hp,
        'no_bpjsksh' => $karyawan->no_bpjsksh,
        'no_bpjsktk' => $karyawan->no_bpjsktk,
        'tgl_diangkat' => $karyawan->tgl_diangkat,
        'masa_kerja' => $karyawan->masa_kerja,
        'npwp' => $karyawan->npwp,
        'jenis_kelamin' => $karyawan->jenis_kelamin,
        'agama' => $karyawan->kategori_agamas, // agama_id
        'golongan_darah' => $karyawan->kategori_darahs, // golongan_darah_id
        'pendidikan_terakhir' => $karyawan->pendidikan_terakhir,
        'asal_sekolah' => $karyawan->asal_sekolah,
        'tinggi_badan' => $karyawan->tinggi_badan,
        'berat_badan' => $karyawan->berat_badan,
        'no_ijazah' => $karyawan->no_ijazah,
        'tahun_lulus' => $karyawan->tahun_lulus,
        'no_str' => $karyawan->no_str,
        'masa_berlaku_str' => $karyawan->masa_berlaku_str,
        'masa_berlaku_sip' => $karyawan->masa_berlaku_sip,
        'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
        'masa_diklat' => $karyawan->masa_diklat,
        'jumlah_keluarga' => $jumlahKeluarga,
        'ibu' => $ibu ? $ibu->nama_keluarga : null,
        'ayah' => $ayah ? $ayah->nama_keluarga : null,
        'created_at' => $karyawan->created_at,
        'updated_at' => $karyawan->updated_at
      ];
    });

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Data karyawan berhasil ditampilkan.',
      'data' => $formattedData,
      'pagination' => $paginationData
    ], Response::HTTP_OK);
  }

  // TODO: Untuk RSKI, kalau udah ke product login email aja
  public function store(StoreDataKaryawanRequest $request)
  {
    if (!Gate::allows('create dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $data = $request->validated();
    $requestedRoleId = $request->input('role_id');
    $premis = $request->input('premi_id', []); // Mengambil daftar premi yang dipilih

    $generatedUsername = RandomHelper::generateUsername($data['nama']);
    if (!empty($data['email'])) {
      $generatedPassword = RandomHelper::generatePassword();
      $passwordHash = Hash::make($generatedPassword);
    } else {
      $passwordHash = Hash::make('1234');
    }

    DB::beginTransaction();
    try {
      $userData = [
        'nama' => $data['nama'],
        'status_aktif' => 1,
        'role_id' => $data['role_id'],
        'username' => $generatedUsername,
        'password' => $passwordHash,
      ];

      $createUser = User::create($userData);
      $createUser->roles()->attach($requestedRoleId);

      $createDataKaryawan = new DataKaryawan([
        'user_id' => $createUser->id,
        'email' => $data['email'],
        'no_rm' => $data['no_rm'],
        'no_manulife' => $data['no_manulife'],
        'nik' => $data['nik'],
        'tgl_masuk' => $data['tgl_masuk'],
        'tgl_berakhir_pks' => $data['tgl_berakhir_pks'],
        'unit_kerja_id' => $data['unit_kerja_id'],
        'jabatan_id' => $data['jabatan_id'],
        'kompetensi_id' => $data['kompetensi_id'] ?? null,
        'status_karyawan_id' => $data['status_karyawan_id'],
        'kelompok_gaji_id' => $data['kelompok_gaji_id'],
        'no_rekening' => $data['no_rekening'],
        // 'tunjangan_jabatan' => $data['tunjangan_jabatan'],
        'tunjangan_fungsional' => $data['tunjangan_fungsional'],
        'tunjangan_khusus' => $data['tunjangan_khusus'],
        'tunjangan_lainnya' => $data['tunjangan_lainnya'],
        'uang_makan' => $data['uang_makan'],
        'uang_lembur' => $data['uang_lembur'],
        'ptkp_id' => $data['ptkp_id'],
        'tgl_diangkat' => $data['tgl_diangkat'],
      ]);
      $createDataKaryawan->save();

      // Update data_karyawan_id pada tabel users setelah DataKaryawan berhasil dibuat
      $createUser->update(['data_karyawan_id' => $createDataKaryawan->id]);

      // Masukkan data ke tabel pengurang_gajis jika ada premi yang dipilih
      if (!empty($premis)) {
        $premisData = DB::table('premis')->whereIn('id', $premis)->get();
        if ($premisData->isEmpty()) {
          return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Potongan yang dipilih tidak valid.'), Response::HTTP_NOT_FOUND);
        }

        foreach ($premisData as $premi) {
          DB::table('pengurang_gajis')->insert([
            'data_karyawan_id' => $createDataKaryawan->id,
            'premi_id' => $premi->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);
        }
      }

      DB::commit();

      if (!empty($data['email'])) {
        AccountEmailJob::dispatch($data['email'], $generatedPassword, $data['nama']);
      }

      // Mail::to($data['email'])->send(new SendAccoundUsersMail($data['email'], $generatedPassword, $data['nama']));
      return response()->json(new KaryawanResource(Response::HTTP_OK, "Data karyawan '{$createDataKaryawan->users->nama}' berhasil dibuat.", $createDataKaryawan), Response::HTTP_OK);
    } catch (\Throwable $th) {
      DB::rollBack();
      return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Maaf sepertinya pembuatan data karyawan bermasalah, Error: ' . $th->getMessage()), Response::HTTP_BAD_REQUEST);
    }
  }

  public function showByUserId($user_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    // Find the user by user_id
    $user = User::find($user_id);

    if (!$user) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Akun karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    // Get data_karyawan_id from user
    $data_karyawan_id = $user->data_karyawan_id;
    $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);
    if (!$karyawan) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    $keluargaList = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)
      ->get();

    $statusKeluarga = false;
    foreach ($keluargaList as $keluarga) {
      if ($keluarga->status_keluarga_id == 1) {
        $statusKeluarga = true; // Butuh verifikasi
        break;
      }
    }

    if (!$statusKeluarga) {
      foreach ($keluargaList as $keluarga) {
        if ($keluarga->status_keluarga_id == 2 || $keluarga->status_keluarga_id == 3) {
          $statusKeluarga = false;
          break;
        }
      }
    }

    $role = $karyawan->users->roles->first();

    // diklat calculated
    $total_durasi_internal = PesertaDiklat::whereHas('diklats', function ($query) {
      $query->where('kategori_diklat_id', 1); // 1 = Internal
    })
      ->where('peserta', $karyawan->users->id)
      ->with('diklats')
      ->get()
      ->sum(function ($pesertaDiklat) {
        return $pesertaDiklat->diklats->durasi;
      });

    $total_durasi_eksternal = PesertaDiklat::whereHas('diklats', function ($query) {
      $query->where('kategori_diklat_id', 2); // 2 = Eksternal
    })
      ->where('peserta', $karyawan->users->id)
      ->with('diklats')
      ->get()
      ->sum(function ($pesertaDiklat) {
        return $pesertaDiklat->diklats->durasi;
      });

    // $berkasFields = [
    //   'file_ktp' => $karyawan->file_ktp ?? null,
    //   'file_kk' => $karyawan->file_kk ?? null,
    //   'file_sip' => $karyawan->file_sip ?? null,
    //   'file_bpjs_kesehatan' => $karyawan->file_bpjsksh ?? null,
    //   'file_bpjs_ketenagakerjaan' => $karyawan->file_bpjsktk ?? null,
    //   'file_ijazah' => $karyawan->file_ijazah ?? null,
    //   'file_sertifikat' => $karyawan->file_sertifikat ?? null,
    // ];

    // $baseUrl = env('STORAGE_SERVER_DOMAIN');

    // $formattedPaths = [];
    // foreach ($berkasFields as $field => $berkasId) {
    //   $berkas = Berkas::where('id', $berkasId)->first();
    //   if ($berkas) {
    //     $extension = StorageServerHelper::getExtensionFromMimeType($berkas->ext);
    //     // $formattedPaths[$field] = $baseUrl . $berkas->path . '.' . $extension;
    //     $formattedPaths[$field] = $baseUrl . $berkas->path;
    //   } else {
    //     $formattedPaths[$field] = null;
    //   }
    // }

    // Format the karyawan data
    $formattedData = [
      'id' => $karyawan->id,
      'user' => [
        'id' => $karyawan->users->id,
        'nama' => $karyawan->users->nama,
        'username' => $karyawan->users->username,
        'email_verified_at' => $karyawan->users->email_verified_at,
        'data_karyawan_id' => $karyawan->users->data_karyawan_id,
        'foto_profil' => $karyawan->users->foto_profil,
        'data_completion_step' => $karyawan->users->data_completion_step,
        'status_aktif' => $karyawan->users->status_aktif,
        'created_at' => $karyawan->users->created_at,
        'updated_at' => $karyawan->users->updated_at
      ],
      'role' => [
        'id' => $role->id,
        'name' => $role->name,
        'deskripsi' => $role->deskripsi,
        'created_at' => $role->created_at,
        'updated_at' => $role->updated_at
      ],
      'potongan_gaji' => DB::table('pengurang_gajis')
        ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
        ->where('pengurang_gajis.data_karyawan_id', $karyawan->id)
        ->select(
          'premis.id',
          'premis.nama_premi',
          'premis.kategori_potongan_id',
          'premis.jenis_premi',
          'premis.besaran_premi',
          'premis.minimal_rate',
          'premis.maksimal_rate',
          'premis.created_at',
          'premis.updated_at'
        )
        ->get(),
      'nik' => $karyawan->nik,
      'email' => $karyawan->email,
      'no_rm' => $karyawan->no_rm,
      'no_sip' => $karyawan->no_sip,
      'no_manulife' => $karyawan->no_manulife,
      'tgl_masuk' => $karyawan->tgl_masuk,
      'unit_kerja' => $karyawan->unit_kerjas,
      'jabatan' => $karyawan->jabatans,
      'kompetensi' => $karyawan->kompetensis,
      'nik_ktp' => $karyawan->nik_ktp,
      'status_karyawan' => $karyawan->status_karyawans,
      'tempat_lahir' => $karyawan->tempat_lahir,
      'tgl_lahir' => $karyawan->tgl_lahir,
      'kelompok_gaji' => $karyawan->kelompok_gajis,
      'no_rekening' => $karyawan->no_rekening,
      'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
      'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
      'tunjangan_khusus' => $karyawan->tunjangan_khusus,
      'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
      'uang_lembur' => $karyawan->uang_lembur,
      'uang_makan' => $karyawan->uang_makan,
      'ptkp' => $karyawan->ptkps,
      'tgl_keluar' => $karyawan->tgl_keluar,
      'no_kk' => $karyawan->no_kk,
      'alamat' => $karyawan->alamat,
      'gelar_depan' => $karyawan->gelar_depan,
      'gelar_belakang' => $karyawan->gelar_belakang,
      'no_hp' => $karyawan->no_hp,
      'no_bpjsksh' => $karyawan->no_bpjsksh,
      'no_bpjsktk' => $karyawan->no_bpjsktk,
      'tgl_diangkat' => $karyawan->tgl_diangkat,
      'masa_kerja' => $karyawan->masa_kerja,
      'npwp' => $karyawan->npwp,
      'jenis_kelamin' => $karyawan->jenis_kelamin,
      'agama' => $karyawan->kategori_agamas,
      'golongan_darah' => $karyawan->kategori_darahs,
      'pendidikan_terakhir' => $karyawan->pendidikan_terakhir,
      'asal_sekolah' => $karyawan->asal_sekolah,
      'tinggi_badan' => $karyawan->tinggi_badan,
      'berat_badan' => $karyawan->berat_badan,
      'no_ijazah' => $karyawan->no_ijazah,
      'tahun_lulus' => $karyawan->tahun_lulus,
      'no_str' => $karyawan->no_str,
      'masa_berlaku_str' => $karyawan->masa_berlaku_str,
      'masa_berlaku_sip' => $karyawan->masa_berlaku_sip,
      'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
      'masa_diklat' => $karyawan->masa_diklat,
      'total_durasi_internal' => $total_durasi_internal,
      'total_durasi_eksternal' => $total_durasi_eksternal,
      'status_reward_presensi' => $karyawan->status_reward_presensi,
      'status_keluarga' => $statusKeluarga,
      'bmi_value' => $karyawan->bmi_value,
      'bmi_ket' => $karyawan->bmi_ket,
      'riwayat_penyakit' => $karyawan->riwayat_penyakit,
      'created_at' => $karyawan->created_at,
      'updated_at' => $karyawan->updated_at
    ];

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
      'data' => $formattedData,
    ], Response::HTTP_OK);
  }

  public function showByDataKaryawanId($data_karyawan_id)
  {
    if (!Gate::allows('view dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);
    if (!$karyawan) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    $keluargaList = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)
      ->get();

    $statusKeluarga = false;
    foreach ($keluargaList as $keluarga) {
      if ($keluarga->status_keluarga_id == 1) {
        $statusKeluarga = true; // Butuh verifikasi
        break;
      }
    }

    if (!$statusKeluarga) {
      foreach ($keluargaList as $keluarga) {
        if ($keluarga->status_keluarga_id == 2 || $keluarga->status_keluarga_id == 3) {
          $statusKeluarga = false;
          break;
        }
      }
    }

    $role = $karyawan->users->roles->first();

    // diklat calculated
    $total_durasi_internal = PesertaDiklat::whereHas('diklats', function ($query) {
      $query->where('kategori_diklat_id', 1); // 1 = Internal
    })
      ->where('peserta', $data_karyawan_id)
      ->with('diklats')
      ->get()
      ->sum(function ($pesertaDiklat) {
        return $pesertaDiklat->diklats->durasi;
      });

    $total_durasi_eksternal = PesertaDiklat::whereHas('diklats', function ($query) {
      $query->where('kategori_diklat_id', 2); // 2 = Eksternal
    })
      ->where('peserta', $data_karyawan_id)
      ->with('diklats')
      ->get()
      ->sum(function ($pesertaDiklat) {
        return $pesertaDiklat->diklats->durasi;
      });

    // $berkasFields = [
    //   'file_ktp' => $karyawan->file_ktp ?? null,
    //   'file_kk' => $karyawan->file_kk ?? null,
    //   'file_sip' => $karyawan->file_sip ?? null,
    //   'file_bpjs_kesehatan' => $karyawan->file_bpjsksh ?? null,
    //   'file_bpjs_ketenagakerjaan' => $karyawan->file_bpjsktk ?? null,
    //   'file_ijazah' => $karyawan->file_ijazah ?? null,
    //   'file_sertifikat' => $karyawan->file_sertifikat ?? null,
    // ];

    // $baseUrl = env('STORAGE_SERVER_DOMAIN');

    // $formattedPaths = [];
    // foreach ($berkasFields as $field => $berkasId) {
    //   $berkas = Berkas::where('id', $berkasId)->first();
    //   if ($berkas) {
    //     $extension = StorageServerHelper::getExtensionFromMimeType($berkas->ext);
    //     $formattedPaths[$field] = $baseUrl . $berkas->path;
    //   } else {
    //     $formattedPaths[$field] = null;
    //   }
    // }

    // Format the karyawan data
    $formattedData = [
      'id' => $karyawan->id,
      'user' => [
        'id' => $karyawan->users->id,
        'nama' => $karyawan->users->nama,
        'username' => $karyawan->users->username,
        'email_verified_at' => $karyawan->users->email_verified_at,
        'data_karyawan_id' => $karyawan->users->data_karyawan_id,
        'foto_profil' => $karyawan->users->foto_profil,
        'data_completion_step' => $karyawan->users->data_completion_step,
        'status_aktif' => $karyawan->users->status_aktif,
        'created_at' => $karyawan->users->created_at,
        'updated_at' => $karyawan->users->updated_at
      ],
      'role' => [
        'id' => $role->id,
        'name' => $role->name,
        'deskripsi' => $role->deskripsi,
        'created_at' => $role->created_at,
        'updated_at' => $role->updated_at
      ], // role_id
      'potongan_gaji' => DB::table('pengurang_gajis')
        ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
        ->where('pengurang_gajis.data_karyawan_id', $karyawan->id)
        ->select(
          'premis.id',
          'premis.nama_premi',
          'premis.kategori_potongan_id',
          'premis.jenis_premi',
          'premis.besaran_premi',
          'premis.minimal_rate',
          'premis.maksimal_rate',
          'premis.created_at',
          'premis.updated_at'
        )
        ->get(),
      'email' => $karyawan->email,
      'nik' => $karyawan->nik,
      'no_rm' => $karyawan->no_rm,
      'no_sip' => $karyawan->no_sip,
      'masa_berlaku_sip' => $karyawan->masa_berlaku_sip,
      'no_manulife' => $karyawan->no_manulife,
      'tgl_masuk' => $karyawan->tgl_masuk,
      'unit_kerja' => $karyawan->unit_kerjas, // unit_kerja_id
      'jabatan' => $karyawan->jabatans, // jabatan_id
      'kompetensi' => $karyawan->kompetensis, // kompetensi_id
      'nik_ktp' => $karyawan->nik_ktp,
      'status_karyawan' => $karyawan->status_karyawans, // status_karyawan_id
      'tempat_lahir' => $karyawan->tempat_lahir,
      'tgl_lahir' => $karyawan->tgl_lahir,
      'kelompok_gaji' => $karyawan->kelompok_gajis, // kelompok_gaji_id
      'no_rekening' => $karyawan->no_rekening,
      'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
      'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
      'tunjangan_khusus' => $karyawan->tunjangan_khusus,
      'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
      'uang_lembur' => $karyawan->uang_lembur,
      'uang_makan' => $karyawan->uang_makan,
      'ptkp' => $karyawan->ptkps, // ptkp_id
      'tgl_keluar' => $karyawan->tgl_keluar,
      'no_kk' => $karyawan->no_kk,
      'alamat' => $karyawan->alamat,
      'gelar_depan' => $karyawan->gelar_depan,
      'gelar_belakang' => $karyawan->gelar_belakang,
      'no_hp' => $karyawan->no_hp,
      'no_bpjsksh' => $karyawan->no_bpjsksh,
      'no_bpjsktk' => $karyawan->no_bpjsktk,
      'tgl_diangkat' => $karyawan->tgl_diangkat,
      'masa_kerja' => $karyawan->masa_kerja,
      'npwp' => $karyawan->npwp,
      'jenis_kelamin' => $karyawan->jenis_kelamin,
      'agama' => $karyawan->kategori_agamas, // agama_id
      'golongan_darah' => $karyawan->kategori_darahs, // golongan_darah_id
      'pendidikan_terakhir' => $karyawan->pendidikan_terakhir,
      'asal_sekolah' => $karyawan->asal_sekolah,
      'tinggi_badan' => $karyawan->tinggi_badan,
      'berat_badan' => $karyawan->berat_badan,
      'no_ijazah' => $karyawan->no_ijazah,
      'tahun_lulus' => $karyawan->tahun_lulus,
      'no_str' => $karyawan->no_str,
      'masa_berlaku_str' => $karyawan->masa_berlaku_str,
      'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
      'masa_diklat' => $karyawan->masa_diklat,
      'total_durasi_internal' => $total_durasi_internal,
      'total_durasi_eksternal' => $total_durasi_eksternal,
      'status_reward_presensi' => $karyawan->status_reward_presensi,
      'status_keluarga' => $statusKeluarga,
      'bmi_value' => $karyawan->bmi_value,
      'bmi_ket' => $karyawan->bmi_ket,
      'riwayat_penyakit' => $karyawan->riwayat_penyakit,
      'created_at' => $karyawan->created_at,
      'updated_at' => $karyawan->updated_at
    ];

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
      'data' => $formattedData,
    ], Response::HTTP_OK);
  }

  public function update(UpdateDataKaryawanRequest $request, $id)
  {
    if (!Gate::allows('edit dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $data = $request->validated();
    $karyawan = DataKaryawan::find($id);

    if (!$karyawan) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    $user = $karyawan->users;
    $oldEmail = $karyawan->email;
    $newEmail = $data['email'];

    // Memeriksa apakah email telah berubah
    if ($oldEmail !== $newEmail) {
      if ($user->status_aktif !== 1 && $user->status_aktif !== 3) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Email tidak dapat diubah, Status akun harus dalam keadaan belum aktif atau dinonaktifkan.'), Response::HTTP_NOT_ACCEPTABLE);
      }

      $generatedPassword = RandomHelper::generatePassword();
      $karyawan->email = $newEmail;
      $user->password = Hash::make($generatedPassword);

      $karyawan->save();
      $user->save();

      // Kirim email dengan password baru
      AccountEmailJob::dispatch($newEmail, $generatedPassword, $data['nama']);
    }

    DB::beginTransaction();
    try {
      // Update nama di tabel users
      $user->nama = $data['nama'];
      $user->save();

      // Update role di tabel users
      if (isset($data['role_id'])) {
        $user->roles()->sync([$data['role_id']]);
      }

      $karyawan->update($data);

      // Update potongan gaji (premi)
      $premis = $request->input('premi_id', []);
      DB::table('pengurang_gajis')->where('data_karyawan_id', $karyawan->id)->delete(); // Hapus potongan gaji yang lama

      if (!empty($premis)) {
        $premisData = DB::table('premis')->whereIn('id', $premis)->get();
        if ($premisData->isEmpty()) {
          return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Potongan yang dipilih tidak valid.'), Response::HTTP_NOT_FOUND);
        }

        foreach ($premisData as $premi) {
          DB::table('pengurang_gajis')->insert([
            'data_karyawan_id' => $karyawan->id,
            'premi_id' => $premi->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);
        }
      }

      DB::commit();

      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => "Data karyawan '{$karyawan->users->nama}' berhasil diperbarui."
        // 'data' => $this->formatKaryawanData($karyawan),
      ], Response::HTTP_OK);
    } catch (Exception $e) {
      DB::rollBack();
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function exportKaryawan(Request $request)
  {
    if (!Gate::allows('export dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $dataKaryawan = DataKaryawan::all();
    if ($dataKaryawan->isEmpty()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
    }

    try {
      return Excel::download(new KaryawanExport($request->all()), 'karyawan-data.xls');
    } catch (\Throwable $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function importKaryawan(ImportKaryawanRequest $request)
  {
    if (!Gate::allows('import dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $file = $request->validated();

    try {
      Excel::import(new KaryawanImport, $file['karyawan_file']);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
    }

    return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan berhasil di import kedalam table.'), Response::HTTP_OK);
  }

  public function toggleStatusUser($data_karyawan_id)
  {
    if (!Gate::allows('edit dataKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);

    if (!$karyawan) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    $user = $karyawan->users;

    // Validasi pertama kali untuk memastikan data_completion_step = 0
    if ($user->data_completion_step !== 0) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, "Proses ini tidak bisa dilanjutkan karena langkah pengisian data belum mencapai tahap akhir."), Response::HTTP_NOT_ACCEPTABLE);
    }

    // Validasi data karyawan belom verif
    $dataKeluargas = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)->get();
    // dd($dataKeluargas);
    foreach ($dataKeluargas as $keluarga) {
      if ($keluarga->status_keluarga_id != 2) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, "Proses ini tidak bisa dilanjutkan karena terdapat data keluarga karyawan '{$user->nama}' yang belum diverivikasi."), Response::HTTP_NOT_ACCEPTABLE);
      }
    }

    if ($user->status_aktif === 1) {
      // Verifikasi data karyawan
      // $fieldsToCheck = [
      //   'tempat_lahir',
      //   'tgl_lahir',
      //   'no_hp',
      //   'jenis_kelamin',
      //   'nik_ktp',
      //   'nik',
      //   'no_kk',
      //   'kategori_agama_id',
      //   'kategori_darah_id',
      //   'tinggi_badan',
      //   'alamat',
      //   'tahun_lulus',
      //   'no_ijazah',
      //   'no_str',
      //   'masa_berlaku_str',
      //   'no_sip',
      //   'masa_berlaku_sip',
      //   'no_bpjsksh',
      //   'no_bpjsktk',
      //   'tgl_berakhir_pks'
      // ];

      // $nullFields = [];
      // foreach ($fieldsToCheck as $field) {
      //   if (is_null($karyawan->$field)) {
      //     $nullFields[] = $field;
      //   }
      // }

      // if (!empty($nullFields)) {
      //   // Log data yang belum terisi
      //   Log::warning("Data karyawan ID {$karyawan->id} memiliki field yang belum terisi: " . implode(', ', $nullFields));
      //   // Reset data jika ada yang null
      //   return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Data karyawan '{$karyawan->users->nama}' belum lengkap, pastikan semua data telah diisi."), Response::HTTP_BAD_REQUEST);
      // }

      // Jika semua data valid, update status_aktif menjadi 2
      $user->status_aktif = 2;
      $karyawan->verifikator_1 = Auth::id();
      // $user->data_completion_step = 0;
      $karyawan->save();
      $message = "Karyawan '{$karyawan->users->nama}' berhasil diaktifkan.";
    } elseif ($user->status_aktif === 2) {
      $user->status_aktif = 3;
      $message = "Karyawan '{$karyawan->users->nama}' berhasil dinonaktifkan.";
    } elseif ($user->status_aktif === 3) {
      $user->status_aktif = 2;
      $message = "Karyawan '{$karyawan->users->nama}' berhasil diaktifkan kembali.";
    } else {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, "Karyawan '{$karyawan->users->nama}' belum melengkapi data karyawan."), Response::HTTP_NOT_ACCEPTABLE);
    }

    $user->save();
    return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
  }

  public function downloadKaryawanTemplate()
  {
    try {
      return Excel::download(new TemplateKaryawanExport, 'template_import_karyawan.xls');
    } catch (\Throwable $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  private function calculateTrackRecordMasaKerja($tglMasuk, $tglKeluar)
  {
    if ($tglMasuk) {
      $tglMasuk = Carbon::parse($tglMasuk)->format('Y-m-d');
      $tglSekarang = Carbon::now()->format('Y-m-d');

      if ($tglKeluar) {
        $tglKeluar = Carbon::parse($tglKeluar)->format('Y-m-d');
        return Carbon::parse($tglMasuk)->diffInDays(Carbon::parse($tglKeluar));
      } else {
        return Carbon::parse($tglMasuk)->diffInDays(Carbon::parse($tglSekarang));
      }
    }
    return null;
  }

  private function createNotifikasiBerkas($berkas, $status)
  {
    // Dapatkan user terkait dengan berkas
    $user = $berkas->users;

    // Siapkan pesan notifikasi berdasarkan status
    if ($status == 'disetujui') {
      $message = "Berkas {$berkas->nama} Anda telah diverifikasi dan disetujui.";
    } elseif ($status == 'ditolak') {
      $message = "Berkas {$berkas->nama} Anda telah ditolak. Alasan: {$berkas->alasan}.";
    }

    // Buat notifikasi untuk user yang bersangkutan
    Notifikasi::create([
      'kategori_notifikasi_id' => 6, // Sesuaikan dengan kategori notifikasi yang sesuai
      'user_id' => $user->id, // Penerima notifikasi
      'message' => $message,
      'is_read' => false,
      'created_at' => Carbon::now('Asia/Jakarta'),
    ]);
  }

  private function createNotifikasiKeluarga($keluarga, $status)
  {
    // Dapatkan user terkait dengan berkas
    $karyawan = $keluarga->data_karyawans;

    if (!$karyawan || !$karyawan->users) {
      throw new \Exception("User terkait dengan karyawan '{$karyawan->users->nama}' tidak ditemukan.");
    }

    // Siapkan pesan notifikasi berdasarkan status
    if ($status == 'disetujui') {
      $message = "Keluarga karyawan '{$karyawan->users->nama}' telah diverifikasi dan disetujui.";
    } elseif ($status == 'ditolak') {
      $message = "Keluarga karyawan '{$karyawan->users->nama}' telah ditolak. Alasan: {$keluarga->alasan}.";
    }

    // Buat notifikasi untuk user yang bersangkutan
    Notifikasi::create([
      'kategori_notifikasi_id' => 6, // Sesuaikan dengan kategori notifikasi yang sesuai
      'user_id' => $karyawan->users->id, // Penerima notifikasi
      'message' => $message,
      'is_read' => false,
      'created_at' => Carbon::now('Asia/Jakarta'),
    ]);
  }
}
