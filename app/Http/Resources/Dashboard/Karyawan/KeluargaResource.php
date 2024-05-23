<?php

namespace App\Http\Resources\Dashboard\Karyawan;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class KeluargaResource extends JsonResource
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
        // Mengelompokkan data berdasarkan data_karyawan_id
        // $grouped = $collection->groupBy('data_karyawan_id');

        // return $grouped->flatMap(function ($keluargas, $data_karyawan_id) {
        //     // Menghitung jumlah keluarga untuk setiap data_karyawan_id
        //     $jumlah_keluarga = $keluargas->count();

        //     return $keluargas->map(function ($keluarga) use ($jumlah_keluarga) {
        //         return [
        //             'id' => $keluarga->id,
        //             'data_karyawan_id' => $keluarga->data_karyawans->users,
        //             'jumlah_keluarga' => $jumlah_keluarga,
        //             'nama_keluarga' => $keluarga->nama_keluarga,
        //             'hubungan' => $keluarga->hubungan,
        //             'pendidikan_terakhir' => $keluarga->pendidikan_terakhir,
        //             'pekerjaan' => $keluarga->pekerjaan,
        //             'status_hidup' => $keluarga->status_hidup,
        //             'no_hp' => $keluarga->no_hp,
        //             'email' => $keluarga->email,
        //             'created_at' => $keluarga->created_at,
        //             'updated_at' => $keluarga->updated_at
        //         ];
        //     });
        // });
        return $collection->transform(function ($keluarga) {
            return [
                'id' => $keluarga->id,
                'data_karyawan_id' => $keluarga->data_karyawans->users,
                // 'jumlah_keluarga' => $this->jumlah_keluarga,
                'nama_keluarga' => $keluarga->nama_keluarga,
                'hubungan' => $keluarga->hubungan,
                'pendidikan_terakhir' => $keluarga->pendidikan_terakhir,
                'pekerjaan' => $keluarga->pekerjaan,
                'status_hidup' => $keluarga->status_hidup,
                'no_hp' => $keluarga->no_hp,
                'email' => $keluarga->email,
                'created_at' => $keluarga->created_at,
                'updated_at' => $keluarga->updated_at
            ];
        });
    }
}
