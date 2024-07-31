<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use App\Models\Ter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreTERRequest;
use App\Http\Requests\UpdateTERRequest;
use App\Exports\Pengaturan\Finance\TER21Export;
use App\Imports\Pengaturan\Finance\TER21Import;
use App\Http\Requests\Excel_Import\ImportTER21Request;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

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
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ter = Ter::withTrashed();

        // Filter
        if ($request->has('delete_data')) {
            $softDeleteFilters = $request->delete_data;
            $ter->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->onlyTrashed();
            })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->withoutTrashed();
            });
        }

        // Search
        if ($request->has('search')) {
            $ter = $ter->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('kategori_ters', function ($query) use ($searchTerm) {
                    $query->where('nama_kategori_ter', 'like', $searchTerm);
                });
            });
        }

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
        $existingDataValidation = Ter::where('kategori_ter_id', $data['kategori_ter_id'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kategori TER dengan ID tersebut sudah ada.'), Response::HTTP_BAD_REQUEST);
        }

        $ter_pph_21->update($data);
        $successMessage = "Data TER PPH21 berhasil diubah.";
        $formattedData = $this->formatData(collect([$ter_pph_21]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Ter $ter_pph_21)
    {
        if (!Gate::allows('delete ter21', $ter_pph_21)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ter_pph_21->delete();

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

    public function exportTER()
    {
        if (!Gate::allows('export ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new TER21Export(), 'ter-pph-21.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data TER berhasil di download.'), Response::HTTP_OK);
    }

    public function importTER(ImportTER21Request $request)
    {
        if (!Gate::allows('import ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new TER21Import, $file['ter_pph_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data Ter PPH21 berhasil di import kedalam table.'), Response::HTTP_OK);
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($ter21) {
            return [
                'id' => 'T00' . $ter21->id,
                'kategori_ter_id' => $ter21->kategori_ters,
                'from_ter' => $ter21->from_ter,
                'to_ter' => $ter21->to_ter,
                'percentage' => $ter21->percentage_ter,
                'deleted_at' => $ter21->deleted_at,
                'created_at' => $ter21->created_at,
                'updated_at' => $ter21->updated_at
            ];
        });
    }
}
