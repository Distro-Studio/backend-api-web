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
use App\Http\Requests\Excel_Import\ImportUnitKerjaRequest;
use App\Imports\Pengaturan\Karyawan\UnitKerjaImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\UnitKerjaResource;

class UnitKerjaController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllUnitKerja()
    {
        if (!Gate::allows('view unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja = UnitKerja::get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all unit kerja for dropdown',
            'data' => $unit_kerja
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        // $this->middleware(RoleMiddleware::class, ['roles' => ['Super Admin']]);
        if (!Gate::allows('view unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja = UnitKerja::query();

        // Filter
        if ($request->has('jenis_karyawan')) {
            if (is_array($request->jenis_karyawan)) {
                $unit_kerja->whereIn('jenis_karyawan', $request->jenis_karyawan);
            } else {
                $unit_kerja->where('jenis_karyawan', $request->jenis_karyawan);
            }
        }

        // Search
        if ($request->has('search')) {
            $unit_kerja = $unit_kerja->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhere('nama_unit', 'like', $searchTerm);
            });
        }

        $dataUnitKerja = $unit_kerja->paginate(10);
        if ($dataUnitKerja->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data unit kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new UnitKerjaResource(Response::HTTP_OK, 'Data unit kerja berhasil ditampilkan.', $dataUnitKerja), Response::HTTP_OK);
    }

    public function store(StoreUnitKerjaRequest $request)
    {
        if (!Gate::allows('create unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $unit_kerja = UnitKerja::create($data);
        $successMessage = "Data unit kerja '{$unit_kerja->nama_unit}' berhasil dibuat.";
        return response()->json(new UnitKerjaResource(Response::HTTP_OK, $successMessage, $unit_kerja), Response::HTTP_OK);
    }

    public function show(UnitKerja $unit_kerja)
    {
        if (!Gate::allows('view unitKerja', $unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$unit_kerja) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data unit kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new UnitKerjaResource(Response::HTTP_OK, 'Data unit kerja berhasil ditampilkan.', $unit_kerja), Response::HTTP_OK);
    }

    public function update(UnitKerja $unit_kerja, UpdateUnitKerjaRequest $request)
    {
        if (!Gate::allows('edit unitKerja', $unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $unit_kerja->update($data);
        $updatedUnitKerja = $unit_kerja->fresh();
        $successMessage = "Data unit kerja '{$updatedUnitKerja->nama_unit}' berhasil diubah.";
        return response()->json(new UnitKerjaResource(Response::HTTP_OK, $successMessage, $unit_kerja), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete unitKerja')) {
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

        $message = 'Data unit kerja berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportUnitKerja(Request $request)
    {
        if (!Gate::allows('export unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new UnitKerjaExport($ids), 'unit-kerja.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data unit kerja berhasil di download.'), Response::HTTP_OK);
    }

    public function importUnitKerja(ImportUnitKerjaRequest $request)
    {
        if (!Gate::allows('import unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new UnitKerjaImport, $file['unit_kerja_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data unit kerja berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
