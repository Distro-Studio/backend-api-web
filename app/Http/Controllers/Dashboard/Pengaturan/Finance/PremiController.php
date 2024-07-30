<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use App\Models\Premi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\Pengaturan\Finance\PremiExport;
use App\Http\Requests\Excel_Import\ImportPremiRequest;
use App\Http\Requests\StorePremiRequest;
use App\Http\Requests\UpdatePremiRequest;
use App\Http\Resources\Dashboard\Pengaturan_Finance\PremiResource;
use App\Imports\Pengaturan\Finance\PremiImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PremiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllPremi()
    {
        if (!Gate::allows('view premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPremi = Premi::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all premi for dropdown',
            'data' => $dataPremi
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi = Premi::query();

        // Filter
        $softDeleteFilters = $request->input('delete_data', []);
        if (in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters)) {
            $premi->withTrashed();
        } elseif (in_array('dihapus', $softDeleteFilters)) {
            $premi->onlyTrashed();
        } else {
            $premi->withoutTrashed();
        }

        $jenisPremiFilters = $request->input('jenis_premi', []);
        if (!empty($jenisPremiFilters)) {
            $premi->whereIn('jenis_premi', $jenisPremiFilters);
        }

        // Search
        if ($request->has('search')) {
            $premi = $premi->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhere('nama_premi', 'like', $searchTerm);
            });
        }

        $dataPremi = $premi->get();
        if ($dataPremi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data premi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new PremiResource(Response::HTTP_OK, 'Data premi berhasil ditampilkan.', $dataPremi), Response::HTTP_OK);
    }

    public function store(StorePremiRequest $request)
    {
        if (!Gate::allows('create premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $premi = Premi::create($data);
        $successMessage = "Data premi '{$premi->nama_premi}' berhasil dibuat.";
        return response()->json(new PremiResource(Response::HTTP_OK, $successMessage, $premi), Response::HTTP_OK);
    }

    public function show(Premi $premi)
    {
        if (!Gate::allows('view premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$premi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data premi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new PremiResource(Response::HTTP_OK, 'Data premi berhasil ditampilkan.', $premi), Response::HTTP_OK);
    }

    public function update(Premi $premi, UpdatePremiRequest $request)
    {
        if (!Gate::allows('edit premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $premi->update($data);
        $updatedPremi = $premi->fresh();
        $successMessage = "Data premi '{$updatedPremi->nama_premi}' berhasil diubah.";
        return response()->json(new PremiResource(Response::HTTP_OK, $successMessage, $premi), Response::HTTP_OK);
    }

    public function destroy(Premi $premi)
    {
        if (!Gate::allows('delete premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi->delete();

        $successMessage = 'Data premi berhasil dihapus.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $premi = Premi::withTrashed()->find($id);

        if (!Gate::allows('delete premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi->restore();

        if (is_null($premi->deleted_at)) {
            $successMessage = "Data premi {$premi->premis->nama_premi} berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }
    public function exportPremi(Request $request)
    {
        if (!Gate::allows('export premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new PremiExport(), 'data-premi.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data premi berhasil di download.'), Response::HTTP_OK);
    }

    public function importPremi(ImportPremiRequest $request)
    {
        if (!Gate::allows('import premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new PremiImport, $file['premi_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data premi berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
