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

        $non_shift = NonShift::withTrashed()->orderBy('created_at', 'desc');

        $data_non_shift = $non_shift->get();
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

        $successMessage = "Data jam kerja tetap karyawwan '{$non_shift->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$non_shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function editJadwalNonShift(UpdateNonShiftRequest $request)
    {
        // Check if the user has the required permission
        if (!Gate::allows('edit shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Find the NonShift record with ID 1
        $non_shift = NonShift::withTrashed()->find(1);

        if (!$non_shift) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jam kerja tetap karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Validate for uniqueness
        $existingDataValidation = NonShift::where('nama', $data['nama'])->where('id', '!=', 1)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama jam kerja tetap karyawan tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        // Update the NonShift record
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
