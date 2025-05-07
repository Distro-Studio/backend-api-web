<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\KategoriPendidikan;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StorePendidikanRequest;
use App\Http\Requests\UpdatePendidikanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PendidikanTerakhirController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view pendidikan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pendidikan = KategoriPendidikan::withTrashed()->orderBy('created_at', 'desc');

        $datapendidikan = $pendidikan->get();
        if ($datapendidikan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data pendidikan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data pendidikan berhasil ditampilkan.";
        $formattedData = $this->formatData($datapendidikan);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StorePendidikanRequest $request)
    {
        if (!Gate::allows('create pendidikan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $pendidikan = KategoriPendidikan::create($data);
        $successMessage = "Data pendidikan '{$pendidikan->label}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$pendidikan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(KategoriPendidikan $pendidikan)
    {
        if (!Gate::allows('view pendidikan', $pendidikan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$pendidikan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data unit kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data pendidikan '{$pendidikan->label}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$pendidikan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdatePendidikanRequest $request)
    {
        $pendidikan = KategoriPendidikan::withTrashed()->find($id);

        if (!Gate::allows('edit pendidikan', $pendidikan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $pendidikan->update($data);
        $updated_pendidikan = $pendidikan->fresh();
        $successMessage = "Data pendidikan '{$updated_pendidikan->label}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$pendidikan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(KategoriPendidikan $pendidikan)
    {
        if (!Gate::allows('delete pendidikan', $pendidikan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pendidikan->delete();

        $successMessage = "Data pendidikan {$pendidikan->label} berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $pendidikan = KategoriPendidikan::withTrashed()->find($id);

        if (!Gate::allows('edit pendidikan', $pendidikan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pendidikan->restore();

        if (is_null($pendidikan->deleted_at)) {
            $successMessage = "Data pendidikan '{$pendidikan->label}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $successMessage), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($pendidikan) {
            return [
                'id' => $pendidikan->id,
                'label' => $pendidikan->label,
                'deleted_at' => $pendidikan->deleted_at,
                'created_at' => $pendidikan->created_at,
                'updated_at' => $pendidikan->updated_at
            ];
        });
    }
}
