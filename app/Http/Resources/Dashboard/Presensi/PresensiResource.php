<?php

namespace App\Http\Resources\Dashboard\Presensi;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class PresensiResource extends JsonResource
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
        return $collection->transform(function ($presensi) {
            return [
                'id' => $presensi->id,
                'user_id' => $presensi->users,
                'data_karyawan_id' => optional($presensi->data_karyawans)->unit_kerjas,
                'jadwal_id' => optional($presensi->jadwals)->shifts, // ambil shift
                'jam_masuk' => $presensi->jam_masuk,
                'jam_keluar' => $presensi->jam_keluar,
                'durasi' => $presensi->durasi,
                'lat' => $presensi->lat,
                'long' => $presensi->long,
                'foto' => $presensi->foto,
                'absensi' => $presensi->absensi,
                'kategori' => $presensi->kategori,
                'created_at' => $presensi->created_at,
                'updated_at' => $presensi->updated_at
            ];
        });
    }
}
