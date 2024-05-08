<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreUnitKerjaRequest;
use App\Http\Requests\UpdateUnitKerjaRequest;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Exports\Pengaturan\Karyawan\UnitKerjaExport;
use App\Imports\Pengaturan\Karyawan\UnitKerjaImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\UnitKerjaResource;

class UnitKerjaController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllUnitKerja()
    {
        if (!Gate::allows('view.unitkerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja = UnitKerja::get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Unit Kerja for dropdown',
            'data' => $unit_kerja
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        // $this->middleware(RoleMiddleware::class, ['roles' => ['Super Admin']]);
        if (!Gate::allows('view.unitkerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja = UnitKerja::query();

        // Filter
        if ($request->has('jenis_karyawan')) {
            $unit_kerja = $unit_kerja->where('jenis_karyawan', $request->jenis_karyawan);
        }

        // Search
        if ($request->has('search')) {
            $unit_kerja = $unit_kerja->where('nama_unit', 'like', '%' . $request->search . '%');
        }

        // Sort
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort);
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $unit_kerja = $unit_kerja->orderBy($sortField, $sortOrder);
            }
        }

        $dataUnitKerja = $unit_kerja->paginate(10);

        if ($dataUnitKerja->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Unit Kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new UnitKerjaResource(Response::HTTP_OK, 'Data Unit Kerja berhasil ditampilkan.', $dataUnitKerja), Response::HTTP_OK);
    }

    public function store(StoreUnitKerjaRequest $request)
    {
        if (!Gate::allows('create.unitkerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $unit_kerja = UnitKerja::create($data);
        $successMessage = "Data Unit Kerja '{$unit_kerja->nama_unit}' berhasil dibuat.";
        return response()->json(new UnitKerjaResource(Response::HTTP_OK, $successMessage, $unit_kerja), Response::HTTP_OK);
    }

    public function update(UnitKerja $UnitKerja, UpdateUnitKerjaRequest $request)
    {
        if (!Gate::allows('edit.unitkerja', $UnitKerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $UnitKerja->update($data);
        $updatedUnitKerja = $UnitKerja->fresh();
        $successMessage = "Data Unit Kerja '{$updatedUnitKerja->nama_unit}' berhasil diubah.";
        return response()->json(new UnitKerjaResource(Response::HTTP_OK, $successMessage, $UnitKerja), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete.unitkerja')) {
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
        UnitKerja::destroy($ids);

        $deletedCount = UnitKerja::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Unit Kerja berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportUnitKerja()
    {
        if (!Gate::allows('export.unitkerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        $UnitKerja = UnitKerja::all();
        return Excel::download(new UnitKerjaExport, 'unit-kerjas.xlsx');
    }

    public function importUnitKerja(Request $request)
    {
        if (!Gate::allows('import.unitkerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            Excel::import(new UnitKerjaImport, $request->file('unit_kerja_file'));
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data Unit Kerja berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
