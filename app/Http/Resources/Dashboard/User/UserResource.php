<?php

namespace App\Http\Resources\Dashboard\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public $status;
    public $message;
    public $data;

    public function __construct($status, $message, $data)
    {
        parent::__construct($data);
        $this->status = $status;
        $this->message = $message;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data_karyawan = $this->data_karyawans;
        $role = $this->roles->first();

        return [
            'status' => $this->status,
            'message' => $this->message,
            'data'  => [
                'id' => $this->id,
                'nama' => $this->nama,
                'email' => $data_karyawan ? $data_karyawan->email : null,
                'foto_profil' => $this->foto_profil,
                'status_aktif' => $this->status_aktif,
                'data_completion_step' => $this->data_completion_step,
                'data_karyawan' => [
                    'id' => $data_karyawan->id,
                    'email' => $data_karyawan->email,
                    'no_rm' => $data_karyawan->no_rm ?? null,
                    'no_manulife' => $data_karyawan->no_manulife ?? null,
                    'tgl_masuk' => $data_karyawan->tgl_masuk ?? null,
                    'unit_kerja' => $data_karyawan->unit_kerjas ?? null,
                    'jabatan' => $data_karyawan->jabatans ?? null,
                    'kompetensi' => $data_karyawan->kompetensis ?? null,
                    'nik_ktp' => $data_karyawan->nik_ktp ?? null,
                    'status_karyawan' => $data_karyawan->status_karyawans ?? null,
                    'tempat_lahir' => $data_karyawan->tempat_lahir ?? null,
                    'tgl_lahir' => $data_karyawan->tgl_lahir ?? null,
                    'kelompok_gaji' => $data_karyawan->kelompok_gajis ?? null,
                    'no_rekening' => $data_karyawan->no_rekening ?? null,
                    'tunjangan_jabatan' => $data_karyawan->tunjangan_jabatan ?? null,
                    'tunjangan_fungsional' => $data_karyawan->tunjangan_fungsional ?? null,
                    'tunjangan_khusus' => $data_karyawan->tunjangan_khusus ?? null,
                    'tunjangan_lainnya' => $data_karyawan->tunjangan_lainnya ?? null,
                    'uang_lembur' => $data_karyawan->uang_lembur ?? null,
                    'uang_makan' => $data_karyawan->uang_makan ?? null,
                    'ptkp' => $data_karyawan->ptkps ?? null,
                    'tgl_keluar' => $data_karyawan->tgl_keluar ?? null,
                    'no_kk' => $data_karyawan->no_kk ?? null,
                    'alamat' => $data_karyawan->alamat ?? null,
                    'gelar_depan' => $data_karyawan->gelar_depan ?? null,
                    'no_hp' => $data_karyawan->no_hp ?? null,
                    'no_bpjsksh' => $data_karyawan->no_bpjsksh ?? null,
                    'no_bpjsktk' => $data_karyawan->no_bpjsktk ?? null,
                    'tgl_diangkat' => $data_karyawan->tgl_diangkat ?? null,
                    'masa_kerja' => $data_karyawan->masa_kerja ?? null,
                    'npwp' => $data_karyawan->npwp ?? null,
                    'jenis_kelamin' => $data_karyawan->jenis_kelamin ?? null,
                    'agama' => $data_karyawan->kategori_agamas ?? null,
                    'golongan_darah' => $data_karyawan->kategori_darahs ?? null,
                    'tinggi_badan' => $data_karyawan->tinggi_badan ?? null,
                    'berat_badan' => $data_karyawan->berat_badan ?? null,
                    'no_ijazah' => $data_karyawan->no_ijazah ?? null,
                    'tahun_lulus' => $data_karyawan->tahun_lulus ?? null,
                    'no_str' => $data_karyawan->no_str ?? null,
                    'masa_berlaku_str' => $data_karyawan->masa_berlaku_str ?? null,
                    'tgl_berakhir_pks' => $data_karyawan->tgl_berakhir_pks ?? null,
                    'masa_diklat' => $data_karyawan->masa_diklat ?? null,
                    'created_at' => $data_karyawan->created_at,
                    'updated_at' => $data_karyawan->updated_at,
                ],
                'role' => [
                    'id' => $role ? $role->id : null,
                    'name' => $role ? $role->name : null,
                    'deskripsi' => $role ? $role->deskripsi : null,
                    'created_at' => $role ? $role->created_at : null,
                    'updated_at' => $role ? $role->updated_at : null,
                ],
                'permissions' => $this->getAllPermissions(),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }
}
