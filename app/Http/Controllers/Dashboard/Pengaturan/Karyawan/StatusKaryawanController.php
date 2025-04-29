<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStatusKaryawanRequest;
use App\Http\Requests\UpdateStatusKaryawanRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\StatusKaryawan;
use Google\Service\AIPlatformNotebooks\Status;

class StatusKaryawanController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view statusKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $statusKaryawan = StatusKaryawan::withTrashed()->orderBy('created_at', 'desc');

        $dataStatusKaryawan = $statusKaryawan->get();
        if ($dataStatusKaryawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data status karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data status karyawan berhasil ditampilkan.";
        $formattedData = $this->formatData($dataStatusKaryawan);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreStatusKaryawanRequest $request)
    {
        if (!Gate::allows('create statusKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $statusKaryawan = StatusKaryawan::create($data);
        $successMessage = "Data status karyawan '{$statusKaryawan->label}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$statusKaryawan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(StatusKaryawan $statusKaryawan)
    {
        if (!Gate::allows('view statusKaryawan', $statusKaryawan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$statusKaryawan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data status karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data status karyawan '{$statusKaryawan->label}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$statusKaryawan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateStatusKaryawanRequest $request)
    {
        $statusKaryawan = StatusKaryawan::withTrashed()->find($id);

        if (!Gate::allows('edit statusKaryawan', $statusKaryawan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = StatusKaryawan::where('label', $data['label'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama status karyawan tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $statusKaryawan->update($data);
        $updatedStatusKaryawan = $statusKaryawan->fresh();
        $successMessage = "Data status karyawan '{$updatedStatusKaryawan->label}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$updatedStatusKaryawan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(StatusKaryawan $statusKaryawan)
    {
        if (!Gate::allows('delete statusKaryawan', $statusKaryawan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $statusKaryawan->delete();

        $successMessage = "Data status karyawan '{$statusKaryawan->label}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $statusKaryawan = StatusKaryawan::withTrashed()->find($id);

        if (!Gate::allows('delete statusKaryawan', $statusKaryawan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $statusKaryawan->restore();

        if (is_null($statusKaryawan->deleted_at)) {
            $successMessage = "Data status karyawan '{$statusKaryawan->label}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($statusKaryawan) {
            return [
                'id' => $statusKaryawan->id,
                'label' => $statusKaryawan->label,
                'kategori_status' => $statusKaryawan->kategori_status,
                'deleted_at' => $statusKaryawan->deleted_at,
                'created_at' => $statusKaryawan->created_at,
                'updated_at' => $statusKaryawan->updated_at
            ];
        });
    }
}
