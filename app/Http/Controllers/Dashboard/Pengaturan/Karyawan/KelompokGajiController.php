<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\KelompokGaji;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreKelompokGajiRequest;
use App\Http\Requests\UpdateKelompokGajiRequest;
use App\Exports\Pengaturan\Karyawan\KelompokGajiExport;
use App\Http\Requests\Excel_Import\ImportKelompokGajiRequest;
use App\Imports\Pengaturan\Karyawan\KelompokGajiImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\KelompokGajiResource;

class KelompokGajiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllKelompokGaji()
    {
        if (!Gate::allows('view kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kelompk_gaji = KelompokGaji::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kelompok gaji for dropdown',
            'data' => $kelompk_gaji
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kelompok_gaji = KelompokGaji::query();

        // Filter
        if ($request->has('nama_kelompok')) {
            $kelompok_gaji = $kelompok_gaji->where('nama_kelompok', $request->nama_kelompok);
        }

        if ($request->has('range_min')) {
            $kelompok_gaji = $kelompok_gaji->where('besaran_gaji', '>=', $request->range_min);
        }

        if ($request->has('range_max')) {
            $kelompok_gaji = $kelompok_gaji->where('besaran_gaji', '<=', $request->range_max);
        }

        if ($request->has('range_min') && $request->has('range_max')) {
            $kelompok_gaji = $kelompok_gaji->whereBetween('besaran_gaji', [$request->range_min, $request->range_max]);
        }

        // Search
        if ($request->has('search')) {
            $kelompok_gaji = $kelompok_gaji->where('nama_kelompok', 'like', '%' . $request->search . '%');
        }

        $dataKelompokGaji = $kelompok_gaji->paginate(10);

        if ($dataKelompokGaji->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kelompok gaji tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new KelompokGajiResource(Response::HTTP_OK, 'Data kelompok gaji berhasil ditampilkan.', $dataKelompokGaji), Response::HTTP_OK);
    }

    public function store(StoreKelompokGajiRequest $request)
    {
        if (!Gate::allows('create kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kelompk_gaji = KelompokGaji::create($data);
        $successMessage = "Data kelompok gaji '{$kelompk_gaji->nama_kelompok}' berhasil dibuat.";
        return response()->json(new KelompokGajiResource(Response::HTTP_OK, $successMessage, $kelompk_gaji), Response::HTTP_OK);
    }

    public function update(KelompokGaji $KelompokGaji, UpdateKelompokGajiRequest $request)
    {
        if (!Gate::allows('edit kelompokGaji', $KelompokGaji)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $KelompokGaji->update($data);
        $updatedKelompokGaji = $KelompokGaji->fresh();
        $successMessage = "Data kelompok gaji '{$updatedKelompokGaji->nama_kelompok}' berhasil diubah.";
        return response()->json(new KelompokGajiResource(Response::HTTP_OK, $successMessage, $KelompokGaji), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKelompokGaji = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:jabatans,id'
        ]);

        if ($dataKelompokGaji->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataKelompokGaji->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        KelompokGaji::destroy($ids);

        $deletedCount = KelompokGaji::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Data kelompok gaji berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportKelompokGaji(Request $request)
    {
        if (!Gate::allows('export kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new KelompokGajiExport($ids), 'kelompok-gajis.xlsx');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data kelompok gaji berhasil di download.'), Response::HTTP_OK);
    }

    public function importKelompokGaji(ImportKelompokGajiRequest $request)
    {
        if (!Gate::allows('import kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new KelompokGajiImport, $file['kelompok_gaji_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data kelompok gaji berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
