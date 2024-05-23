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

        $shift = Shift::get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all shift for dropdown',
            'data' => $shift
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        // $this->middleware(RoleMiddleware::class, ['roles' => ['Super Admin']]);
        if (!Gate::allows('view shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift = Shift::query();

        // Filter
        if ($request->has('nama')) {
            $shift = $shift->where('nama', $request->nama);
        }

        if ($request->has('jam_from')) {
            $shift = $shift->where('jam_from', $request->jam_from);
        }

        if ($request->has('jam_to')) {
            $shift = $shift->where('jam_to', $request->jam_to);
        }

        // Search
        if ($request->has('search')) {
            $shift = $shift->where('nama', 'like', '%' . $request->search . '%');
        }

        // Sort
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort);
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $shift = $shift->orderBy($sortField, $sortOrder);
            }
        }

        $dataShift = $shift->paginate(10);

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

    public function update(Shift $shift, UpdateShiftRequest $request)
    {
        if (!Gate::allows('edit shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $shift->update($data);
        $updatedShift = $shift->fresh();
        $successMessage = "Data shift '{$updatedShift->nama}' berhasil diubah.";
        return response()->json(new ShiftResource(Response::HTTP_OK, $successMessage, $shift), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKompetensi = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:unit_kerjas,id'
        ]);

        if ($dataKompetensi->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataKompetensi->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        Shift::destroy($ids);

        $deletedCount = Shift::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Data shift berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportShift(Request $request)
    {
        if (!Gate::allows('export shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        try {
            $ids = $request->input('ids', []);
            return Excel::download(new ShiftExport($ids), 'shifts.xlsx');
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
