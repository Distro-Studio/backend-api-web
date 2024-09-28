<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Jabatan;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreJabatanRequest;
use App\Http\Requests\UpdateJabatanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class JabatanController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan = Jabatan::withTrashed()->orderBy('created_at', 'desc');

        $dataJabatan = $jabatan->get();
        if ($dataJabatan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jabatan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data jabatan berhasil ditampilkan.";
        $formattedData = $this->formatData($dataJabatan);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreJabatanRequest $request)
    {
        if (!Gate::allows('create jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jabatan = Jabatan::create($data);
        $successMessage = "Data jabatan '{$jabatan->nama_jabatan}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$jabatan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Jabatan $jabatan)
    {
        if (!Gate::allows('view jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$jabatan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jabatan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data jabatan '{$jabatan->nama_jabatan}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$jabatan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateJabatanRequest $request)
    {
        $jabatan = Jabatan::withTrashed()->find($id);

        if (!Gate::allows('edit jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = Jabatan::where('nama_jabatan', $data['nama_jabatan'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama jabatan tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $jabatan->update($data);
        $updatedJabatan = $jabatan->fresh();
        $successMessage = "Data jabatan '{$updatedJabatan->nama_jabatan}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$jabatan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Jabatan $jabatan)
    {
        if (!Gate::allows('delete jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan->delete();

        $successMessage = "Data jabatan '{$jabatan->nama_jabatan}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $jabatan = Jabatan::withTrashed()->find($id);

        if (!Gate::allows('delete jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan->restore();

        if (is_null($jabatan->deleted_at)) {
            $successMessage = "Data jabatan '{$jabatan->nama_jabatan}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($jabatan) {
            return [
                'id' => $jabatan->id,
                'nama_jabatan' => $jabatan->nama_jabatan,
                'is_struktural' => $jabatan->is_struktural,
                // 'tunjangan_jabatan' => $jabatan->tunjangan_jabatan,
                'deleted_at' => $jabatan->deleted_at,
                'created_at' => $jabatan->created_at,
                'updated_at' => $jabatan->updated_at
            ];
        });
    }
}
