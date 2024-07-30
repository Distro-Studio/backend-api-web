<?php

namespace App\Imports\Karyawan;

use App\Models\Ptkp;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Kompetensi;
use App\Models\DataKaryawan;
use App\Models\KelompokGaji;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
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
            'email' => 'required|email|max:225|unique:data_karyawans,email',
            'role' => 'required|exists:roles,name',
            'no_rm' => 'required',
            'no_manulife' => 'nullable',
            'tgl_masuk' => 'required',
            'unit_kerja' => 'required|exists:unit_kerjas,nama_unit',
            'jabatan' => 'required|exists:jabatans,nama_jabatan',
            'kompetensi' => 'required|exists:kompetensis,nama_kompetensi',
            'status_karyawan' => 'required|exists:status_karyawans,label',

            'kelompok_gaji' => 'required|exists:kelompok_gajis,nama_kelompok',
            'no_rekening' => 'required|numeric',
            'tunjangan_jabatan' => 'required|numeric',
            'tunjangan_fungsional' => 'required|numeric',
            'tunjangan_khusus' => 'required|numeric',
            'tunjangan_lainnya' => 'required|numeric',
            'uang_makan' => 'required|numeric',
            'uang_lembur' => 'nullable|numeric',
            'kode_ptkp' => 'required|exists:ptkps,kode_ptkp',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama karyawan melebihi batas maksimum panjang karakter.',
            'email.required' => 'Email karyawan tidak diperbolehkan kosong.',
            'email.email' => 'Alamat email yang valid wajib menggunakan @.',
            'email.max' => 'Email karyawan melebihi batas maksimum panjang karakter.',
            'email.unique' => 'Email karyawan tersebut sudah pernah digunakan.',
            'role.required' => 'Silahkan masukkan nama role karyawan terlebih dahulu.',
            'role.exists' => 'Maaf role tersebut tidak valid.',
            'no_rm.required' => 'Nomor rekam medis karyawan tidak diperbolehkan kosong.',
            'no_manulife.string' => 'Nomor manulife karyawan tidak diperbolehkan kosong.',
            'tgl_masuk.required' => 'Tanggal masuk karyawan tidak diperbolehkan kosong.',
            'unit_kerja.required' => 'Silahkan masukkan nama unit kerja karyawan terlebih dahulu.',
            'unit_kerja.exists' => 'Maaf unit kerja tersebut tidak valid.',
            'jabatan.required' => 'Silahkan masukkan nama jabatan karyawan terlebih dahulu.',
            'jabatan.exists' => 'Maaf jabatan tersebut tidak valid.',
            'kompetensi.required' => 'Silahkan masukkan nama kompetensi karyawan terlebih dahulu.',
            'kompetensi.exists' => 'Maaf kompetensi tersebut tidak valid.',
            'status_karyawan.required' => 'Status karyawan tidak diperbolehkan kosong.',
            'status_karyawan.exists' => 'Status karyawan tersebut tidak valid.',

            'kelompok_gaji.required' => 'Silahkan pilih kelompok gaji karyawan terlebih dahulu.',
            'kelompok_gaji.exists' => 'Maaf kelompok gaji tersebut tidak valid.',
            'no_rekening.required' => 'Nomor rekening karyawan tidak diperbolehkan kosong.',
            'no_rekening.numeric' => 'Nomor rekening karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_jabatan.required' => 'Tunjangan jabatan karyawan tidak diperbolehkan kosong.',
            'tunjangan_jabatan.numeric' => 'Tunjangan jabatan karyawan tidak diperbolehkan mengandung huruf.',
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
            'kode_ptkp.required' => 'Silahkan pilih PTKP karyawan terlebih dahulu.',
            'kode_ptkp.exists' => 'Maaf PTKP tersebut tidak valid.',
        ];
    }

    public function model(array $row)
    {
        $existingUser = $this->User->where('nama', $row['nama'])->first();
        if ($existingUser) {
            $user_id = $existingUser->id;
        } else {
            $password = RandomHelper::generatePassword();

            // Find role ID
            $role = $this->Role->where('name', $row['role'])->first();

            if (!$role) {
                throw new \Exception("Role tidak tersedia: " . $row['role']);
            }

            // Create new user
            $userData = [
                'nama' => $row['nama'],
                'role_id' => $role->id,
                'password' => Hash::make($password),
            ];
            $createUser = User::create($userData);
            $createUser->assignRole($role->name);
            Mail::to($row['email'])->send(new SendAccoundUsersMail($row['email'], $password, $row['nama']));

            $user_id = $createUser->id;
        }

        $jabatan_id = $this->Jabatan->where('nama_jabatan', $row['jabatan'])->first();
        $unit_kerja_id = $this->UnitKerja->where('nama_unit', $row['unit_kerja'])->first();
        $kompetensi_id = $this->Kompetensi->where('nama_kompetensi', $row['kompetensi'])->first();
        $kelompok_gaji_id = $this->KelompokGaji->where('nama_kelompok', $row['kelompok_gaji'])->first();
        $ptkp_id = $this->PTKP->where('kode_ptkp', $row['kode_ptkp'])->first();
        $status_karyawan_id = $this->StatusKaryawan->where('label', $row['status_karyawan'])->first();

        return new DataKaryawan([
            'user_id' => $user_id,
            'email' => $row['email'],
            'no_rm' => $row['no_rm'],
            'no_manulife' => $row['no_manulife'],
            'tgl_masuk' => $row['tgl_masuk'],
            'unit_kerja_id' => $unit_kerja_id->id,
            'jabatan_id' => $jabatan_id->id,
            'kompetensi_id' => $kompetensi_id->id,
            'status_karyawan' => $status_karyawan_id->id,
            'kelompok_gaji_id' => $kelompok_gaji_id->id,
            'no_rekening' => $row['no_rekening'],
            'tunjangan_jabatan' => $row['tunjangan_jabatan'],
            'tunjangan_fungsional' => $row['tunjangan_fungsional'],
            'tunjangan_khusus' => $row['tunjangan_khusus'],
            'tunjangan_lainnya' => $row['tunjangan_lainnya'],
            'uang_makan' => $row['uang_makan'],
            'uang_lembur' => $row['uang_lembur'],
            'ptkp_id' => $ptkp_id->id,
        ]);
    }
}
