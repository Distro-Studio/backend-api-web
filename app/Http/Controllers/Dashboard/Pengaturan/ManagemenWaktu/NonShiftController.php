<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\NonShift;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShiftRequest;
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

    public function store(StoreShiftRequest $request)
    {
        if (!Gate::allows('create shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $non_shift = NonShift::create($data);
        $successMessage = "Data jam kerja tetap karyawwan '{$non_shift->nama}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$non_shift]))->first();

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

    public function update($id, UpdateNonShiftRequest $request)
    {
        $non_shift = NonShift::withTrashed()->find($id);

        if (!Gate::allows('edit shift', $non_shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = NonShift::where('nama', $data['nama'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama jam kerja tetap karyawwan tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $non_shift->update($data);
        $updatedShift = $non_shift->fresh();
        $successMessage = "Data jam kerja tetap karyawwan '{$updatedShift->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$non_shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    // public function destroy(NonShift $non_shift)
    // {
    //     if (!Gate::allows('delete shift', $non_shift)) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $non_shift->delete();

    //     $successMessage = "Data jam kerja tetap karyawwan '{$non_shift->nama}' berhasil dihapus.";
    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    // }

    // public function restore($id)
    // {
    //     $non_shift = NonShift::withTrashed()->find($id);

    //     if (!Gate::allows('delete shift', $non_shift)) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $non_shift->restore();

    //     if (is_null($non_shift->deleted_at)) {
    //         $successMessage = "Data jam kerja tetap karyawwan '{$non_shift->nama}' berhasil dipulihkan.";
    //         return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    //     } else {
    //         $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
    //     }
    // }

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
