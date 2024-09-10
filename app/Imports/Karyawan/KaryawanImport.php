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
      'no_rekening.numeric' => 'Nomor rekening karyawan tidak diperbolehkan mengandung huruf.',
      'tunjangan_fungsional.required' => 'Tunjangan fungsional karyawan tidak diperbolehkan kosong.',
      'tunjangan_fungsional.numeric' => 'Tunjangan fungsional karyawan tidak diperbolehkan mengandung huruf.',
      'tunjangan_khusus.required' => 'Tunjangan khusus karyawan tidak diperbolehkan kosong.',
      'tunjangan_khusus.numeric' => 'Tunjangan khusus karyawan tidak diperbolehkan mengandung huruf.',
      'tunjangan_lainnya.required' => 'Tunjangan karyawan lainya tidak diperbolehkan kosong.',
      'tunjangan_lainnya.numeric' => 'Tunjangan karyawan lainya tidak diperbolehkan mengandung huruf.',
      'uang_makan.required' => 'Uang makan karyawan tidak diperbolehkan kosong.',
      'uang_makan.numeric' => 'Uang makan karyawan tidak diperbolehkan mengandung huruf.',
      'uang_lembur.required' => 'Uang lembur karyawan tidak diperbolehkan kosong.',
      'uang_lembur.numeric' => 'Uang lembur karyawan tidak diperbolehkan mengandung huruf.',
      'kode_ptkp.required' => 'Silahkan masukkan PTKP karyawan terlebih dahulu.',
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
        'status_aktif' => 1,
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

    $jabatan = $this->Jabatan->where('nama_jabatan', $row['jabatan'])->first();
    if (!$jabatan) {
      throw new \Exception("Jabatan '" . $row['jabatan'] . "' tidak ditemukan.");
    }

    $unit_kerja = $this->UnitKerja->where('nama_unit', $row['unit_kerja'])->first();
    if (!$unit_kerja) {
      throw new \Exception("Unit kerja '" . $row['unit_kerja'] . "' tidak ditemukan.");
    }

    $kompetensi = $this->Kompetensi->where('nama_kompetensi', $row['kompetensi'])->first();
    if (!$kompetensi) {
      throw new \Exception("Kompetensi '" . $row['kompetensi'] . "' tidak ditemukan.");
    }

    $kelompok_gaji = $this->KelompokGaji->where('nama_kelompok', $row['kelompok_gaji'])->first();
    if (!$kelompok_gaji) {
      throw new \Exception("Kelompok gaji '" . $row['kelompok_gaji'], "' tidak ditemukan.");
    }

    $ptkp = $this->PTKP->where('kode_ptkp', $row['kode_ptkp'])->first();
    if (!$ptkp) {
      throw new \Exception("PTKP '" . $row['kode_ptkp'], "' tidak ditemukan.");
    }

    $status_karyawan = $this->StatusKaryawan->where('label', $row['status_karyawan'])->first();
    if (!$status_karyawan) {
      throw new \Exception("Status karyawan '" . $row['status_karyawan'], "' tidak ditemukan.");
    }

    $tgl_masuk = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_masuk']);
    $tgl_berakhir_pks = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_berakhir_pks']);

    $tgl_masuk_formatted = Carbon::instance($tgl_masuk)->format('d-m-Y');
    $tgl_berakhir_pks_formatted = Carbon::instance($tgl_berakhir_pks)->format('d-m-Y');

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
      'unit_kerja_id' => $unit_kerja->id,
      'jabatan_id' => $jabatan->id,
      'kompetensi_id' => $kompetensi->id,
      'status_karyawan_id' => $status_karyawan->id,
      'kelompok_gaji_id' => $kelompok_gaji->id,
      'no_rekening' => $row['no_rekening'],
      'tunjangan_fungsional' => $row['tunjangan_fungsional'],
      'tunjangan_khusus' => $row['tunjangan_khusus'],
      'tunjangan_lainnya' => $row['tunjangan_lainnya'],
      'uang_makan' => $row['uang_makan'],
      'uang_lembur' => $row['uang_lembur'],
      'ptkp_id' => $ptkp->id,
    ]);

    $createUser->update(['data_karyawan_id' => $dataKaryawan->id]);
    return $dataKaryawan;
  }
}
