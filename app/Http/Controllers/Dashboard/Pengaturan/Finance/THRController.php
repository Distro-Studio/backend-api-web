<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreTHRSettingRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\Thr;

class THRController extends Controller
{
    public function getAllTHRSetting()
    {
        if (!Gate::allows('view thr')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses.'), Response::HTTP_FORBIDDEN);
        }

        $dataTHR = Thr::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all data thr for dropdown.',
            'data' => $dataTHR
        ], Response::HTTP_OK);
    }

    public function store(StoreTHRSettingRequest $request)
    {
        if (!Gate::allows('create thr')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $existingThr = Thr::where('perhitungan', $data['perhitungan'])
            ->where('potongan', $data['potongan'])
            ->first();
        if ($existingThr) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Data THR dengan perhitungan dan potongan yang sama sudah tersedia.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $thr = new Thr();
        $thr->perhitungan = $data['perhitungan'];
        $thr->nominal_satu = $data['nominal_satu'];
        $thr->nominal_dua = $data['nominal_dua'] ?? null;
        $thr->potongan = $data['potongan'];

        $tahun = $data['tahun'] ?? 0;
        $bulan = $data['bulan'];
        $thr->kriteria_karyawan_kontrak = "$tahun tahun, $bulan bulan";

        $thr->save();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Pengaturan THR {$data['perhitungan']} berhasil ditambahkan.",
            'data' => $thr
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view thr')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses.'), Response::HTTP_FORBIDDEN);
        }

        $thr = Thr::find($id);
        if (!$thr) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data THR tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data pengaturan THR berhasil ditampilkan.',
            'data' => $thr
        ], Response::HTTP_OK);
    }
}
