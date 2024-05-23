<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Kompetensi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreKompetensiRequest;
use App\Http\Requests\UpdateKompetensiRequest;
use App\Exports\Pengaturan\Karyawan\KompetensiExport;
use App\Http\Requests\Excel_Import\ImportKompetensiRequest;
use App\Imports\Pengaturan\Karyawan\KompetensiImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\KompetensiResource;

class KompetensiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllKompetensi()
    {
        if (!Gate::allows('view kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kompetensi = Kompetensi::get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kompetensi for dropdown',
            'data' => $kompetensi
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        $kompetensi = Kompetensi::query();

        // Filter
        if ($request->has('jenis_kompetensi')) {
            $kompetensi = $kompetensi->where('jenis_kompetensi', $request->jenis_kompetensi);
        }

        // Search
        if ($request->has('search')) {
            $kompetensi = $kompetensi->where('nama_kompetensi', 'like', '%' . $request->search . '%')
                ->orWhere('jenis_kompetensi', 'like', '%' . $request->search . '%');
        }

        $dataKompetensi = $kompetensi->paginate(10);

        if ($dataKompetensi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kompetensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new KompetensiResource(Response::HTTP_OK, 'Data kompetensi berhasil ditampilkan.', $dataKompetensi), Response::HTTP_OK);
    }

    public function store(StoreKompetensiRequest $request)
    {
        if (!Gate::allows('create kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kompetensi = Kompetensi::create($data);
        $successMessage = "Data kompetensi '{$kompetensi->nama_kompetensi}' berhasil dibuat.";
        return response()->json(new KompetensiResource(Response::HTTP_OK, $successMessage, $kompetensi), Response::HTTP_OK);
    }

    public function update(Kompetensi $kompetensi, UpdateKompetensiRequest $request)
    {
        if (!Gate::allows('edit kompetensi', $kompetensi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kompetensi->update($data);
        $updatedKompetensi = $kompetensi->fresh();
        $successMessage = "Data kompetensi '{$updatedKompetensi->nama_kompetensi}' berhasil diubah.";
        return response()->json(new KompetensiResource(Response::HTTP_OK, $successMessage, $kompetensi), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete kompetensi')) {
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

        $message = 'Data kompetensi berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportKompetensi(Request $request)
    {
        if (!Gate::allows('export kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new KompetensiExport($ids), 'kompetensis.xlsx');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data kompetensi berhasil di download.'), Response::HTTP_OK);
    }

    public function importKompetensi(ImportKompetensiRequest $request)
    {
        if (!Gate::allows('import kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new KompetensiImport, $file['kompetensi_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data kompetensi berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
