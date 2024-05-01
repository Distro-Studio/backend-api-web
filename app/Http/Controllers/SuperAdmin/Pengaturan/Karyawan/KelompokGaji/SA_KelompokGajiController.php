<?php

namespace App\Http\Controllers\SuperAdmin\Pengaturan\Karyawan\KelompokGaji;

use App\Exports\Pengaturan\Karyawan\KelompokGaji\KelompokGajiExport;
use App\Models\KelompokGaji;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreKelompokGajiRequest;
use App\Http\Requests\UpdateKelompokGajiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\KelompokGaji\KelompokGajiResource;

class SA_KelompokGajiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllKelompokGaji()
    {
        $kelompk_gaji = KelompokGaji::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all KelompokGaji for dropdown',
            'data' => $kelompk_gaji
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        $kelompok_gaji = KelompokGaji::query();

        // Filter data Jabatan berdasarkan parameter 'kode-gaji'
        if ($request->has('kode-gaji')) {
            $kelompok_gaji = $kelompok_gaji->where('nama_kelompok', $request->nama_kelompok);
        }

        // Terapkan pencarian jika parameter 'search' ada
        if ($request->has('search')) {
            $kelompok_gaji = $kelompok_gaji->where('nama_kelompok', 'like', '%' . $request->search . '%');
        }

        // Urutkan data Jabatan
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort); // Pecah parameter 'sort' menjadi array
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $kelompok_gaji = $kelompok_gaji->orderBy($sortField, $sortOrder);
            }
        }

        $dataKelompokGaji = $kelompok_gaji->paginate(10);

        if ($dataKelompokGaji->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Kelompok Gaji tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new KelompokGajiResource(Response::HTTP_OK, 'Data Kelompok Gaji berhasil ditampilkan.', $dataKelompokGaji), Response::HTTP_OK);
    }

    public function store(StoreKelompokGajiRequest $request)
    {
        $data = $request->validated();

        $kelompk_gaji = KelompokGaji::create($data);
        $successMessage = "Data Kelompok Gaji '{$kelompk_gaji->nama_kelompok}' berhasil dibuat.";
        return response()->json(new KelompokGajiResource(Response::HTTP_OK, $successMessage, $kelompk_gaji), Response::HTTP_OK);
    }

    public function update(KelompokGaji $KelompokGaji, UpdateKelompokGajiRequest $request)
    {
        $data = $request->validated();

        $KelompokGaji->update($data);
        $updatedKelompokGaji = $KelompokGaji->fresh();
        $successMessage = "Data Kelompok Gaji '{$updatedKelompokGaji->nama_kelompok}' berhasil diubah.";
        return response()->json(new KelompokGajiResource(Response::HTTP_OK, $successMessage, $KelompokGaji), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
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

        $message = 'Kelompok Gaji berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportKelompokGaji()
    {
        $KelompokGaji = KelompokGaji::all();
        return Excel::download(new KelompokGajiExport, 'kelompok-gajis.xlsx');
    }

    // public function importJabatan(Request $request)
    // {
    //     $import = Excel::import(new KelompokGajiImport, $request->file('kelompok_gaji_file'));

    //     if ($import->failures()->count() > 0) {
    //         // Handle import failures with validation errors (consider logging specific errors)
    //         return response()->json($import->failures(), Response::HTTP_UNPROCESSABLE_ENTITY);
    //     }

    //     // More informative success message
    //     $message = 'Data Jabatan berhasil di import ' . $import->count() . ' record(s) kedalam table.';
    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    // }
}
