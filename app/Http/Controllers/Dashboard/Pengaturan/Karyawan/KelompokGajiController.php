<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\KelompokGaji;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreKelompokGajiRequest;
use App\Http\Requests\UpdateKelompokGajiRequest;
use App\Exports\Pengaturan\Karyawan\KelompokGajiExport;
use App\Imports\Pengaturan\Karyawan\KelompokGajiImport;
use App\Http\Requests\Excel_Import\ImportKelompokGajiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\KelompokGajiResource;

class KelompokGajiController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kelompok_gaji = KelompokGaji::withTrashed();

        // Filter
        if ($request->has('delete_data')) {
            $softDeleteFilters = $request->delete_data;
            $kelompok_gaji->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->onlyTrashed();
            })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->withoutTrashed();
            });
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $kelompok_gaji->where('nama_kelompok', 'like', $searchTerm);
        }

        $dataKelompokGaji = $kelompok_gaji->get();

        if ($dataKelompokGaji->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kelompok gaji tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data kelompok gaji berhasil ditampilkan.";
        $formattedData = $this->formatData($dataKelompokGaji);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreKelompokGajiRequest $request)
    {
        if (!Gate::allows('create kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kelompk_gaji = KelompokGaji::create($data);
        $successMessage = "Data kelompok gaji {$kelompk_gaji->nama_kelompok} berhasil dibuat.";
        $formattedData = $this->formatData(collect([$kelompk_gaji]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(KelompokGaji $kelompok_gaji)
    {
        if (!Gate::allows('view kelompokGaji', $kelompok_gaji)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$kelompok_gaji) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kelompok gaji tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data kelompok gaji {$kelompok_gaji->nama_kelompok} berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$kelompok_gaji]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateKelompokGajiRequest $request)
    {
        $kelompok_gaji = KelompokGaji::withTrashed()->find($id);

        if (!$kelompok_gaji) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kelompok gaji tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        if (!Gate::allows('edit kelompokGaji', $kelompok_gaji)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $kelompok_gaji->update($data);
        $updatedKelompokGaji = $kelompok_gaji->fresh();

        $successMessage = "Data kelompok gaji '{$updatedKelompokGaji->nama_kelompok}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$updatedKelompokGaji]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(KelompokGaji $kelompok_gaji)
    {
        if (!Gate::allows('delete kelompokGaji', $kelompok_gaji)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kelompok_gaji->delete();

        $successMessage = 'Data kelompok gaji berhasil dihapus.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $kelompok_gaji = KelompokGaji::withTrashed()->find($id);

        if (!Gate::allows('delete kelompokGaji', $kelompok_gaji)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kelompok_gaji->restore();

        if (is_null($kelompok_gaji->deleted_at)) {
            $successMessage = "Data kelompok gaji {$kelompok_gaji->nama_kelompok} berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportKelompokGaji()
    {
        if (!Gate::allows('export kelompokGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new KelompokGajiExport(), 'data-kelompok-gaji.xls');
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

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($kelompok_gaji) {
            return [
                'id' => $kelompok_gaji->id,
                'nama_kelompok' => $kelompok_gaji->nama_kelompok,
                'besaran_gaji' => $kelompok_gaji->besaran_gaji,
                'deleted_at' => $kelompok_gaji->deleted_at,
                'created_at' => $kelompok_gaji->created_at,
                'updated_at' => $kelompok_gaji->updated_at
            ];
        });
    }
}
