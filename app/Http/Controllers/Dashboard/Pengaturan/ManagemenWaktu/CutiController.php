<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\Cuti;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreCutiRequest;
use App\Http\Requests\UpdateCutiRequest;
use Illuminate\Support\Facades\Validator;
use App\Exports\Pengaturan\Managemen_Waktu\CutiExport;
use App\Http\Requests\Excel_Import\ImportCutiRequest;
use App\Imports\Pengaturan\Managemen_Waktu\CutiImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Managemen_Waktu\CutiResource;
use App\Models\TipeCuti;

class CutiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllTipeCuti()
    {
        if (!Gate::allows('view cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti = TipeCuti::get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all cuti for dropdown',
            'data' => $cuti
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti = TipeCuti::query();

        // Filter
        if ($request->has('durasi_min')) {
            if (is_array($request->durasi_min)) {
                $cuti->where(function ($query) use ($request) {
                    foreach ($request->durasi_min as $min) {
                        $query->orWhere('durasi', '>', $min);
                    }
                });
            } else {
                $cuti->where('durasi', '>', $request->durasi_min);
            }
        }

        if ($request->has('durasi_max')) {
            if (is_array($request->durasi_max)) {
                $cuti->where(function ($query) use ($request) {
                    foreach ($request->durasi_max as $min) {
                        $query->orWhere('durasi', '<', $min);
                    }
                });
            } else {
                $cuti->where('durasi', '<', $request->durasi_max);
            }
        }

        if ($request->has('durasi_min') && $request->has('durasi_max')) {
            if (is_array($request->durasi_min) && is_array($request->durasi_max)) {
                $cuti->where(function ($query) use ($request) {
                    foreach ($request->durasi_min as $index => $min) {
                        if (isset($request->durasi_max[$index])) {
                            $query->orWhereBetween('durasi', [$min, $request->durasi_max[$index]]);
                        }
                    }
                });
            } else if (!is_array($request->durasi_min) && !is_array($request->durasi_max)) {
                $cuti->whereBetween('durasi', [$request->durasi_min, $request->durasi_max]);
            } else {
                // Handle case where one is array and the other is not, if needed
                // Example: Assume single durasi_min and multiple durasi_max, or vice versa
                if (is_array($request->durasi_min)) {
                    $cuti->where(function ($query) use ($request) {
                        foreach ($request->durasi_min as $min) {
                            $query->orWhere('durasi', '>=', $min);
                        }
                    });
                } else {
                    $cuti->where('durasi', '>=', $request->durasi_min);
                }

                if (is_array($request->durasi_max)) {
                    $cuti->where(function ($query) use ($request) {
                        foreach ($request->durasi_max as $max) {
                            $query->orWhere('durasi', '<=', $max);
                        }
                    });
                } else {
                    $cuti->where('durasi', '<=', $request->durasi_max);
                }
            }
        }

        // Search
        if ($request->has('search')) {
            $cuti = $cuti->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhere('nama', 'like', $searchTerm);
            });
        }

        $dataCuti = $cuti->paginate(10);
        if ($dataCuti->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new CutiResource(Response::HTTP_OK, 'Data cuti berhasil ditampilkan.', $dataCuti), Response::HTTP_OK);
    }

    public function store(StoreCutiRequest $request)
    {
        if (!Gate::allows('create cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $cuti = TipeCuti::create($data);
        $successMessage = "Data cuti berhasil dibuat.";
        return response()->json(new CutiResource(Response::HTTP_OK, $successMessage, $cuti), Response::HTTP_OK);
    }

    public function show(TipeCuti $cuti)
    {
        if (!Gate::allows('view cuti', $cuti)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$cuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new CutiResource(Response::HTTP_OK, 'Data cuti ditemukan.', $cuti), Response::HTTP_OK);
    }

    public function update(TipeCuti $cuti, UpdateCutiRequest $request)
    {
        if (!Gate::allows('edit.hariLibur', $cuti)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $cuti->update($data);
        $updatedCuti = $cuti->fresh();
        $successMessage = "Data cuti '{$updatedCuti->nama}' berhasil diubah.";
        return response()->json(new CutiResource(Response::HTTP_OK, $successMessage, $cuti), Response::HTTP_OK);
    }

    // public function bulkDelete(Request $request)
    // {
    //     if (!Gate::allows('delete cuti')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $dataCuti = Validator::make($request->all(), [
    //         'ids' => 'required|array|min:1',
    //         'ids.*' => 'integer|exists:tipe_cutis,id'
    //     ]);

    //     if ($dataCuti->fails()) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataCuti->errors()), Response::HTTP_BAD_REQUEST);
    //     }

    //     $ids = $request->input('ids');
    //     TipeCuti::destroy($ids);

    //     $deletedCount = TipeCuti::whereIn('id', $ids)->delete();

    //     $message = 'Data cuti berhasil dihapus.';

    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    // }

    public function exportCuti(Request $request)
    {
        if (!Gate::allows('export cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new CutiExport(), 'data-tipe-cuti.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data cuti berhasil di download.'), Response::HTTP_OK);
    }

    public function importCuti(ImportCutiRequest $request)
    {
        if (!Gate::allows('import cuti')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new CutiImport, $file['cuti_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data cuti berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
