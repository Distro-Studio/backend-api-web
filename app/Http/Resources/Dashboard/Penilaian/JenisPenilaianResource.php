<?php

namespace App\Http\Resources\Dashboard\Penilaian;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class JenisPenilaianResource extends JsonResource
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
        return $collection->transform(function ($jenis_penilaian) {
            return [
                'id' => $jenis_penilaian->id,
                'nama' => $jenis_penilaian->nama,
                'tgl_mulai' => $jenis_penilaian->tgl_mulai,
                'tgl_selesai' => $jenis_penilaian->tgl_selesai,
                'status_karyawan' => $jenis_penilaian->status_karyawans,
                'unit_kerja' => $jenis_penilaian->unit_kerjas,
                'created_at' => $jenis_penilaian->created_at,
                'updated_at' => $jenis_penilaian->updated_at
            ];
        });
    }
}
