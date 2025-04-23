<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Exception;
use Carbon\Carbon;
use App\Models\Ptkp;
use App\Models\User;
use App\Models\Premi;
use App\Models\Berkas;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Kompetensi;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\KelompokGaji;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use App\Models\PesertaDiklat;
use Illuminate\Http\Response;
use App\Models\StatusKaryawan;
use App\Models\KategoriPendidikan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Helpers\CalculateBMIHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\KategoriTagihanPotongan;
use Illuminate\Support\Facades\Storage;
use App\Exports\Karyawan\KaryawanExport;
use App\Helpers\LogHelper;
use App\Imports\Karyawan\KaryawanImport;
use App\Http\Requests\StoreDataKaryawanRequest;
use App\Jobs\EmailNotification\AccountEmailJob;
use App\Http\Requests\UpdateDataKaryawanRequest;
use App\Http\Requests\Excel_Import\ImportKaryawanRequest;
use App\Http\Resources\Dashboard\Karyawan\KaryawanResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Mail\SendUpdateAccoundUsersMail;
use Illuminate\Support\Facades\Mail;

class DataKaryawanController extends Controller
{
  public function resetCredentials(Request $request)
  {
    try {
      $loggedInUser = Auth::user();
      if ($loggedInUser->id !== 1 && $loggedInUser->nama !== 'Super Admin') {
        return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
      }

      // 1. Get user_id dari request
      $userId = $request->input('user_id');
      $user = User::find($userId);
      if (!$user) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna akun tidak ditemukan.'), Response::HTTP_NOT_FOUND);
      }

      // 2. Pengecualian 'Super Admin'
      if ($user->id == 1 || $user->nama === 'Super Admin') {
        return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Tidak diperbolehkan mereset password untuk akun Super Admin.'), Response::HTTP_FORBIDDEN);
      }

      // 3. Reset password
      $newPassword = $request->input('password', '1234');
      $hashedPassword = Hash::make($newPassword);
      $user->password = $hashedPassword;
      $user->save();

      $user->tokens()->delete();

      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => "Berhasil melakukan reset password untuk karyawan '{$user->nama}'.",
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function resetCredentials: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataUserNonShift()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataUserNonShift: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataUserShift()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataUserShift: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataUser()
  {
    try {
      $users = User::where('nama', '!=', 'Super Admin')->where('status_aktif', 2)->get();
      if ($users->isEmpty()) {
        return response()->json([
          'status' => Response::HTTP_NOT_FOUND,
          'message' => 'Data karyawan tidak ditemukan.',
        ], Response::HTTP_NOT_FOUND);
      }

      $formattedData = $users->map(function ($user) {
        $unitKerja = $user->data_karyawans->unit_kerjas ?? null;
        $statusKaryawan = $user->data_karyawans->status_karyawans ?? null;

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
          'status_karyawan' => $statusKaryawan ? [
            'id' => $statusKaryawan->id,
            'label' => $statusKaryawan->label,
          ] : null
        ];
      });

      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => 'Retrieving all user for dropdown',
        'data' => $formattedData
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataUser: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataUnitKerja()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataUnitKerja: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataJabatan()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataJabatan: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataStatusKaryawan()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataStatusKaryawan: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataKompetensi()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataKompetensi: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataRole()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataRole: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataKelompokGaji()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataKelompokGaji: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataPTKP()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataPTKP: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataPremi()
  {
    try {
      $premi = Premi::withoutTrashed()->get();
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataPremi: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataKaryawan()
  {
    try {
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataKaryawan: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllPendidikan()
  {
    try {
      $kategori_pendidikan = KategoriPendidikan::all();
      if ($kategori_pendidikan->isEmpty()) {
        return response()->json([
          'status' => Response::HTTP_NOT_FOUND,
          'message' => 'Data pendidikan tidak ditemukan.',
        ], Response::HTTP_NOT_FOUND);
      }

      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => 'Retrieving all pendidikan for dropdown',
        'data' => $kategori_pendidikan
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllPendidikan: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getAllDataTagihanPotongan()
  {
    try {
      $kategori_tagihan = KategoriTagihanPotongan::all();
      if ($kategori_tagihan->isEmpty()) {
        return response()->json([
          'status' => Response::HTTP_NOT_FOUND,
          'message' => 'Data tagihan potongan tidak ditemukan.',
        ], Response::HTTP_NOT_FOUND);
      }

      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => 'Retrieving all tagihan potongan for dropdown',
        'data' => $kategori_tagihan
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function getAllDataTagihanPotongan: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function index(Request $request)
  {
    try {
      if (!Gate::allows('view dataKaryawan')) {
        return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
      }

      $loggedInUser = auth()->user();
      $isSuperAdmin = $loggedInUser->id == 1 || $loggedInUser->nama == 'Super Admin';

      // Per page
      $limit = $request->input('limit', 10);

      $karyawan = DataKaryawan::query()->where('id', '!=', 1)->orderBy('nik', 'asc');

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
        $currentDate = Carbon::now('Asia/Jakarta');
        if (is_array($masaKerja)) {
          $karyawan->where(function ($query) use ($masaKerja, $currentDate) {
            foreach ($masaKerja as $masa) {
              $bulan = $masa * 12;
              $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
            }
          });
        } else {
          $bulan = $masaKerja * 12;
          $karyawan->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
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
          $karyawan->whereIn('tgl_masuk', $tglMasuk);
        } else {
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

      if (isset($filters['jenis_kompetensi'])) {
        $jenisKaryawan = $filters['jenis_kompetensi'];
        $karyawan->whereHas('kompetensis', function ($query) use ($jenisKaryawan) {
          if (is_array($jenisKaryawan)) {
            $query->whereIn('jenis_kompetensi', $jenisKaryawan);
          } else {
            $query->where('jenis_kompetensi', '=', $jenisKaryawan);
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

      $formattedData = $dataKaryawan->map(function ($karyawan) use ($isSuperAdmin) {
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
            'tgl_dinonaktifkan' => $karyawan->users->tgl_dinonaktifkan,
            'alasan' => $karyawan->users->alasan,
            'created_at' => $karyawan->users->created_at,
            'updated_at' => $karyawan->users->updated_at
          ],
          'role' => $isSuperAdmin ? [
            'id' => $role->id,
            'name' => $role->name,
            'deskripsi' => $role->deskripsi,
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at
          ] : null,
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
          'pendidikan_terakhir' => $karyawan->kategori_pendidikans, // pendidikan_terakhir_id
          'asal_sekolah' => $karyawan->asal_sekolah,
          'tinggi_badan' => $karyawan->tinggi_badan,
          'berat_badan' => $karyawan->berat_badan,
          'no_ijazah' => $karyawan->no_ijazah,
          'tahun_lulus' => $karyawan->tahun_lulus,
          'no_str' => $karyawan->no_str,
          'created_str' => $karyawan->created_str,
          'masa_berlaku_str' => $karyawan->masa_berlaku_str,
          'no_sip' => $karyawan->no_sip,
          'created_sip' => $karyawan->created_sip,
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function index: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function store(StoreDataKaryawanRequest $request)
  {
    try {
      if (!Gate::allows('create dataKaryawan')) {
        return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
      }

      $data = $request->validated();
      $requestedRoleId = $request->input('role_id');
      $premis = $request->input('premi_id', []); // Mengambil daftar premi yang dipilih

      DB::beginTransaction();
      $generatedUsername = RandomHelper::generateUsername($data['nama']);
      if (!empty($data['email'])) {
        $generatedPassword = RandomHelper::generatePassword();
        $passwordHash = Hash::make($generatedPassword);
      } else {
        $passwordHash = Hash::make('1234');
      }

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
          'tunjangan_jabatan' => $data['tunjangan_jabatan'],
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

        LogHelper::logAction('Karyawan', 'create', $createDataKaryawan->id);

        if (!empty($data['email'])) {
          AccountEmailJob::dispatch($data['email'], $generatedPassword, $data['nama']);
        }

        return response()->json(new KaryawanResource(Response::HTTP_OK, "Data karyawan '{$createDataKaryawan->users->nama}' berhasil dibuat.", $createDataKaryawan), Response::HTTP_OK);
      } catch (\Throwable $th) {
        DB::rollBack();
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Maaf sepertinya pembuatan data karyawan bermasalah, Error: ' . $th->getMessage()), Response::HTTP_BAD_REQUEST);
      }
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function store: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function showByUserId($user_id)
  {
    try {
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

      $berkasList = Berkas::where('user_id', $user_id)->get();

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

      $statusBerkas = false;
      foreach ($berkasList as $berkas) {
        if ($berkas->status_berkas_id == 1) {
          $statusBerkas = true; // Butuh verifikasi
          break;
        }
      }

      if (!$statusBerkas) {
        foreach ($berkasList as $berkas) {
          if ($berkas->status_berkas_id == 2 || $berkas->status_berkas_id == 3) {
            $statusBerkas = false;
            break;
          }
        }
      }

      $role = $karyawan->users->roles->first();

      $loggedInUser = auth()->user();

      // Kondisi kalo yg login super admin aja
      $isSuperAdmin = $loggedInUser->id == 1 || $loggedInUser->nama == 'Super Admin';

      // diklat calculated
      $currentYear = now('Asia/Jakarta')->year;
      $total_durasi_internal = PesertaDiklat::where('peserta', $karyawan->users->id)
        ->whereHas('diklats', function ($query) use ($currentYear) {
          $query->where('kategori_diklat_id', 1) // Internal
            ->whereRaw("YEAR(STR_TO_DATE(tgl_mulai, '%d-%m-%Y')) = ?", [$currentYear]);
        })
        ->with('diklats')
        ->get()
        ->sum(fn($pd) => $pd->diklats?->durasi ?? 0);

      $total_durasi_eksternal = PesertaDiklat::where('peserta', $karyawan->users->id)
        ->whereHas('diklats', function ($query) use ($currentYear) {
          $query->where('kategori_diklat_id', 2) // Eksternal
            ->whereRaw("YEAR(STR_TO_DATE(tgl_mulai, '%d-%m-%Y')) = ?", [$currentYear]);
        })
        ->with('diklats')
        ->get()
        ->sum(fn($pd) => $pd->diklats?->durasi ?? 0);

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
          'tgl_dinonaktifkan' => $karyawan->users->tgl_dinonaktifkan,
          'alasan' => $karyawan->users->alasan,
          'created_at' => $karyawan->users->created_at,
          'updated_at' => $karyawan->users->updated_at
        ],
        'role' => $role ? [
          'id' => $role->id,
          'name' => $role->name,
          'deskripsi' => $role->deskripsi,
          'created_at' => $role->created_at,
          'updated_at' => $role->updated_at
        ] : null,
        'nik' => $karyawan->nik,
        'email' => $karyawan->email,
        'no_rm' => $karyawan->no_rm,
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
        'pendidikan_terakhir' => $karyawan->kategori_pendidikans,
        'asal_sekolah' => $karyawan->asal_sekolah,
        'tinggi_badan' => $karyawan->tinggi_badan,
        'berat_badan' => $karyawan->berat_badan,
        'no_ijazah' => $karyawan->no_ijazah,
        'tahun_lulus' => $karyawan->tahun_lulus,
        'no_str' => $karyawan->no_str,
        'created_str' => $karyawan->created_str,
        'masa_berlaku_str' => $karyawan->masa_berlaku_str,
        'no_sip' => $karyawan->no_sip,
        'created_sip' => $karyawan->created_sip,
        'masa_berlaku_sip' => $karyawan->masa_berlaku_sip,
        'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
        'masa_diklat' => $karyawan->masa_diklat,
        'total_durasi_internal' => $total_durasi_internal,
        'total_durasi_eksternal' => $total_durasi_eksternal,
        'status_reward_presensi' => $karyawan->status_reward_presensi,
        'status_keluarga' => $statusKeluarga,
        'status_berkas' => $statusBerkas,
        'bmi_value' => $karyawan->bmi_value,
        'bmi_ket' => $karyawan->bmi_ket,
        'riwayat_penyakit' => $karyawan->riwayat_penyakit,
        'created_at' => $karyawan->created_at,
        'updated_at' => $karyawan->updated_at
      ];

      // Data sensitif hanya ditampilkan jika Super Admin
      if ($isSuperAdmin) {
        $formattedData['potongan_gaji'] = DB::table('pengurang_gajis')
          ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
          ->where('pengurang_gajis.data_karyawan_id', $karyawan->id)
          ->whereNull('pengurang_gajis.deleted_at')
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
          ->get();

        $formattedData['tunjangan_jabatan'] = $karyawan->tunjangan_jabatan;
        $formattedData['tunjangan_fungsional'] = $karyawan->tunjangan_fungsional;
        $formattedData['tunjangan_khusus'] = $karyawan->tunjangan_khusus;
        $formattedData['tunjangan_lainnya'] = $karyawan->tunjangan_lainnya;
        $formattedData['uang_lembur'] = $karyawan->uang_lembur;
        $formattedData['uang_makan'] = $karyawan->uang_makan;
      }

      if (!$isSuperAdmin && isset($formattedData['kompetensi'])) {
        if (is_array($formattedData['kompetensi'])) {
          unset($formattedData['kompetensi']['nilai_bor']);
        } elseif (is_object($formattedData['kompetensi'])) {
          unset($formattedData['kompetensi']->nilai_bor);
        }
      }

      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => "Detail karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
        'data' => $formattedData,
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function showByUserId: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.' . $e->getMessage(),
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function showByDataKaryawanId($data_karyawan_id)
  {
    try {
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

      $berkasList = Berkas::where('user_id', $karyawan->user_id)
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

      $statusBerkas = false;
      foreach ($berkasList as $berkas) {
        if ($berkas->status_berkas_id == 1) {
          $statusBerkas = true; // Butuh verifikasi
          break;
        }
      }

      if (!$statusBerkas) {
        foreach ($berkasList as $berkas) {
          if ($berkas->status_berkas_id == 2 || $berkas->status_berkas_id == 3) {
            $statusBerkas = false;
            break;
          }
        }
      }

      $role = $karyawan->users->roles->first();

      $loggedInUser = auth()->user();
      $isSuperAdmin = $loggedInUser->id == 1 || $loggedInUser->nama == 'Super Admin';

      // diklat calculated
      $currentYear = now('Asia/Jakarta')->year;
      $total_durasi_internal = PesertaDiklat::where('peserta', $karyawan->users->id)
        ->whereHas('diklats', function ($query) use ($currentYear) {
          $query->where('kategori_diklat_id', 1) // Internal
            ->whereRaw("EXTRACT(YEAR FROM TO_DATE(tgl_mulai, 'DD-MM-YYYY')) = ?", [$currentYear]);
        })
        ->with('diklats')
        ->get()
        ->sum(fn($pd) => $pd->diklats?->durasi ?? 0);

      $total_durasi_eksternal = PesertaDiklat::where('peserta', $karyawan->users->id)
        ->whereHas('diklats', function ($query) use ($currentYear) {
          $query->where('kategori_diklat_id', 2) // Eksternal
            ->whereRaw("EXTRACT(YEAR FROM TO_DATE(tgl_mulai, 'DD-MM-YYYY')) = ?", [$currentYear]);
        })
        ->with('diklats')
        ->get()
        ->sum(fn($pd) => $pd->diklats?->durasi ?? 0);

      // $berkasFields = [
      //   'file_ktp' => $karyawan->file_ktp ?? null,
      //   'file_kk' => $karyawan->file_kk ?? null,
      //   'file_sip' => $karyawan->file_sip ?? null,
      //   'file_bpjs_kesehatan' => $karyawan->file_bpjsksh ?? null,
      //   'file_bpjs_ketenagakerjaan' => $karyawan->file_bpjsktk ?? null,
      //   'file_ijazah' => $karyawan->file_ijazah ?? null,
      //   'file_sertifikat' => $karyawan->file_sertifikat ?? null,
      // ];

      // $baseUrl = "https://192.168.0.20/RskiSistem24/file-storage/public";

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
          'tgl_dinonaktifkan' => $karyawan->users->tgl_dinonaktifkan,
          'alasan' => $karyawan->users->alasan,
          'created_at' => $karyawan->users->created_at,
          'updated_at' => $karyawan->users->updated_at
        ],
        'role' => $role ? [
          'id' => $role->id,
          'name' => $role->name,
          'deskripsi' => $role->deskripsi,
          'created_at' => $role->created_at,
          'updated_at' => $role->updated_at
        ] : null,
        'potongan_gaji' => DB::table('pengurang_gajis')
          ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
          ->where('pengurang_gajis.data_karyawan_id', $karyawan->id)
          ->whereNull('pengurang_gajis.deleted_at')
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
        'pendidikan_terakhir' => $karyawan->kategori_pendidikans,
        'asal_sekolah' => $karyawan->asal_sekolah,
        'tinggi_badan' => $karyawan->tinggi_badan,
        'berat_badan' => $karyawan->berat_badan,
        'no_ijazah' => $karyawan->no_ijazah,
        'tahun_lulus' => $karyawan->tahun_lulus,
        'no_str' => $karyawan->no_str,
        'created_str' => $karyawan->created_str,
        'masa_berlaku_str' => $karyawan->masa_berlaku_str,
        'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
        'no_sip' => $karyawan->no_sip,
        'created_sip' => $karyawan->created_sip,
        'masa_berlaku_sip' => $karyawan->masa_berlaku_sip,
        'masa_diklat' => $karyawan->masa_diklat,
        'total_durasi_internal' => $total_durasi_internal,
        'total_durasi_eksternal' => $total_durasi_eksternal,
        'status_reward_presensi' => $karyawan->status_reward_presensi,
        'status_keluarga' => $statusKeluarga,
        'status_berkas' => $statusBerkas,
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
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function showByDataKaryawanId: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.' . $e->getMessage(),
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function update(UpdateDataKaryawanRequest $request, $id)
  {
    try {
      if (!Gate::allows('edit dataKaryawan')) {
        return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
      }

      $data = $request->validated();
      $karyawan = DataKaryawan::find($id);

      if (!$karyawan) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
      }

      DB::beginTransaction();
      $user = $karyawan->users;
      $oldEmail = $karyawan->email;
      $newEmail = $data['email'];

      // Memeriksa apakah email telah berubah
      if ($oldEmail !== $newEmail) {
        $generatedPassword = RandomHelper::generatePassword();
        $karyawan->email = $newEmail;
        $user->password = Hash::make($generatedPassword);

        $karyawan->save();
        $user->save();

        // Hapus semua token user terkait, supaya otomatis logout
        $user->tokens()->delete();

        // Kirim email dengan password baru
        Mail::to($newEmail)->send(new SendUpdateAccoundUsersMail($newEmail, $generatedPassword, $data['nama']));
      }

      try {
        // Update nama di tabel users
        $user->nama = $data['nama'];
        $user->save();

        // Update role di tabel users
        if (isset($data['role_id'])) {
          $user->roles()->sync([$data['role_id']]);
        }

        $karyawan->update($data);

        // Itung BMI berat dan tinggi badan kalo ada
        if (!empty($data['berat_badan']) && !empty($data['tinggi_badan'])) {
          $bmi_calculated = CalculateBMIHelper::calculateBMI($data['berat_badan'], $data['tinggi_badan']);
          $karyawan->bmi_value = $bmi_calculated['bmi_value'];
          $karyawan->bmi_ket = $bmi_calculated['bmi_ket'];
          $karyawan->save();
        }

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

        LogHelper::logAction('Karyawan', 'update', $karyawan->id);

        return response()->json([
          'status' => Response::HTTP_OK,
          'message' => "Data karyawan '{$karyawan->users->nama}' berhasil diperbarui."
        ], Response::HTTP_OK);
      } catch (Exception $e) {
        DB::rollBack();
        return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function update: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function exportKaryawan(Request $request)
  {
    try {
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
        return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function exportKaryawan: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function importKaryawan(ImportKaryawanRequest $request)
  {
    try {
      if (!Gate::allows('import dataKaryawan')) {
        return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
      }

      $file = $request->validated();

      try {
        Excel::import(new KaryawanImport, $file['karyawan_file']);
      } catch (\Exception $e) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
      }

      LogHelper::logAction('Karyawan', 'import', 0);

      return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan berhasil di import kedalam table.'), Response::HTTP_OK);
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function importKaryawan: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function toggleStatusUser(Request $request, $data_karyawan_id)
  {
    try {
      if (!Gate::allows('edit dataKaryawan')) {
        return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
      }

      $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);

      if (!$karyawan) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
      }

      $user = $karyawan->users;

      $authUser = Auth::user();

      // Validasi hanya jika yang login bukan Super Admin
      if ($authUser->role_id !== 1 && $user->data_completion_step !== 0) {
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

      // Jika semua data valid, update status_aktif menjadi 2
      if ($user->status_aktif === 1) {
        $user->status_aktif = 2;
        $karyawan->verifikator_1 = Auth::id();
        if ($user->data_completion_step !== 0) {
          $user->data_completion_step = 0;
        }
        $karyawan->save();
        $message = "Karyawan '{$karyawan->users->nama}' berhasil diaktifkan.";

        LogHelper::logAction('Karyawan', 'update', $karyawan->id);
      } elseif ($user->status_aktif === 2) {
        $user->status_aktif = 3;
        $user->tgl_dinonaktifkan = Carbon::now('Asia/Jakarta');
        $user->alasan = $request->input('alasan');
        $message = "Karyawan '{$karyawan->users->nama}' berhasil dinonaktifkan.";

        LogHelper::logAction('Karyawan', 'update', $karyawan->id);
      } elseif ($user->status_aktif === 3) {
        $user->status_aktif = 2;
        $user->tgl_dinonaktifkan = null;
        $user->alasan = null;
        $message = "Karyawan '{$karyawan->users->nama}' berhasil diaktifkan kembali.";

        LogHelper::logAction('Karyawan', 'update', $karyawan->id);
      } else {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, "Karyawan '{$karyawan->users->nama}' belum melengkapi data karyawan."), Response::HTTP_NOT_ACCEPTABLE);
      }

      $user->save();
      return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    } catch (\Exception $e) {
      Log::error('| Karyawan | - Error function toggleStatusUser: ' . $e->getMessage());
      return response()->json([
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function downloadKaryawanTemplate()
  {
    try {
      $filePath = 'templates/template_import_karyawan.xls';

      if (!Storage::exists($filePath)) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'File template tidak ditemukan.'), Response::HTTP_NOT_FOUND);
      }

      return Storage::download($filePath, 'template_import_karyawan.xls');
    } catch (\Throwable $e) {
      Log::error('| Karyawan | - Error saat download template karyawan: ' . $e->getMessage() . ' Line: ' . $e->getLine());
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error.'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
