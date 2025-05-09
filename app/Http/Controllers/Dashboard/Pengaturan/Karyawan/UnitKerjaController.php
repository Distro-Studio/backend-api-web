<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\UnitKerja;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreUnitKerjaRequest;
use App\Http\Requests\UpdateUnitKerjaRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class UnitKerjaController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja = UnitKerja::withTrashed()->orderBy('created_at', 'desc');

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
                'kategori_unit' => $unit_kerja->kategori_unit,
                'deleted_at' => $unit_kerja->deleted_at,
                'created_at' => $unit_kerja->created_at,
                'updated_at' => $unit_kerja->updated_at
            ];
        });
    }
}
