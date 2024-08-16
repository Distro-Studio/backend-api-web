<?php

namespace App\Http\Resources\Dashboard\Karyawan;

use App\Http\Resources\Dashboard\Pengaturan_Karyawan\JabatanResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\UnitKerjaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class TransferKaryawanResource extends JsonResource
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
        return $collection->transform(function ($transfer) {
            $user = $transfer->users;
            $data_karyawan = $user->data_karyawans;
            return [
                'id' => $transfer->id,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'role' => $user->role,
                    'foto_profil' => $user->foto_profil,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'tgl_mulai' => $transfer->tgl_mulai,
                'nik' => $data_karyawan->nik,
                'unit_kerja_asal' => $transfer->unit_kerja_asals,
                'unit_kerja_tujuan' => $transfer->unit_kerja_tujuans,
                'jabatan_asal' => $transfer->jabatan_asals,
                'jabatan_tujuan' => $transfer->jabatan_tujuans,
                'kelompok_gaji_asal' => $transfer->kelompok_gaji_asals,
                'kelompok_gaji_tujuan' => $transfer->kelompok_gaji_tujuans,
                'role_asal' => $transfer->role_asals,
                'role_tujuan' => $transfer->role_tujuans,
                'kategori_transfer' => $transfer->kategori_transfer_karyawans,
                'alasan' => $transfer->alasan,
                'dokumen' => $transfer->dokumen,
                'created_at' => $transfer->created_at,
                'updated_at' => $transfer->updated_at
            ];
        });
    }
}
