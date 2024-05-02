<?php

namespace App\Http\Controllers\SuperAdmin\Pengaturan\Karyawan\Kompetensi;

use App\Models\Kompetensi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreKompetensiRequest;
use App\Http\Requests\UpdateKompetensiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Exports\Pengaturan\Karyawan\Kompetensi\KompetensiExport;
use App\Imports\Pengaturan\Karyawan\Kompetensi\KompetensiImport;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\Kompetensi\KompetensiResource;

class SA_KompetensiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllKompetensi()
    {
        $kompetensi = Kompetensi::get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Kompetensi for dropdown',
            'data' => $kompetensi
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view.kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        $kompetensi = Kompetensi::query();

        // Filter data Jabatan berdasarkan parameter 'kode-gaji'
        if ($request->has('jenis_kompetensi')) {
            $kompetensi = $kompetensi->where('jenis_kompetensi', $request->jenis_kompetensi);
        }

        // Terapkan pencarian jika parameter 'search' ada
        if ($request->has('search')) {
            $kompetensi = $kompetensi->where('nama_kompetensi', 'like', '%' . $request->search . '%')
                ->orWhere('jenis_kompetensi', 'like', '%' . $request->search . '%');
        }

        // Urutkan data Jabatan
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort); // Pecah parameter 'sort' menjadi array
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $kompetensi = $kompetensi->orderBy($sortField, $sortOrder);
            }
        }

        $dataKompetensi = $kompetensi->paginate(10);

        if ($dataKompetensi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Kompetensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new KompetensiResource(Response::HTTP_OK, 'Data Kompetensi berhasil ditampilkan.', $dataKompetensi), Response::HTTP_OK);
    }

    public function store(StoreKompetensiRequest $request)
    {
        if (!Gate::allows('create.kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kompetensi = Kompetensi::create($data);
        $successMessage = "Data Kompetensi '{$kompetensi->nama_kompetensi}' berhasil dibuat.";
        return response()->json(new KompetensiResource(Response::HTTP_OK, $successMessage, $kompetensi), Response::HTTP_OK);
    }

    public function update(Kompetensi $kompetensi, UpdateKompetensiRequest $request)
    {
        if (!Gate::allows('edit.kompetensi', $kompetensi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kompetensi->update($data);
        $updatedKompetensi = $kompetensi->fresh();
        $successMessage = "Data Kompetensi '{$updatedKompetensi->nama_kompetensi}' berhasil diubah.";
        return response()->json(new KompetensiResource(Response::HTTP_OK, $successMessage, $kompetensi), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete.kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKompetensi = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:kompetensis,id'
        ]);

        if ($dataKompetensi->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataKompetensi->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        Kompetensi::destroy($ids);

        $deletedCount = Kompetensi::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Kompetensi berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportKompetensi()
    {
        if (!Gate::allows('export.kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $Kompetensi = Kompetensi::all();
        return Excel::download(new KompetensiExport, 'kompetensis.xlsx');
    }

    // public function importKompetensi(Request $request)
    // {
    //     if (!Gate::allows('import.kompetensi')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $import = Excel::import(new KompetensiImport, $request->file('kompetensi_file'));

    //     if ($import->failures()->count() > 0) {
    //         // Handle import failures with validation errors (consider logging specific errors)
    //         return response()->json($import->failures(), Response::HTTP_UNPROCESSABLE_ENTITY);
    //     }

    //     // More informative success message
    //     $message = 'Data Kompetensi ' . $import->count() . ' record(s) berhasil di import kedalam table.';
    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    // }
}
