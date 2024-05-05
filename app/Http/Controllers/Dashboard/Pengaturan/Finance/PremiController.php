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
        $dataPremi = Premi::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Premi for dropdown',
            'data' => $dataPremi
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view.premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi = Premi::query();

        // Filter data Premi berdasarkan parameter 'jenis_premi'
        if ($request->has('jenis_premi')) {
            $premi = $premi->where('jenis_premi', $request->jenis_premi);
        }

        // Terapkan pencarian jika parameter 'search' ada
        if ($request->has('search')) {
            $premi = $premi->where('nama_premi', 'like', '%' . $request->search . '%');
        }

        // Urutkan data Premi
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort); // Pecah parameter 'sort' menjadi array
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $premi = $premi->orderBy($sortField, $sortOrder);
            }
        }

        $dataPremi = $premi->paginate(10);

        if ($dataPremi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Premi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new PremiResource(Response::HTTP_OK, 'Data Premi berhasil ditampilkan.', $dataPremi), Response::HTTP_OK);
    }

    public function store(StorePremiRequest $request)
    {
        if (!Gate::allows('create.premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $premi = Premi::create($data);
        $successMessage = "Data Premi '{$premi->nama_premi}' berhasil dibuat.";
        return response()->json(new PremiResource(Response::HTTP_OK, $successMessage, $premi), Response::HTTP_OK);
    }

    public function update(Premi $premi, UpdatePremiRequest $request)
    {
        if (!Gate::allows('edit.premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $premi->update($data);
        $updatedPremi = $premi->fresh();
        $successMessage = "Data Premi '{$updatedPremi->nama_premi}' berhasil diubah.";
        return response()->json(new PremiResource(Response::HTTP_OK, $successMessage, $premi), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete.premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPremi = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:premis,id'
        ]);

        if ($dataPremi->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataPremi->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        Premi::destroy($ids);

        $deletedCount = Premi::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Data Premi berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportPremi()
    {
        if (!Gate::allows('export.premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premis = Premi::all();
        return Excel::download(new PremiExport, 'premis.xlsx');
    }

    // public function importPremi(Request $request)
    // {
    //     if (!Gate::allows('import.premi')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $import = Excel::import(new PremiImport, $request->file('premi_file'));

    //     if ($import->failures()->count() > 0) {
    //         // Handle import failures with validation errors (consider logging specific errors)
    //         return response()->json($import->failures(), Response::HTTP_UNPROCESSABLE_ENTITY);
    //     }

    //     // More informative success message
    //     $message = 'Data Premi berhasil di import ' . $import->count() . ' record(s) kedalam table.';
    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    // }
}
