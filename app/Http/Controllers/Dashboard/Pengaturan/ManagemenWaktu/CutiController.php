<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\TipeCuti;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreCutiRequest;
use App\Http\Requests\UpdateCutiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class CutiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllTipeCuti()
    {
        if (!Gate::allows('view cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti = TipeCuti::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all cuti for dropdown',
            'data' => $cuti
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index()
    {
        if (!Gate::allows('view cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti = TipeCuti::withTrashed()->orderBy('created_at', 'desc');

        $dataTipeCuti = $cuti->get();
        if ($dataTipeCuti->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data tipe cuti berhasil ditampilkan.";
        $formattedData = $this->formatData($dataTipeCuti);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreCutiRequest $request)
    {
        if (!Gate::allows('create cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $tipe_cuti = TipeCuti::create($data);
        $successMessage = "Data tipe cuti '{$tipe_cuti->nama}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$tipe_cuti]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(TipeCuti $cuti)
    {
        if (!Gate::allows('view cuti', $cuti)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$cuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data tipe cuti '{$cuti->nama}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$cuti]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateCutiRequest $request)
    {
        $cuti = TipeCuti::withTrashed()->find($id);

        if (!Gate::allows('edit cuti', $cuti)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = TipeCuti::where('nama', $data['nama'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama cuti tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $cuti->update($data);
        $updatedCuti = $cuti->fresh();
        $successMessage = "Data cuti '{$updatedCuti->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$cuti]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(TipeCuti $cuti)
    {
        if (!Gate::allows('delete cuti', $cuti)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti->delete();

        $successMessage = "Data cuti {$cuti->nama} berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $cuti = TipeCuti::withTrashed()->find($id);

        if (!Gate::allows('delete cuti', $cuti)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti->restore();

        if (is_null($cuti->deleted_at)) {
            $successMessage = "Data cuti '{$cuti->nama}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($tipe_cuti) {
            return [
                'id' => $tipe_cuti->id,
                'nama' => $tipe_cuti->nama,
                'kuota' => $tipe_cuti->kuota,
                'is_need_requirement' => $tipe_cuti->is_need_requirement,
                'keterangan' => $tipe_cuti->keterangan,
                'cuti_administratif' => $tipe_cuti->cuti_administratif,
                'deleted_at' => $tipe_cuti->deleted_at,
                'created_at' => $tipe_cuti->created_at,
                'updated_at' => $tipe_cuti->updated_at
            ];
        });
    }
}
