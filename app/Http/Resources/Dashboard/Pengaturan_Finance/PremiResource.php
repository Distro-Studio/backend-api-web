<?php

namespace App\Http\Resources\Dashboard\Pengaturan_Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class PremiResource extends JsonResource
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
        return $collection->transform(function ($premi) {
            return [
                'id' => 'P00' . $premi->id,
                'nama_premi' => $premi->nama_premi,
                'sumber_potongan' => $premi->sumber_potongan,
                'jenis_premi' => $premi->jenis_premi,
                'besaran_premi' => $premi->besaran_premi,
                'minimal_rate' => $premi->minimal_rate,
                'maksimal_rate' => $premi->maksimal_rate,
                'deleted_at' => $premi->deleted_at,
                'created_at' => $premi->created_at,
                'updated_at' => $premi->updated_at
            ];
        });
    }
}