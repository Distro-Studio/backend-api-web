<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreSpesialisasiRequest;
use App\Http\Requests\UpdateSpesialisasiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\Spesialisasi;
use Illuminate\Http\Request;

class SpesialisasiController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view spesialisasi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $spesialisasi = Spesialisasi::withTrashed()->orderBy('created_at', 'desc');

        $dataSpesialisasi = $spesialisasi->get();
        if ($dataSpesialisasi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data spesialisasi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data spesialisasi berhasil ditampilkan.";
        $formattedData = $this->formatData($dataSpesialisasi);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreSpesialisasiRequest $request)
    {
        if (!Gate::allows('create spesialisasi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $spesialisasi = Spesialisasi::create($data);
        $successMessage = "Data spesialisasi '{$spesialisasi->nama_spesialisasi}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$spesialisasi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Spesialisasi $spesialisasi)
    {
        if (!Gate::allows('view spesialisasi', $spesialisasi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$spesialisasi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data spesialisasi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data spesialisasi '{$spesialisasi->nama_spesialisasi}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$unit_kerja]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateSpesialisasiRequest $request)
    {
        $spesialisasi = Spesialisasi::withTrashed()->find($id);

        if (!Gate::allows('edit spesialisasi', $spesialisasi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $spesialisasi->update($data);
        $updatedSpesialisasi = $spesialisasi->fresh();
        $successMessage = "Data spesialisasi '{$updatedSpesialisasi->nama_spesialisasi}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$spesialisasi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Spesialisasi $spesialisasi)
    {
        if (!Gate::allows('delete spesialisasi', $spesialisasi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $spesialisasi->delete();

        $successMessage = "Data spesialisasi {$spesialisasi->nama_spesialisasi} berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $spesialisasi = Spesialisasi::withTrashed()->find($id);

        if (!Gate::allows('delete spesialisasi', $spesialisasi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $spesialisasi->restore();

        if (is_null($spesialisasi->deleted_at)) {
            $successMessage = "Data spesialisasi '{$spesialisasi->nama_unit}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($spesialisasi) {
            return [
                'id' => $spesialisasi->id,
                'nama_spesialisasi' => $spesialisasi->nama_spesialisasi,
                'deleted_at' => $spesialisasi->deleted_at,
                'created_at' => $spesialisasi->created_at,
                'updated_at' => $spesialisasi->updated_at
            ];
        });
    }
}
