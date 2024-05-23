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
use App\Exports\Pengaturan\Karyawan\JabatanExport;
use App\Http\Requests\Excel_Import\ImportJabatanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\JabatanResource;
use App\Imports\Pengaturan\Karyawan\JabatanImport;

class JabatanController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllJabatan()
    {
        if (!Gate::allows('view jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataJabatan = Jabatan::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all jabatan for dropdown',
            'data' => $dataJabatan
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan = Jabatan::query();

        // Filter
        if ($request->has('is_struktural')) {
            $jabatan = $jabatan->where('is_struktural', $request->is_struktural);
        }

        // Search
        if ($request->has('search')) {
            $jabatan = $jabatan->where('nama_jabatan', 'like', '%' . $request->search . '%');
        }

        $dataJabatan = $jabatan->paginate(10);

        if ($dataJabatan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jabatan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new JabatanResource(Response::HTTP_OK, 'Data jabatan berhasil ditampilkan.', $dataJabatan), Response::HTTP_OK);
    }

    public function store(StoreJabatanRequest $request)
    {
        if (!Gate::allows('create jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jabatan = Jabatan::create($data);
        $successMessage = "Data jabatan '{$jabatan->nama_jabatan}' berhasil dibuat.";
        return response()->json(new JabatanResource(Response::HTTP_OK, $successMessage, $jabatan), Response::HTTP_OK);
    }

    public function update(Jabatan $jabatan, UpdateJabatanRequest $request)
    {
        if (!Gate::allows('edit jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jabatan->update($data);
        $updatedJabatan = $jabatan->fresh();
        $successMessage = "Data jabatan '{$updatedJabatan->nama_jabatan}' berhasil diubah.";
        return response()->json(new JabatanResource(Response::HTTP_OK, $successMessage, $jabatan), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete jabatan')) {
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

        $message = 'Data jabatan berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportJabatan(Request $request)
    {
        if (!Gate::allows('export jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new JabatanExport($ids), 'jabatans.xlsx');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jabatan berhasil di download.'), Response::HTTP_OK);
    }

    public function importJabatan(ImportJabatanRequest $request)
    {
        if (!Gate::allows('import jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new JabatanImport, $file['jabatan_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jabatan berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
