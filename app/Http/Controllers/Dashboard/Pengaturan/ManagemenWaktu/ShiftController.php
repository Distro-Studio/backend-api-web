<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\Pengaturan\Managemen_Waktu\ShiftExport;
use App\Http\Requests\Excel_Import\ImportShiftRequest;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Http\Resources\Dashboard\Pengaturan_Managemen_Waktu\ShiftResource;
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

        $shift = Shift::query();

        // Filter
        $softDeleteFilters = $request->input('delete_data', []);
        if (in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters)) {
            $shift->withTrashed();
        } elseif (in_array('dihapus', $softDeleteFilters)) {
            $shift->onlyTrashed();
        } else {
            $shift->withoutTrashed();
        }

        // Search
        if ($request->has('search')) {
            $shift = $shift->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhere('nama', 'like', $searchTerm);
            });
        }

        $dataShift = $shift->get();
        if ($dataShift->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new ShiftResource(Response::HTTP_OK, 'Data shift berhasil ditampilkan.', $dataShift), Response::HTTP_OK);
    }

    public function store(StoreShiftRequest $request)
    {
        if (!Gate::allows('create shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $shift = Shift::create($data);
        $successMessage = "Data shift '{$shift->nama}' berhasil dibuat.";
        return response()->json(new ShiftResource(Response::HTTP_OK, $successMessage, $shift), Response::HTTP_OK);
    }

    public function show(Shift $shift)
    {
        if (!Gate::allows('view shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$shift) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new ShiftResource(Response::HTTP_OK, 'Data shift berhasil ditampilkan.', $shift), Response::HTTP_OK);
    }

    public function update(Shift $shift, UpdateShiftRequest $request)
    {
        if (!Gate::allows('edit shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $shift->update($data);
        $updatedShift = $shift->fresh();

        $successMessage = "Data shift '{$updatedShift->nama}' berhasil diubah.";
        return response()->json(new ShiftResource(Response::HTTP_OK, $successMessage, $updatedShift), Response::HTTP_OK);
    }

    public function destroy(Shift $shift)
    {
        if (!Gate::allows('delete shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift->delete();

        $successMessage = 'Data shift berhasil dihapus.';
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
            $successMessage = "Data shift {$shift->nama} berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportShift(Request $request)
    {
        if (!Gate::allows('export shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        try {
            return Excel::download(new ShiftExport(), 'data-shift.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data shift berhasil di download.'), Response::HTTP_OK);
    }

    public function importShift(ImportShiftRequest $request)
    {
        if (!Gate::allows('import shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new ShiftImport, $file['shift_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        // More informative success message
        $message = 'Data shift berhasil di import kedalam table.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }
}
