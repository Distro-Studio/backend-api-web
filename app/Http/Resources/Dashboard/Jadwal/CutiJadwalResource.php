<?php

namespace App\Http\Resources\Dashboard\Jadwal;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class CutiJadwalResource extends JsonResource
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
        return $collection->transform(function ($cuti_jadwal) {
            return [
                'id' => $cuti_jadwal->id,
                'user' => $cuti_jadwal->users,
                'tipe_cuti' => $cuti_jadwal->tipe_cutis,
                'tgl_from' => $cuti_jadwal->tgl_from,
                'tgl_to' => $cuti_jadwal->tgl_to,
                'catatan' => $cuti_jadwal->catatan,
                'durasi' => $cuti_jadwal->durasi,
                'created_at' => $cuti_jadwal->created_at,
                'updated_at' => $cuti_jadwal->updated_at
            ];
        });
    }
}
