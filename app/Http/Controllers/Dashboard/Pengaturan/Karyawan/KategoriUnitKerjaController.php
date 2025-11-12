<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriUnitKerjaRequest;
use App\Http\Requests\UpdateKategoriUnitKerjaRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\KategoriUnitKerja;

class KategoriUnitKerjaController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kategori_unit_kerja = KategoriUnitKerja::withTrashed()->orderBy('created_at', 'desc');

        $dataUnitKerja = $kategori_unit_kerja->get();
        if ($dataUnitKerja->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kategori unit kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data kategori unit kerja berhasil ditampilkan.";
        $formattedData = $this->formatData($dataUnitKerja);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreKategoriUnitKerjaRequest $request)
    {
        if (!Gate::allows('create unitKerja')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kategori_unit_kerja = KategoriUnitKerja::create($data);
        $successMessage = "Data kategori unit kerja '{$kategori_unit_kerja->nama_unit}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$kategori_unit_kerja]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(KategoriUnitKerja $kategori_unit_kerja)
    {
        if (!Gate::allows('view unitKerja', $kategori_unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$kategori_unit_kerja) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kategori unit kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data kategori unit kerja '{$kategori_unit_kerja->nama_unit}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$kategori_unit_kerja]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateKategoriUnitKerjaRequest $request)
    {
        $kategori_unit_kerja = KategoriUnitKerja::withTrashed()->find($id);

        if (!Gate::allows('edit unitKerja', $kategori_unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kategori_unit_kerja->update($data);
        $updatedUnitKerja = $kategori_unit_kerja->fresh();
        $successMessage = "Data kategori unit kerja '{$updatedUnitKerja->nama_unit}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$kategori_unit_kerja]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(KategoriUnitKerja $kategori_unit_kerja)
    {
        if (!Gate::allows('delete unitKerja', $kategori_unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kategori_unit_kerja->delete();

        $successMessage = "Data kategori unit kerja {$kategori_unit_kerja->nama_unit} berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $kategori_unit_kerja = KategoriUnitKerja::withTrashed()->find($id);

        if (!Gate::allows('delete unitKerja', $kategori_unit_kerja)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kategori_unit_kerja->restore();

        if (is_null($kategori_unit_kerja->deleted_at)) {
            $successMessage = "Data kategori unit kerja '{$kategori_unit_kerja->nama_unit}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($kategori_unit_kerja) {
            return [
                'id' => $kategori_unit_kerja->id,
                'label' => $kategori_unit_kerja->label,
                'deleted_at' => $kategori_unit_kerja->deleted_at,
                'created_at' => $kategori_unit_kerja->created_at,
                'updated_at' => $kategori_unit_kerja->updated_at
            ];
        });
    }
}
