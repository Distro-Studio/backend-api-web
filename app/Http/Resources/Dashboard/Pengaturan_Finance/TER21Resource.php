<?php

namespace App\Http\Resources\Dashboard\Pengaturan_Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class TER21Resource extends JsonResource
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
        return $collection->transform(function ($ter21) {
            return [
                'id' => 'T00' . $ter21->id,
                'kategori_ter_id' => $ter21->kategori_ters,
                'ptkp' => $ter21->ptkps,
                'from_ter' => $ter21->from_ter,
                'to_ter' => $ter21->to_ter,
                'percentage_ter' => $ter21->percentage_ter,
                'deleted_at' => $ter21->deleted_at,
                'created_at' => $ter21->created_at,
                'updated_at' => $ter21->updated_at
            ];
        });
    }
}
