<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use App\Models\Ter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreTERRequest;
use App\Http\Requests\UpdateTERRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\KategoriTer;

class TER21Controller extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllTer()
    {
        if (!Gate::allows('view ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataTer = Ter::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Ter PPH21 for dropdown',
            'data' => $dataTer
        ], Response::HTTP_OK);
    }

    public function getAllKategoriTER()
    {
        if (!Gate::allows('view ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kategori_ter = KategoriTer::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kategori Ter PPH21 for dropdown',
            'data' => $kategori_ter
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ter = Ter::withTrashed()->orderBy('created_at', 'desc');

        $dataTer = $ter->get();
        if ($dataTer->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Ter PPH21 tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data Ter PPH21 berhasil ditampilkan.";
        $formattedData = $this->formatData($dataTer);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreTERRequest $request)
    {
        if (!Gate::allows('create ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique pada relasi kategori_ter_id
        $existingTer = Ter::where('kategori_ter_id', $data['kategori_ter_id'])->first();
        if ($existingTer) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kategori TER dengan ID tersebut sudah ada.'), Response::HTTP_BAD_REQUEST);
        }

        $ter_pph_21 = Ter::create($data);
        $successMessage = "Data Ter PPH21 berhasil dibuat.";
        $formattedData = $this->formatData(collect([$ter_pph_21]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Ter $ter_pph_21)
    {
        if (!Gate::allows('view ter21', $ter_pph_21)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$ter_pph_21) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data TER PPH21 tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data TER PPH21 berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$ter_pph_21]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateTERRequest $request)
    {
        $ter_pph_21 = Ter::withTrashed()->find($id);

        if (!Gate::allows('edit ter21', $ter_pph_21)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique pada relasi kategori_ter_id
        // $existingDataValidation = Ter::where('kategori_ter_id', $data['kategori_ter_id'])->where('id', '!=', $id)->first();
        // if ($existingDataValidation) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kategori TER dengan ID tersebut sudah ada.'), Response::HTTP_BAD_REQUEST);
        // }

        $ter_pph_21->update($data);
        $successMessage = "Data TER PPH21 berhasil diubah.";
        $formattedData = $this->formatData(collect([$ter_pph_21]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Ter $pph_21)
    {
        if (!Gate::allows('delete ter21', $pph_21)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pph_21->delete();

        $successMessage = 'Data TER berhasil dihapus.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $ter_pph_21 = Ter::withTrashed()->find($id);

        if (!Gate::allows('delete ter21', $ter_pph_21)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ter_pph_21->restore();

        if (is_null($ter_pph_21->deleted_at)) {
            $successMessage = "Data TER PPH 21 berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($ter_21) {
            return [
                'id' => $ter_21->id,
                'kategori_ter_id' => $ter_21->kategori_ters,
                'from_ter' => $ter_21->from_ter,
                'to_ter' => $ter_21->to_ter,
                'percentage' => $ter_21->percentage,
                'deleted_at' => $ter_21->deleted_at,
                'created_at' => $ter_21->created_at,
                'updated_at' => $ter_21->updated_at
            ];
        });
    }
}
