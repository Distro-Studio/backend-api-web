<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreUnitKerjaRequest;
use App\Http\Requests\UpdateUnitKerjaRequest;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Exports\Pengaturan\Karyawan\UnitKerjaExport;
use App\Imports\Pengaturan\Karyawan\UnitKerjaImport;
use App\Http\Requests\Excel_Import\ImportUnitKerjaRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class UnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja = UnitKerja::withTrashed()->orderBy('created_at', 'desc');

        // Filter
        // if ($request->has('delete_data')) {
        //     $softDeleteFilters = $request->delete_data;
        //     $unit_kerja->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
        //         return $query->onlyTrashed();
        //     })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
        //         return $query->withoutTrashed();
        //     });
        // }

        // Search
        // if ($request->has('search')) {
        //     $unit_kerja = $unit_kerja->where(function ($query) use ($request) {
        //         $searchTerm = '%' . $request->search . '%';

        //         $query->orWhere('nama_unit', 'like', $searchTerm);
        //     });
        // }

        $dataUnitKerja = $unit_kerja->get();
        if ($dataUnitKerja->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data unit kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data unit kerja berhasil ditampilkan.";
        $formattedData = $this->formatData($dataUnitKerja);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreUnitKerjaRequest $request)
    {
        if (!Gate::allows('create unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $unit_kerja = UnitKerja::create($data);
        $successMessage = "Data unit kerja '{$unit_kerja->nama_unit}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$unit_kerja]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(UnitKerja $unit_kerja)
    {
        if (!Gate::allows('view unitKerja', $unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$unit_kerja) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data unit kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data unit kerja '{$unit_kerja->nama_unit}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$unit_kerja]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateUnitKerjaRequest $request)
    {
        $unit_kerja = UnitKerja::withTrashed()->find($id);

        if (!Gate::allows('edit unitKerja', $unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        
        // Validasi unique untuk nama_unit
        $existingDataValidation = UnitKerja::where('nama_unit', $data['nama_unit'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama unit kerja tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $unit_kerja->update($data);
        $updatedUnitKerja = $unit_kerja->fresh();
        $successMessage = "Data unit kerja '{$updatedUnitKerja->nama_unit}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$unit_kerja]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(UnitKerja $unit_kerja)
    {
        if (!Gate::allows('delete unitKerja', $unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja->delete();

        $successMessage = "Data unit kerja {$unit_kerja->nama_unit} berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $unit_kerja = UnitKerja::withTrashed()->find($id);

        if (!Gate::allows('delete unitKerja', $unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja->restore();

        if (is_null($unit_kerja->deleted_at)) {
            $successMessage = "Data unit kerja '{$unit_kerja->nama_unit}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($unit_kerja) {
            return [
                'id' => $unit_kerja->id,
                'nama_unit' => $unit_kerja->nama_unit,
                'jenis_karyawan' => $unit_kerja->jenis_karyawan,
                'deleted_at' => $unit_kerja->deleted_at,
                'created_at' => $unit_kerja->created_at,
                'updated_at' => $unit_kerja->updated_at
            ];
        });
    }
}
