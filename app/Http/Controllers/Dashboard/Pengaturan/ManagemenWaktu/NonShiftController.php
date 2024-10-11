<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\NonShift;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNonShiftRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class NonShiftController extends Controller
{
    public function getAllNonShift()
    {
        if (!Gate::allows('view shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $non_shift = NonShift::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all jam kerja tetap karyawwan for dropdown',
            'data' => $non_shift
        ], Response::HTTP_OK);
    }

    public function index()
    {
        if (!Gate::allows('view shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data_non_shift = NonShift::withTrashed()->orderBy('created_at', 'desc')->get();
        if ($data_non_shift->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data jam kerja tetap karyawwan berhasil ditampilkan.";
        $formattedData = $this->formatData($data_non_shift);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(NonShift $non_shift)
    {
        if (!Gate::allows('view shift', $non_shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$non_shift) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data jam kerja tetap karyawan '{$non_shift->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$non_shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function edit(UpdateNonShiftRequest $request, $non_shift)
    {
        if (!Gate::allows('edit shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Find the NonShift record with ID 1
        $non_shift = NonShift::withTrashed()->find($non_shift);
        if (!$non_shift) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jam kerja tetap karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $non_shift->update($data);

        $successMessage = "Data jam kerja tetap karyawan '{$non_shift->nama}' berhasil diubah.";
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $non_shift->fresh(),
        ], Response::HTTP_OK);
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($non_shift) {
            return [
                'id' => $non_shift->id,
                'nama' => $non_shift->nama,
                'jam_from' => $non_shift->jam_from,
                'jam_to' => $non_shift->jam_to,
                'deleted_at' => $non_shift->deleted_at,
                'created_at' => $non_shift->created_at,
                'updated_at' => $non_shift->updated_at
            ];
        });
    }
}
