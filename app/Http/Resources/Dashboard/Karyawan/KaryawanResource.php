<?php

namespace App\Http\Resources\Dashboard\Karyawan;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class KaryawanResource extends JsonResource
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

    public function toArray($request)
    {
        // Check if the resource is a paginator instance and adapt the response accordingly
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'status' =>  $this->status,
                'message' => $this->message,
                'data' => $this->formatData($this->resource->getCollection()),
                'links' => [
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $this->resource->currentPage(),
                    'from' => $this->resource->firstItem(),
                    'last_page' => $this->resource->lastPage(),
                    'per_page' => $this->resource->perPage(),
                    'to' => $this->resource->lastItem(),
                    'total' => $this->resource->total(),
                ],
            ];
        } else {
            return [
                'status' =>  $this->status,
                'message' => $this->message,
                'data' => $this->formatData(collect([$this->resource])),
            ];
        }
    }


    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($karyawan) {
            return [
                'id' => $karyawan->id,

                // Step 1
                'user' => [
                    'id' => $karyawan->users->id,
                    'nama' => $karyawan->users->nama,
                    'email_verified_at' => $karyawan->users->email_verified_at,
                    'data_karyawan_id' => $karyawan->users->data_karyawan_id,
                    'foto_profil' => $karyawan->users->foto_profil,
                    'data_completion_step' => $karyawan->users->data_completion_step,
                    'status_aktif' => $karyawan->users->status_aktif,
                    'created_at' => $karyawan->users->created_at,
                    'updated_at' => $karyawan->users->updated_at
                ],
                'role' => $karyawan->users->roles,
                'email' => $karyawan->email,
                'no_rm' => $karyawan->no_rm,
                'no_manulife' => $karyawan->no_manulife,
                'tgl_masuk' => $karyawan->tgl_masuk,
                'unit_kerja' => $karyawan->unit_kerjas,
                'jabatan' => $karyawan->jabatans,
                'kompetensi' => $karyawan->kompetensis,
                
                // yang ada di table tp gak ada di create
                "nik" => $karyawan->nik,
                "nik_ktp" => $karyawan->nik_ktp,
                'status_karyawan' => $karyawan->status_karyawans,
                'tempat_lahir' => $karyawan->tempat_lahir,
                'tgl_lahir' => $karyawan->tgl_lahir,

                // Step 2
                'kelompok_gaji' => $karyawan->kelompok_gajis,
                'no_rekening' => $karyawan->no_rekening,
                'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
                'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
                'tunjangan_khusus' => $karyawan->tunjangan_khusus,
                'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
                'uang_lembur' => $karyawan->uang_lembur,
                'uang_makan' => $karyawan->uang_makan,
                'ptkp' => $karyawan->ptkps,

                "tgl_keluar" => $karyawan->tgl_keluar,
                "no_kk" => $karyawan->no_kk,
                "alamat" => $karyawan->alamat,
                "gelar_depan" => $karyawan->gelar_depan,
                "no_hp" => $karyawan->no_hp,
                "no_bpjsksh" => $karyawan->no_bpjsksh,
                "no_bpjsktk" => $karyawan->no_bpjsktk,
                "tgl_diangkat" => $karyawan->tgl_diangkat,
                "masa_kerja" => $karyawan->masa_kerja,
                "npwp" => $karyawan->npwp,
                "jenis_kelamin" => $karyawan->jenis_kelamin,
                "agama" => $karyawan->agama,
                "golongan_darah" => $karyawan->golongan_darah,
                "tinggi_badan" => $karyawan->tinggi_badan,
                "berat_badan" => $karyawan->berat_badan,
                "no_ijasah" => $karyawan->no_ijasah,
                "tahun_lulus" => $karyawan->tahun_lulus,
                "no_str" => $karyawan->no_str,
                "masa_berlaku_str" => $karyawan->masa_berlaku_str,
                "no_sip" => $karyawan->no_sip,
                "masa_berlaku_sip" => $karyawan->masa_berlaku_sip,
                "tgl_berakhir_pks" => $karyawan->tgl_berakhir_pks,
                "masa_diklat" => $karyawan->masa_diklat,
                
                'created_at' => $karyawan->created_at,
                'updated_at' => $karyawan->updated_at
            ];
        });
    }
}
