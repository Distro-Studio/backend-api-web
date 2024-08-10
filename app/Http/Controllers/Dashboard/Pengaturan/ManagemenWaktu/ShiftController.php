<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Http\Requests\Excel_Import\ImportShiftRequest;
use App\Exports\Pengaturan\Managemen_Waktu\ShiftExport;
use App\Imports\Pengaturan\Managemen_Waktu\ShiftImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class ShiftController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllShift()
    {
        if (!Gate::allows('view shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift = Shift::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all shift for dropdown',
            'data' => $shift
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift = Shift::withTrashed()->orderBy('created_at', 'desc');

        // Filter
        // if ($request->has('delete_data')) {
        //     $softDeleteFilters = $request->delete_data;
        //     $shift->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
        //         return $query->onlyTrashed();
        //     })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
        //         return $query->withoutTrashed();
        //     });
        // }

        // Search
        // if ($request->has('search')) {
        //     $shift = $shift->where(function ($query) use ($request) {
        //         $searchTerm = '%' . $request->search . '%';

        //         $query->orWhere('nama', 'like', $searchTerm);
        //     });
        // }

        $dataShift = $shift->get();
        if ($dataShift->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data shift berhasil ditampilkan.";
        $formattedData = $this->formatData($dataShift);
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

        $shift = Shift::create($data);
        $successMessage = "Data shift '{$shift->nama}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Shift $shift)
    {
        if (!Gate::allows('view shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$shift) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data shift '{$shift->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateShiftRequest $request)
    {
        $shift = Shift::withTrashed()->find($id);

        if (!Gate::allows('edit shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = Shift::where('nama', $data['nama'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama shift tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $shift->update($data);
        $updatedShift = $shift->fresh();
        $successMessage = "Data shift '{$updatedShift->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Shift $shift)
    {
        if (!Gate::allows('delete shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift->delete();

        $successMessage = "Data shift '{$shift->nama}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $shift = Shift::withTrashed()->find($id);

        if (!Gate::allows('delete shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift->restore();

        if (is_null($shift->deleted_at)) {
            $successMessage = "Data shift '{$shift->nama}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($shift) {
            return [
                'id' => $shift->id,
                'nama' => $shift->nama,
                'jam_from' => $shift->jam_from,
                'jam_to' => $shift->jam_to,
                'deleted_at' => $shift->deleted_at,
                'created_at' => $shift->created_at,
                'updated_at' => $shift->updated_at
            ];
        });
    }
}
