<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\LokasiKantor;
use Illuminate\Http\Request;
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
            'radius' => $lokasiKantor->radius
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Detail lokasi kantor berhasil ditampilkan.',
            'data' => $lokasiKantorDetail
        ], Response::HTTP_OK);
    }

    public function createLokasiKantor(StoreLokasiKantorRequest $request)
    {
        if (!Gate::allows('create lokasiKantor') && !Gate::allows('edit lokasiKantor')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Create or Update the first jadwal penggajian
        $lokasiKantor = LokasiKantor::updateOrCreate(
            ['id' => 1],
            [
                'alamat' => $data['alamat'],
                'lat' => $data['lat'],
                'long' => $data['long'],
                'radius' => $data['radius']
            ]
        );

        $message = $lokasiKantor->wasRecentlyCreated
            ? "Titik lokasi kantor berhasil diatur pada tanggal {$lokasiKantor->created_at}."
            : "Titik lokasi kantor berhasil diperbarui pada tanggal {$lokasiKantor->updated_at}.";

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $message,
            'data' => $lokasiKantor
        ], Response::HTTP_OK);
    }
}
