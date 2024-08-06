<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Pertanyaan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StorePertanyaanRequest;
use App\Http\Requests\UpdatePertanyaanRequest;
use App\Exports\Pengaturan\Karyawan\PertanyaanExport;
use App\Imports\Pengaturan\Karyawan\PertanyaanImport;
use App\Http\Requests\Excel_Import\ImportPertanyaanRequest;
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

    public function index(Request $request)
    {
        if (!Gate::allows('view kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pertanyaan = Pertanyaan::withTrashed();

        // Filter
        // if ($request->has('delete_data')) {
        //     $softDeleteFilters = $request->delete_data;
        //     $pertanyaan->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
        //         return $query->onlyTrashed();
        //     })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
        //         return $query->withoutTrashed();
        //     });
        // }
        // if (isset($filters['jabatan'])) {
        //     $namaUnitKerja = $filters['jabatan'];
        //     $pertanyaan->whereHas('jabatans', function ($query) use ($namaUnitKerja) {
        //         if (is_array($namaUnitKerja)) {
        //             $query->whereIn('id', $namaUnitKerja);
        //         } else {
        //             $query->where('id', '=', $namaUnitKerja);
        //         }
        //     });
        // }

        // search
        // if ($request->has('search')) {
        //     $pertanyaan = $pertanyaan->where(function ($query) use ($request) {
        //         $searchTerm = '%' . $request->search . '%';

        //         $query->whereHas('jabatans', function ($query) use ($searchTerm) {
        //             $query->where('nama_jabatan', 'like', $searchTerm);
        //         })->orWhere('pertanyaan', 'like', $searchTerm);
        //     });
        // }

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
        $successMessage = "Data pertanyaan kuesioner untuk jabatan '{$pertanyaan->jabatans->nama_jabatan}' berhasil dibuat.";
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

        $successMessage = "Data pertanyaan kuesioner dari jabatan '{$pertanyaan->jabatans->nama_jabatan}' berhasil ditampilkan.";
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
        $successMessage = "Data pertanyaan kuesioner untuk jabatan '{$updatedPertanyaan->jabatans->nama_jabatan}' diubah.";
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

        $successMessage = "Data pertanyaan kuesioner dari jabatan '{$pertanyaan->jabatans->nama_jabatan}' berhasil dihapus.";
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
            $successMessage = "Data pertanyaan kuesioner dari jabatan '{$pertanyaan->jabatans->nama_jabatan}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($pertanyaan) {
            return [
                'id' => $pertanyaan->id,
                'pertanyaan' => $pertanyaan->pertanyaan,
                'jabatan' => $pertanyaan->jabatans,
                'deleted_at' => $pertanyaan->deleted_at,
                'created_at' => $pertanyaan->created_at,
                'updated_at' => $pertanyaan->updated_at
            ];
        });
    }
}
