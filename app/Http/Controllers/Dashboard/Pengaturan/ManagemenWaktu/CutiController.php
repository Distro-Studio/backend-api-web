<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\Cuti;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreCutiRequest;
use App\Http\Requests\UpdateCutiRequest;
use Illuminate\Support\Facades\Validator;
use App\Exports\Pengaturan\Managemen_Waktu\CutiExport;
use App\Imports\Pengaturan\Managemen_Waktu\CutiImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Managemen_Waktu\CutiResource;
use App\Models\TipeCuti;

class CutiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllCuti()
    {
        if (!Gate::allows('view.cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti = TipeCuti::get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Cuti for dropdown',
            'data' => $cuti
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        // $this->middleware(RoleMiddleware::class, ['roles' => ['Super Admin']]);
        if (!Gate::allows('view.cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti = TipeCuti::query();

        // Filter
        if ($request->has('durasi')) {
            $cuti = $cuti->where('durasi' , '<=' , $request->durasi);
        }

        // Search
        if ($request->has('search')) {
            $cuti = $cuti->where('nama', 'like', '%' . $request->search . '%');
        }

        // Sort
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort);
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $cuti = $cuti->orderBy($sortField, $sortOrder);
            }
        }

        $dataCuti = $cuti->paginate(10);

        if ($dataCuti->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new CutiResource(Response::HTTP_OK, 'Data Cuti berhasil ditampilkan.', $dataCuti), Response::HTTP_OK);
    }

    public function store(StoreCutiRequest $request)
    {
        if (!Gate::allows('create.cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $cuti = TipeCuti::create($data);
        $successMessage = "Data Cuti berhasil dibuat.";
        return response()->json(new CutiResource(Response::HTTP_OK, $successMessage, $cuti), Response::HTTP_OK);
    }

    public function update(TipeCuti $cuti, UpdateCutiRequest $request)
    {
        if (!Gate::allows('edit.hariLibur', $cuti)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $cuti->update($data);
        $updatedCuti = $cuti->fresh();
        $successMessage = "Data Cuti '{$updatedCuti->nama}' berhasil diubah.";
        return response()->json(new CutiResource(Response::HTTP_OK, $successMessage, $cuti), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete.cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataCuti = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:tipe_cutis,id'
        ]);

        if ($dataCuti->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataCuti->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        TipeCuti::destroy($ids);

        $deletedCount = TipeCuti::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Data Cuti berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportCuti()
    {
        if (!Gate::allows('export.cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        $cuti = Cuti::all();
        return Excel::download(new CutiExport, 'cutis.xlsx');
    }

    public function importCuti(Request $request)
    {
        if (!Gate::allows('import.cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            Excel::import(new CutiImport, $request->file('cuti_file'));
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data Cuti berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
