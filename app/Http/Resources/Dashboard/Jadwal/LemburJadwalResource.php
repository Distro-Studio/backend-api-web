<?php

namespace App\Http\Resources\Dashboard\Jadwal;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class LemburJadwalResource extends JsonResource
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
        return $collection->transform(function ($lembur) {
            return [
                'id' => $lembur->id,
                'user' => [
                    'id' => $lembur->users->id,
                    'nama' => $lembur->users->nama,
                    'email_verified_at' => $lembur->users->email_verified_at,
                    'data_karyawan_id' => $lembur->users->data_karyawan_id,
                    'foto_profil' => $lembur->users->foto_profil,
                    'data_completion_step' => $lembur->users->data_completion_step,
                    'status_aktif' => $lembur->users->status_aktif,
                    'created_at' => $lembur->users->created_at,
                    'updated_at' => $lembur->users->updated_at
                ],
                'jadwal' => $lembur->jadwals,
                'tgl_pengajuan' => $lembur->tgl_pengajuan,
                'kompensasi' => $lembur->kategori_kompensasis,
                'durasi' => $lembur->durasi,
                'catatan' => $lembur->catatan,
                // 'status' => $lembur->status_lemburs,
                'created_at' => $lembur->created_at,
                'updated_at' => $lembur->updated_at
            ];
        });
    }
}
