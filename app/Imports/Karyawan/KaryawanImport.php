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
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Mail\SendAccoundUsersMail;
use Illuminate\Http\Response;
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

    public function __construct()
    {
        $this->User = User::select('id', 'nama')->get();
        $this->Role = Role::select('id', 'name')->get();
        $this->Jabatan = Jabatan::select('id', 'nama_jabatan')->get();
        $this->UnitKerja = UnitKerja::select('id', 'nama_unit')->get();
        $this->Kompetensi = Kompetensi::select('id', 'nama_kompetensi')->get();
        $this->KelompokGaji = KelompokGaji::select('id', 'nama_kelompok')->get();
        $this->PTKP = Ptkp::select('id', 'kode_ptkp')->get();
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:225',
            'email' => 'required|email|max:225|unique:data_karyawans,email',
            'roles' => 'required|exists:roles,name',
            'no_rm' => 'required',
            'no_manulife' => 'nullable',
            'tgl_masuk' => 'required',
            'unit_kerja' => 'required|exists:unit_kerjas,nama_unit',
            'jabatan' => 'required|exists:jabatans,nama_jabatan',
            'kompetensi' => 'required|exists:kompetensis,nama_kompetensi',
            // 'status_karyawan' => 'required|string',

            'kelompok_gaji' => 'required|integer|exists:kelompok_gajis,id',
            'no_rekening' => 'required|numeric|max:50',
            'tunjangan_jabatan' => 'required|numeric|max:20',
            'tunjangan_fungsional' => 'required|numeric|max:20',
            'tunjangan_khusus' => 'required|numeric|max:20',
            'tunjangan_lainnya' => 'required|numeric|max:20',
            'uang_makan' => 'required|numeric|max:20',
            'uang_lembur' => 'nullable|numeric|max:20',
            'kode_ptkp' => 'required|integer|exists:ptkps,id',

            'username' => 'nullable|unique:users,username',
            'password' => 'nullable',
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
            'roles.required' => 'Silahkan masukkan nama role karyawan terlebih dahulu.',
            'roles.exists' => 'Maaf role tersebut tidak valid.',
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
            'status_karyawan.string' => 'Status karyawan tidak diperbolehkan mengandung angka.',

            'kelompok_gaji.required' => 'Silahkan pilih kelompok gaji karyawan terlebih dahulu.',
            'kelompok_gaji.exists' => 'Maaf kelompok gaji tersebut tidak valid.',
            'no_rekening.required' => 'Nomor rekening karyawan tidak diperbolehkan kosong.',
            'no_rekening.numeric' => 'Nomor rekening karyawan tidak diperbolehkan mengandung huruf.',
            'no_rekening.max' => 'Nomor rekening karyawan melebihi batas maksimum panjang karakter.',
            'tunjangan_jabatan.required' => 'Tunjangan jabatan karyawan tidak diperbolehkan kosong.',
            'tunjangan_jabatan.numeric' => 'Tunjangan jabatan karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_jabatan.max' => 'Tunjangan jabatan karyawan melebihi batas maksimum panjang karakter.',
            'tunjangan_fungsional.required' => 'Tunjangan fungsional karyawan tidak diperbolehkan kosong.',
            'tunjangan_fungsional.numeric' => 'Tunjangan fungsional karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_fungsional.max' => 'Tunjangan fungsional karyawan melebihi batas maksimum panjang karakter.',
            'tunjangan_khusus.required' => 'Tunjangan khusus karyawan tidak diperbolehkan kosong.',
            'tunjangan_khusus.numeric' => 'Tunjangan khusus karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_khusus.max' => 'Tunjangan khusus karyawan melebihi batas maksimum panjang karakter.',
            'tunjangan_lainnya.required' => 'Tunjangan karyawan lainya tidak diperbolehkan kosong.',
            'tunjangan_lainnya.numeric' => 'Tunjangan karyawan lainya tidak diperbolehkan mengandung huruf.',
            'tunjangan_lainnya.max' => 'Tunjangan lainya karyawan melebihi batas maksimum panjang karakter.',
            'uang_makan.required' => 'Uang makan karyawan tidak diperbolehkan kosong.',
            'uang_makan.numeric' => 'Uang makan karyawan tidak diperbolehkan mengandung huruf.',
            'uang_makan.max' => 'Uang makan karyawan melebihi batas maksimum panjang karakter.',
            'uang_lembur.required' => 'Uang lembur karyawan tidak diperbolehkan kosong.',
            'uang_lembur.numeric' => 'Uang lembur karyawan tidak diperbolehkan mengandung huruf.',
            'uang_lembur.max' => 'Uang lembur karyawan melebihi batas maksimum panjang karakter.',
            'kode_ptkp.required' => 'Silahkan pilih PTKP karyawan terlebih dahulu.',
            'kode_ptkp.exists' => 'Maaf PTKP tersebut tidak valid.',

            'username.required' => 'Username karyawan tidak diperbolehkan kosong.',
            'username.unique' => 'Username tersebut sudah pernah digunakan.',
            'password.required' => 'Password karyawan tidak diperbolehkan kosong.',
        ];
    }

    public function model(array $row)
    {
        $existingUser = $this->User->where('nama', $row['nama'])->first();
        if ($existingUser) {
            $user_id = $existingUser->id;
        } else {
            $username = RandomHelper::generateUniqueUsername($row['nama'], $row['email']);
            $password = RandomHelper::generatePassword();

            // Find role ID
            $role = $this->Role->where('name', $row['roles'])->first();

            if (!$role) {
                throw new \Exception("Role tidak tersedia: " . $row['roles']);
            }

            // Create new user
            $userData = [
                'nama' => $row['nama'],
                'role_id' => $role->id,
                'username' => $username,
                'password' => Hash::make($password),
            ];
            $createUser = User::create($userData);
            $createUser->assignRole($role->name);
            Mail::to($row['email'])->send(new SendAccoundUsersMail($username, $password, $row['nama']));

            $user_id = $createUser->id;
        }

        $jabatan_id = $this->Jabatan->where('nama_jabatan', $row['jabatan'])->first();
        $unit_kerja_id = $this->UnitKerja->where('nama_unit', $row['unit_kerja'])->first();
        $kompetensi_id = $this->Kompetensi->where('nama_kompetensi', $row['kompetensi'])->first();
        $kelompok_gaji_id = $this->KelompokGaji->where('nama_kelompok', $row['kelompok_gaji'])->first();
        $ptkp_id = $this->PTKP->where('kode_ptkp', $row['kode_ptkp'])->first();

        return new DataKaryawan([
            'user_id' => $user_id,
            'email' => $row['email'],
            'no_rm' => $row['no_rm'],
            'no_manulife' => $row['no_manulife'],
            'tgl_masuk' => $row['tgl_masuk'],
            'unit_kerja_id' => $unit_kerja_id->id,
            'jabatan_id' => $jabatan_id->id,
            'kompetensi_id' => $kompetensi_id->id,
            'status_karyawan' => $row['status_karyawan'],
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
