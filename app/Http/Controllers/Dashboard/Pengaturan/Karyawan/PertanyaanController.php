<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Pertanyaan;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StorePertanyaanRequest;
use App\Http\Requests\UpdatePertanyaanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PertanyaanController extends Controller
{
    public function getAllPertanyaan()
    {
        if (!Gate::allows('view kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pertanyaan = Pertanyaan::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving data pertanyaan kuesioner.',
            'data' => $pertanyaan
        ], Response::HTTP_OK);
    }

    public function index()
    {
        if (!Gate::allows('view kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pertanyaan = Pertanyaan::withTrashed()
            ->with(['jenis_penilaians.status_karyawans', 'jenis_penilaians.jabatan_penilais', 'jenis_penilaians.jabatan_dinilais'])
            ->orderBy('created_at', 'desc');

        if (!$pertanyaan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kuesioner tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $dataPertanyaan = $pertanyaan->get();
        $successMessage = "Data kuesioner berhasil ditampilkan.";
        $formattedData = $this->formatData($dataPertanyaan);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StorePertanyaanRequest $request)
    {
        if (!Gate::allows('create kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $pertanyaan = Pertanyaan::create($data);
        $successMessage = "Data pertanyaan untuk jenis penilaian '{$pertanyaan->jenis_penilaians->nama}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$pertanyaan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Pertanyaan $pertanyaan)
    {
        if (!Gate::allows('view kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$pertanyaan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data pertanyaan kuesioner tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data pertanyaan untuk jenis penilaian '{$pertanyaan->jenis_penilaians->nama}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$pertanyaan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdatePertanyaanRequest $request)
    {
        $pertanyaan = Pertanyaan::withTrashed()->find($id);

        if (!Gate::allows('edit kuesioner', $pertanyaan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = Pertanyaan::where('pertanyaan', $data['pertanyaan'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kalimat pertanyaan tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $pertanyaan->update($data);
        $updatedPertanyaan = $pertanyaan->fresh();
        $successMessage = "Data pertanyaan untuk jenis penilaian '{$updatedPertanyaan->jenis_penilaians->nama}' berhasil diperbarui.";
        $formattedData = $this->formatData(collect([$pertanyaan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Pertanyaan $pertanyaan)
    {
        if (!Gate::allows('delete kuesioner', $pertanyaan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pertanyaan->delete();

        $successMessage = "Data pertanyaan untuk jenis penilaian '{$pertanyaan->jenis_penilaians->nama}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $pertanyaan = Pertanyaan::withTrashed()->find($id);

        if (!Gate::allows('delete kuesioner', $pertanyaan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pertanyaan->restore();

        if (is_null($pertanyaan->deleted_at)) {
            $successMessage = "Data pertanyaan untuk jenis penilaian '{$pertanyaan->jenis_penilaians->nama}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $successMessage), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($pertanyaan) {
            return [
                'id' => $pertanyaan->id,
                'jenis_penilaian' => $pertanyaan->jenis_penilaians,
                'pertanyaan' => $pertanyaan->pertanyaan,
                'deleted_at' => $pertanyaan->deleted_at,
                'created_at' => $pertanyaan->created_at,
                'updated_at' => $pertanyaan->updated_at
            ];
        });
    }
}
