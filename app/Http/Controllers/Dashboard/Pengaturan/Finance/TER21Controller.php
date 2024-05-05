<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use App\Models\Ter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreTERRequest;
use App\Http\Requests\UpdateTERRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exports\Pengaturan\Finance\TER21Export;
use App\Imports\Pengaturan\Finance\TER21Import;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Finance\TER21Resource;

class TER21Controller extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllTer()
    {
        $dataTer = Ter::with(['kategori_ters', 'ptkps'])->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Ter PPH21 for dropdown',
            'data' => $dataTer
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view.ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ter = Ter::query();

        // Filter data Ter berdasarkan parameter 'jenis_premi'
        if ($request->has('jenis_premi')) {
            $ter = $ter->where('jenis_premi', $request->jenis_premi);
        }

        // Terapkan pencarian jika parameter 'search' ada
        if ($request->has('search')) {
            $ter = $ter->where('nama_premi', 'like', '%' . $request->search . '%');
        }

        // Urutkan data Premi
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort); // Pecah parameter 'sort' menjadi array
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $ter = $ter->orderBy($sortField, $sortOrder);
            }
        }

        $dataTer = $ter->with(['kategori_ters', 'ptkps'])->paginate(10);
        if ($dataTer->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Ter PPH21 tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new TER21Resource(Response::HTTP_OK, 'Data Ter PPH21 berhasil ditampilkan.', $dataTer), Response::HTTP_OK);
    }

    public function store(StoreTERRequest $request)
    {
        if (!Gate::allows('create.ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $ter = Ter::with(['kategori_ters', 'ptkps'])->create($data);
        $successMessage = "Data Ter PPH21 berhasil dibuat.";
        return response()->json(new TER21Resource(Response::HTTP_OK, $successMessage, $ter), Response::HTTP_OK);
    }

    public function update(Ter $ter_pph_21, UpdateTERRequest $request)
    {
        if (!Gate::allows('edit.ter21', $ter_pph_21)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $ter_pph_21->update($data);
        $updatedTer = $ter_pph_21->fresh();
        $successMessage = "Data Ter PPH21 berhasil diubah.";
        return response()->json(new TER21Resource(Response::HTTP_OK, $successMessage, $ter_pph_21), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete.ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPremi = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:ters,id'
        ]);

        if ($dataPremi->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataPremi->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        Ter::destroy($ids);

        $deletedCount = Ter::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Data Ter PPH21 berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportTER()
    {
        if (!Gate::allows('export.ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ter = Ter::all();
        return Excel::download(new TER21Export, 'ter-pph21.xlsx');
    }

    // public function importTER(Request $request)
    // {
    //     if (!Gate::allows('import.ter21')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $import = Excel::import(new TER21Import, $request->file('ter_pph_file'));

    //     if ($import->failures()->count() > 0) {
    //         // Handle import failures with validation errors (consider logging specific errors)
    //         return response()->json($import->failures(), Response::HTTP_UNPROCESSABLE_ENTITY);
    //     }

    //     // More informative success message
    //     $message = 'Data Ter PPH21 berhasil di import kedalam table.';
    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    // }
}
