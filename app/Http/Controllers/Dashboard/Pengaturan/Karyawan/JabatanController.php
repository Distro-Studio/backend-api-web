<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreJabatanRequest;
use App\Http\Requests\UpdateJabatanRequest;
use App\Exports\Pengaturan\Karyawan\Jabatan\JabatanExport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\JabatanResource;

class JabatanController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllJabatan()
    {
        $dataJabatan = Jabatan::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Jabatan for dropdown',
            'data' => $dataJabatan
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view.jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan = Jabatan::query();

        // Filter data Jabatan berdasarkan parameter 'is_struktural'
        if ($request->has('is_struktural')) {
            $jabatan = $jabatan->where('is_struktural', $request->is_struktural);
        }

        // Terapkan pencarian jika parameter 'search' ada
        if ($request->has('search')) {
            $jabatan = $jabatan->where('nama_jabatan', 'like', '%' . $request->search . '%');
        }

        // Urutkan data Jabatan
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort); // Pecah parameter 'sort' menjadi array
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $jabatan = $jabatan->orderBy($sortField, $sortOrder);
            }
        }

        $dataJabatan = $jabatan->paginate(10);

        if ($dataJabatan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Jabatan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new JabatanResource(Response::HTTP_OK, 'Data Jabatan berhasil ditampilkan.', $dataJabatan), Response::HTTP_OK);
    }

    public function store(StoreJabatanRequest $request)
    {
        if (!Gate::allows('create.jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jabatan = Jabatan::create($data);
        $successMessage = "Data Jabatan '{$jabatan->nama_jabatan}' berhasil dibuat.";
        return response()->json(new JabatanResource(Response::HTTP_OK, $successMessage, $jabatan), Response::HTTP_OK);
    }

    public function update(Jabatan $jabatan, UpdateJabatanRequest $request)
    {
        if (!Gate::allows('edit.jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jabatan->update($data);
        $updatedJabatan = $jabatan->fresh();
        $successMessage = "Data Jabatan '{$updatedJabatan->nama_jabatan}' berhasil diubah.";
        return response()->json(new JabatanResource(Response::HTTP_OK, $successMessage, $jabatan), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete.jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataJabatan = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:jabatans,id'
        ]);

        if ($dataJabatan->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataJabatan->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        Jabatan::destroy($ids);

        $deletedCount = Jabatan::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Jabatan berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportJabatan()
    {
        if (!Gate::allows('export.jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatans = Jabatan::all();
        return Excel::download(new JabatanExport, 'jabatans.xlsx');
    }

    // public function importJabatan(Request $request)
    // {
    // if (!Gate::allows('import.jabatan')) {
    //     return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    // }

    //     $import = Excel::import(new JabatanImport, $request->file('jabatan_file'));

    //     if ($import->failures()->count() > 0) {
    //         // Handle import failures with validation errors (consider logging specific errors)
    //         return response()->json($import->failures(), Response::HTTP_UNPROCESSABLE_ENTITY);
    //     }

    //     // More informative success message
    //     $message = 'Data Jabatan berhasil di import ' . $import->count() . ' record(s) kedalam table.';
    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    // }
}
