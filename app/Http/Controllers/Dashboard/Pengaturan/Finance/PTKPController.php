<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use App\Models\Ptkp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePTKPRequest;
use App\Http\Requests\UpdatePTKPRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PTKPController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ptkp = Ptkp::withTrashed()->orderBy('created_at', 'desc');

        // Search
        if ($request->has('search')) {
            $ptkp = $ptkp->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $query->orWhere('nama_kategori_ter', 'like', $searchTerm);
            });
        }

        $dataKategoriTER = $ptkp->get();
        if ($dataKategoriTER->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Kode PTKP tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Kode PTKP berhasil ditampilkan.";
        $formattedData = $this->formatData($dataKategoriTER);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StorePTKPRequest $request)
    {
        if (!Gate::allows('create ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $ptkp = Ptkp::create($data);
        $successMessage = "Kode PTKP '{$ptkp->kode_ptkp}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$ptkp]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Ptkp $ptkp)
    {
        if (!Gate::allows('view ter21', $ptkp)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$ptkp) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Kode PTKP tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Kode PTKP '{$ptkp->kode_ptkp}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$ptkp]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdatePTKPRequest $request)
    {
        $ptkp = Ptkp::withTrashed()->find($id);

        if (!Gate::allows('edit ter21', $ptkp)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = Ptkp::where('kode_ptkp', $data['kode_ptkp'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kode PTKP tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $ptkp->update($data);
        $updatedPTKP = $ptkp->fresh();
        $successMessage = "Kode PTKP '{$updatedPTKP->kode_ptkp}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$ptkp]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Ptkp $ptkp)
    {
        if (!Gate::allows('delete ter21', $ptkp)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ptkp->delete();

        $successMessage = "Kode PTKP '{$ptkp->kode_ptkp}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $ptkp = Ptkp::withTrashed()->find($id);

        if (!Gate::allows('delete ter21', $ptkp)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ptkp->restore();

        if (is_null($ptkp->deleted_at)) {
            $successMessage = "Kode PTKP '{$ptkp->kode_ptkp}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $successMessage), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($ptkp) {
            return [
                'id' => $ptkp->id,
                'kode_ptkp' => $ptkp->kode_ptkp,
                'kategori_ter' => $ptkp->kategori_ters,
                'nilai' => $ptkp->nilai,
                'deleted_at' => $ptkp->deleted_at,
                'created_at' => $ptkp->created_at,
                'updated_at' => $ptkp->updated_at
            ];
        });
    }
}
