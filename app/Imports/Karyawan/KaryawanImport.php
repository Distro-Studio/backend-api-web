<?php

namespace App\Imports\Karyawan;

use Carbon\Carbon;
use App\Models\Ptkp;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Kompetensi;
use App\Models\DataKaryawan;
use App\Models\KelompokGaji;
use App\Helpers\RandomHelper;
use App\Models\StatusKaryawan;
use App\Mail\SendAccoundUsersMail;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KaryawanImport implements ToModel, WithHeadingRow, WithValidation
{
  use Importable;

  // Relationship
  private $User;
  private $Role;
  private $Jabatan;
  private $UnitKerja;
  private $Kompetensi;
  private $KelompokGaji;
  private $PTKP;
  private $StatusKaryawan;
  private $KategoriAgama;
  private $GolonganDarah;

  public function __construct()
  {
    $this->User = User::select('id', 'nama')->get();
    $this->Role = Role::select('id', 'name')->get();
    $this->Jabatan = Jabatan::select('id', 'nama_jabatan')->get();
    $this->UnitKerja = UnitKerja::select('id', 'nama_unit')->get();
    $this->Kompetensi = Kompetensi::select('id', 'nama_kompetensi')->get();
    $this->KelompokGaji = KelompokGaji::select('id', 'nama_kelompok')->get();
    $this->PTKP = Ptkp::select('id', 'kode_ptkp')->get();
    $this->StatusKaryawan = StatusKaryawan::select('id', 'label')->get();
    $this->KategoriAgama = KategoriAgama::select('id', 'label')->get();
    $this->GolonganDarah = KategoriDarah::select('id', 'label')->get();
  }

  public function rules(): array
  {
    return [
      'nama' => 'required|string|max:225',
      'email' => 'nullable|email|max:225|unique:data_karyawans,email',
      'role' => 'required',
      'no_rm' => 'required',
      'no_manulife' => 'nullable',
      'tgl_masuk' => 'required',
      'tgl_berakhir_pks' => 'required',
      'unit_kerja' => 'nullable',
      'jabatan' => 'nullable',
      'kompetensi' => 'nullable',
      'status_karyawan' => 'required',
      'kelompok_gaji' => 'nullable',
      'no_rekening' => 'required|numeric',
      'tunjangan_fungsional' => 'required|numeric',
      'tunjangan_khusus' => 'required|numeric',
      'tunjangan_lainnya' => 'required|numeric',
      'uang_makan' => 'required|numeric',
      'uang_lembur' => 'nullable|numeric',
      'kode_ptkp' => 'required',

      // tambahan
      'gelar_depan' => 'nullable',
      'gelar_belakang' => 'nullable',
      'tempat_lahir' => 'nullable',
      'tgl_lahir' => 'nullable',
      'alamat' => 'nullable',
      'no_hp' => 'nullable|numeric',
      'nik_ktp' => 'nullable|numeric',
      'no_kk' => 'nullable|numeric',
      'npwp' => 'nullable|numeric',
      'jenis_kelamin' => 'nullable|in:P,L',
      'agama' => 'nullable',
      'darah' => 'nullable',
      'tinggi_badan' => 'nullable|numeric',
      'berat_badan' => 'nullable|numeric',
      'pendidikan_terakhir' => 'nullable',
      'asal_sekolah' => 'nullable',
      'tahun_lulus' => 'nullable|numeric',
      'tgl_diangkat' => 'nullable',
    ];
  }

  public function customValidationMessages()
  {
    return [
      'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
      'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
      'nama.max' => 'Nama karyawan melebihi batas maksimum panjang karakter.',
      // 'email.required' => 'Email karyawan tidak diperbolehkan kosong.',
      'email.email' => 'Alamat email yang valid wajib menggunakan @.',
      'email.max' => 'Email karyawan melebihi batas maksimum panjang karakter.',
      'email.unique' => 'Email karyawan tersebut sudah pernah digunakan.',
      'role.required' => 'Silahkan masukkan nama role karyawan terlebih dahulu.',
      'no_rm.required' => 'Nomor rekam medis karyawan tidak diperbolehkan kosong.',
      'no_manulife.string' => 'Nomor manulife karyawan tidak diperbolehkan kosong.',
      'tgl_masuk.required' => 'Tanggal masuk karyawan tidak diperbolehkan kosong.',
      'tgl_berakhir_pks.required' => 'Tanggal berakhir PKS tidak diperbolehkan kosong.',
      'unit_kerja.required' => 'Silahkan masukkan nama unit kerja karyawan terlebih dahulu.',
      'jabatan.required' => 'Silahkan masukkan nama jabatan karyawan terlebih dahulu.',
      'kompetensi.required' => 'Silahkan masukkan nama kompetensi karyawan terlebih dahulu.',
      'status_karyawan.required' => 'Status karyawan tidak diperbolehkan kosong.',

      'kelompok_gaji.required' => 'Silahkan masukkan kelompok gaji karyawan terlebih dahulu.',
      'no_rekening.required' => 'Nomor rekening karyawan tidak diperbolehkan kosong.',
      'no_rekening.numeric' => 'Nomor rekening karyawan tidak diperbolehkan mengandung selain angka.',
      'tunjangan_fungsional.required' => 'Tunjangan fungsional karyawan tidak diperbolehkan kosong.',
      'tunjangan_fungsional.numeric' => 'Tunjangan fungsional karyawan tidak diperbolehkan mengandung selain angka.',
      'tunjangan_khusus.required' => 'Tunjangan khusus karyawan tidak diperbolehkan kosong.',
      'tunjangan_khusus.numeric' => 'Tunjangan khusus karyawan tidak diperbolehkan mengandung selain angka.',
      'tunjangan_lainnya.required' => 'Tunjangan karyawan lainya tidak diperbolehkan kosong.',
      'tunjangan_lainnya.numeric' => 'Tunjangan karyawan lainya tidak diperbolehkan mengandung selain angka.',
      'uang_makan.required' => 'Uang makan karyawan tidak diperbolehkan kosong.',
      'uang_makan.numeric' => 'Uang makan karyawan tidak diperbolehkan mengandung selain angka.',
      'uang_lembur.required' => 'Uang lembur karyawan tidak diperbolehkan kosong.',
      'uang_lembur.numeric' => 'Uang lembur karyawan tidak diperbolehkan mengandung selain angka.',
      'kode_ptkp.required' => 'Silahkan masukkan PTKP karyawan terlebih dahulu.',

      'no_hp.numeric' => 'Nomor HP karyawan tidak diperbolehkan mengandung selain angka.',
      'nik_ktp.numeric' => 'NIK KTP karyawan tidak diperbolehkan mengandung selain angka.',
      'no_kk.numeric' => 'Nomor KK karyawan tidak diperbolehkan mengandung selain angka.',
      'npwp.numeric' => 'Nomor NPWP karyawan tidak diperbolehkan mengandung selain angka.',
      'jenis_kelamin.in' => 'Jenis kelamin karyawan tidak diperbolehkan mengandung nilai selain P dan L.',
      'tinggi_badan.numeric' => 'Tinggi badan karyawan tidak diperbolehkan mengandung selain angka.',
      'berat_badan.numeric' => 'Berat badan karyawan tidak diperbolehkan mengandung selain angka.',
      'tahun_lulus.numeric' => 'Tahun lulus pendidikan karyawan tidak diperbolehkan mengandung selain angka.',
    ];
  }

  public function model(array $row)
  {
    $existingUser = $this->User->where('nama', $row['nama'])->first();
    if ($existingUser) {
      $user_id = $existingUser->id;
    } else {
      // $password = RandomHelper::generatePassword();
      $username = RandomHelper::generateUsername($row['nama']);

      // Find role ID
      $role = $this->Role->where('name', $row['role'])->first();
      if (!$role) {
        throw new \Exception("Role '" . $row['role'] . "' tidak ditemukan.");
      }

      // Create new user
      $userData = [
        'nama' => $row['nama'],
        'username' => $username,
        // 'status_aktif' => 1,
        'status_aktif' => 2,
        'data_completion_step' => 0,
        'role_id' => $role->id,
        // 'password' => Hash::make($password),
        'password' => Hash::make('1234'),
      ];
      $createUser = User::create($userData);
      $createUser->assignRole($role->name);

      // Mail::to($row['email'])->send(new SendAccoundUsersMail($row['email'], $password, $row['nama']));
      // AccountEmailJob::dispatch($row['email'], $password, $row['nama']);

      $user_id = $createUser->id;
    }

    if (!empty($row['jabatan'])) {
      $jabatan = $this->Jabatan->where('nama_jabatan', $row['jabatan'])->first();
      if (!$jabatan) {
        throw new \Exception("Jabatan '" . $row['jabatan'] . "' tidak ditemukan.");
      }
    } else {
      $jabatan = null;
    }

    if (!empty($row['unit_kerja'])) {
      $unit_kerja = $this->UnitKerja->where('nama_unit', $row['unit_kerja'])->first();
      if (!$unit_kerja) {
        throw new \Exception("Unit kerja '" . $row['unit_kerja'] . "' tidak ditemukan.");
      }
    } else {
      $unit_kerja = null;
    }

    if (!empty($row['kompetensi'])) {
      $kompetensi = $this->Kompetensi->where('nama_kompetensi', $row['kompetensi'])->first();
      if (!$kompetensi) {
        throw new \Exception("Kompetensi '" . $row['kompetensi'] . "' tidak ditemukan.");
      }
    } else {
      $kompetensi = null;
    }

    if (!empty($row['kelompok_gaji'])) {
      $kelompok_gaji = $this->KelompokGaji->where('nama_kelompok', $row['kelompok_gaji'])->first();
      if (!$kelompok_gaji) {
        throw new \Exception("Kelompok gaji '" . $row['kelompok_gaji'] . "' tidak ditemukan.");
      }
    } else {
      $kelompok_gaji = null;
    }

    if (!empty($row['kode_ptkp'])) {
      $ptkp = $this->PTKP->where('kode_ptkp', $row['kode_ptkp'])->first();
      if (!$ptkp) {
        throw new \Exception("PTKP '" . $row['kode_ptkp'] . "' tidak ditemukan.");
      }
    } else {
      $ptkp = null;
    }

    if (!empty($row['status_karyawan'])) {
      $status_karyawan = $this->StatusKaryawan->where('label', $row['status_karyawan'])->first();
      if (!$status_karyawan) {
        throw new \Exception("Status karyawan '" . $row['status_karyawan'] . "' tidak ditemukan.");
      }
    } else {
      $status_karyawan = null;
    }

    if (!empty($row['agama'])) {
      $agama = $this->KategoriAgama->where('label', $row['agama'])->first();
      if (!$agama) {
        throw new \Exception("Agama '" . $row['agama'] . "' tidak ditemukan.");
      }
    } else {
      $agama = null;
    }

    if (!empty($row['darah'])) {
      $darah = $this->GolonganDarah->where('label', $row['darah'])->first();
      if (!$darah) {
        throw new \Exception("Golongan darah '" . $row['darah'] . "' tidak ditemukan.");
      }
    } else {
      $darah = null;
    }

    $tgl_masuk = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_masuk']);
    $tgl_berakhir_pks = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_berakhir_pks']);
    $tgl_lahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_lahir']);
    $tgl_diangkat = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_diangkat']);

    $tgl_masuk_formatted = Carbon::instance($tgl_masuk)->format('d-m-Y');
    $tgl_berakhir_pks_formatted = Carbon::instance($tgl_berakhir_pks)->format('d-m-Y');
    $tgl_lahir_formatted = Carbon::instance($tgl_lahir)->format('d-m-Y');
    $tgl_diangkat_formatted = Carbon::instance($tgl_diangkat)->format('d-m-Y');

    // Konversi jenis kelamin
    $jenis_kelamin = null;
    if ($row['jenis_kelamin'] === 'L') {
      $jenis_kelamin = 1;
    } elseif ($row['jenis_kelamin'] === 'P') {
      $jenis_kelamin = 0;
    }

    $tahun_lulus = null;
    if (!empty($row['tahun_lulus']) && is_numeric(trim($row['tahun_lulus']))) {
      $tahun_lulus = $row['tahun_lulus'];
    }

    $dataKaryawan = DataKaryawan::create([
      'user_id' => $user_id,
      'email' => $row['email'],
      'no_rm' => $row['no_rm'],
      'no_manulife' => $row['no_manulife'],
      'tgl_masuk' => $tgl_masuk_formatted,
      'tgl_berakhir_pks' => $tgl_berakhir_pks_formatted,
      // 'tgl_masuk' => $row['tgl_masuk'],
      // 'tgl_berakhir_pks' => $row['tgl_berakhir_pks'],
      'nik' => $row['nik'],
      'unit_kerja_id' => $unit_kerja ? $unit_kerja->id : null,
      'jabatan_id' => $jabatan ? $jabatan->id : null,
      'kompetensi_id' => $kompetensi ? $kompetensi->id : null,
      'status_karyawan_id' => $status_karyawan ? $status_karyawan->id : null,
      'kelompok_gaji_id' => $kelompok_gaji ? $kelompok_gaji->id : null,
      'no_rekening' => $row['no_rekening'],
      'tunjangan_fungsional' => $row['tunjangan_fungsional'],
      'tunjangan_khusus' => $row['tunjangan_khusus'],
      'tunjangan_lainnya' => $row['tunjangan_lainnya'],
      'uang_makan' => $row['uang_makan'],
      'uang_lembur' => $row['uang_lembur'],
      'ptkp_id' => $ptkp ? $ptkp->id : null,

      // Tambahan
      'gelar_depan' => $row['gelar_depan'],
      'gelar_belakang' => $row['gelar_belakang'],
      'tempat_lahir' => $row['tempat_lahir'],
      'tgl_lahir' => $tgl_lahir_formatted,
      'alamat' => $row['alamat'],
      'no_hp' => $row['no_hp'],
      'nik_ktp' => $row['nik_ktp'],
      'no_kk' => $row['no_kk'],
      'npwp' => $row['npwp'],
      'jenis_kelamin' => $jenis_kelamin,
      'kategori_agama_id' => $agama ? $agama->id : null,
      'kategori_darah_id' => $darah ? $darah->id : null,
      'tinggi_badan' => $row['tinggi_badan'],
      'berat_badan' => $row['berat_badan'],
      'pendidikan_terakhir' => $row['pendidikan_terakhir'],
      'asal_sekolah' => $row['asal_sekolah'],
      'tahun_lulus' => $tahun_lulus,
      'tgl_diangkat' => $tgl_diangkat_formatted,
    ]);

    $createUser->update(['data_karyawan_id' => $dataKaryawan->id]);
    return $dataKaryawan;
  }
}
