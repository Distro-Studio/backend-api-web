<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\LokasiKantor;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreLokasiKantorRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class LokasiKantorController extends Controller
{
    public function getLokasiKantor($id)
    {
        if (!Gate::allows('view lokasiKantor')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $lokasiKantor = LokasiKantor::find($id);

        if (!$lokasiKantor) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lokasi kantor tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $lokasiKantorDetail = [
            'id' => $lokasiKantor->id,
            'alamat' => $lokasiKantor->alamat,
            'lat' => $lokasiKantor->lat,
            'long' => $lokasiKantor->long,
            'radius' => $lokasiKantor->radius,
            'created_at' => $lokasiKantor->created_at,
            'updated_at' => $lokasiKantor->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Detail lokasi kantor berhasil ditampilkan.',
            'data' => $lokasiKantorDetail
        ], Response::HTTP_OK);
    }

    public function editLokasiKantor(StoreLokasiKantorRequest $request)
    {
        if (!Gate::allows('edit lokasiKantor')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Find the first lokasi kantor with ID 1
        $lokasiKantor = LokasiKantor::find(1);

        if (!$lokasiKantor) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Titik lokasi kantor tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Update lokasi kantor
        $lokasiKantor->update([
            'alamat' => $data['alamat'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'radius' => $data['radius']
        ]);

        $message = "Titik lokasi kantor berhasil diperbarui pada tanggal '{$lokasiKantor->updated_at}'.";

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $message,
            'data' => $lokasiKantor
        ], Response::HTTP_OK);
    }
}
