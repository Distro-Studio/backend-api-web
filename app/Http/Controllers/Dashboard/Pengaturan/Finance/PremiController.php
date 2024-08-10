<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use App\Models\Premi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StorePremiRequest;
use App\Http\Requests\UpdatePremiRequest;
use App\Exports\Pengaturan\Finance\PremiExport;
use App\Imports\Pengaturan\Finance\PremiImport;
use App\Http\Requests\Excel_Import\ImportPremiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PremiController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi = Premi::withTrashed()->orderBy('created_at', 'desc');

        // Filter
        // if ($request->has('delete_data')) {
        //     $softDeleteFilters = $request->delete_data;
        //     $premi->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
        //         return $query->onlyTrashed();
        //     })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
        //         return $query->withoutTrashed();
        //     });
        // }

        // $jenisPremiFilters = $request->input('jenis_premi', []);
        // if (!empty($jenisPremiFilters)) {
        //     $premi->whereIn('jenis_premi', $jenisPremiFilters);
        // }

        // Search
        // if ($request->has('search')) {
        //     $premi = $premi->where(function ($query) use ($request) {
        //         $searchTerm = '%' . $request->search . '%';

        //         $query->orWhere('nama_premi', 'like', $searchTerm);
        //     });
        // }

        $dataPremi = $premi->get();
        if ($dataPremi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data premi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $this->formatData($dataPremi);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data premi berhasil ditampilkan.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StorePremiRequest $request)
    {
        if (!Gate::allows('create premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $premi = Premi::create($data);
        $successMessage = "Data premi '{$premi->nama_premi}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$premi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Premi $premi)
    {
        if (!Gate::allows('view premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$premi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data premi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $this->formatData(collect([$premi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data premi '{$premi->nama_premi}' berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdatePremiRequest $request)
    {
        $premi = Premi::withTrashed()->find($id);

        if (!Gate::allows('edit premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = Premi::where('nama_premi', $data['nama_premi'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama premi tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $premi->update($data);
        $updatedPremi = $premi->fresh();
        $successMessage = "Data premi '{$updatedPremi->nama_premi}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$premi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Premi $premi)
    {
        if (!Gate::allows('delete premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi->delete();

        $successMessage = "Data premi '{$premi->nama_premi}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $premi = Premi::withTrashed()->find($id);

        if (!Gate::allows('delete premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi->restore();

        if (is_null($premi->deleted_at)) {
            $successMessage = "Data premi '{$premi->nama_premi}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($premi) {
            return [
                'id' => $premi->id,
                'nama_premi' => $premi->nama_premi,
                'kategori_potongan' => $premi->kategori_potongans,
                'jenis_premi' => $premi->jenis_premi,
                'besaran_premi' => $premi->besaran_premi,
                'minimal_rate' => $premi->minimal_rate,
                'maksimal_rate' => $premi->maksimal_rate,
                'deleted_at' => $premi->deleted_at,
                'created_at' => $premi->created_at,
                'updated_at' => $premi->updated_at
            ];
        });
    }
}
