<?php

namespace App\Http\Resources\Dashboard\Karyawan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class RekamJejakResource extends JsonResource
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
        return $collection->transform(function ($keluarga) {
            // Masa kerja calculation
            if ($keluarga->tgl_masuk) {
                $tglMasuk = Carbon::parse($keluarga->tgl_masuk);
                $tglSekarang = Carbon::now();

                if ($keluarga->tgl_keluar) {
                    // jika ada tgl_keluar
                    $tglKeluar = Carbon::parse($keluarga->tgl_keluar);
                    $masaKerja = $tglMasuk->diffInDays($tglKeluar);
                } else {
                    $masaKerja = $tglMasuk->diffInDays($tglSekarang);
                }
            } else {
                $masaKerja = null;
            }

            return [
                'id' => $keluarga->id,
                'user_id' => $keluarga->users,
                'tgl_masuk' => $keluarga->tgl_masuk,
                'tgl_keluar' => $keluarga->tgl_keluar,
                // calculation
                'masa_kerja' => $masaKerja,
                // calculation
                'promosi' => $keluarga->promosi,
                'mutasi' => $keluarga->mutasi,
                'penghargaan' => $keluarga->penghargaan,
                'created_at' => $keluarga->created_at,
                'updated_at' => $keluarga->updated_at
            ];
        });
    }
}
