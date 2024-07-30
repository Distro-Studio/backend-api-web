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
        $softDeleteFilters = $request->input('delete_data', []);
        if (in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters)) {
            $jabatan->withTrashed();
        } elseif (in_array('dihapus', $softDeleteFilters)) {
            $jabatan->onlyTrashed();
        } else {
            $jabatan->withoutTrashed();
        }

        // Search
        if ($request->has('search')) {
            $jabatan = $jabatan->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhere('nama_jabatan', 'like', $searchTerm);
            });
        }

        $dataJabatan = $jabatan->get();
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

    public function show(Jabatan $jabatan)
    {
        if (!Gate::allows('view jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$jabatan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jabatan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new JabatanResource(Response::HTTP_OK, 'Data jabatan berhasil ditampilkan.', $jabatan), Response::HTTP_OK);
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
        return response()->json(new JabatanResource(Response::HTTP_OK, $successMessage, $updatedJabatan), Response::HTTP_OK);
    }

    public function destroy(Jabatan $jabatan)
    {
        if (!Gate::allows('delete jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan->delete();

        $successMessage = 'Data jabatan berhasil dihapus.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $jabatan = Jabatan::withTrashed()->find($id);

        if (!Gate::allows('delete jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan->restore();

        if (is_null($jabatan->deleted_at)) {
            $successMessage = "Data jabatan {$jabatan->nama_jabatan} berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportJabatan(Request $request)
    {
        if (!Gate::allows('export jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new JabatanExport(), 'data-jabatan.xls');
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
