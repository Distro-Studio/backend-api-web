<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use App\Models\Ter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreTERRequest;
use App\Http\Requests\UpdateTERRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exports\Pengaturan\Finance\TER21Export;
use App\Http\Requests\Excel_Import\ImportTER21Request;
use App\Imports\Pengaturan\Finance\TER21Import;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Finance\TER21Resource;

class TER21Controller extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllTer()
    {
        if (!Gate::allows('view ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataTer = Ter::with(['kategori_ters', 'ptkps'])->whereNull('deleted_at')->get();
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

        $ter = Ter::with(['kategori_ters', 'ptkps']);

        // Filter
        if ($request->has('nama_kategori_ter')) {
            $namaKategoriTER = $request->nama_kategori_ter;

            $ter->whereHas('kategori_ters', function ($query) use ($namaKategoriTER) {
                if (is_array($namaKategoriTER)) {
                    $query->whereIn('nama_kategori_ter', $namaKategoriTER);
                } else {
                    $query->where('nama_kategori_ter', '=', $namaKategoriTER);
                }
            });
        }

        if ($request->has('kode_ptkp')) {
            $namaPTKP = $request->kode_ptkp;

            $ter->whereHas('ptkps', function ($query) use ($namaPTKP) {
                if (is_array($namaPTKP)) {
                    $query->whereIn('kode_ptkp', $namaPTKP);
                } else {
                    $query->where('kode_ptkp', '=', $namaPTKP);
                }
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

        $dataTer = $ter->paginate(10);
        if ($dataTer->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Ter PPH21 tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new TER21Resource(Response::HTTP_OK, 'Data Ter PPH21 berhasil ditampilkan.', $dataTer), Response::HTTP_OK);
    }

    public function store(StoreTERRequest $request)
    {
        if (!Gate::allows('create ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $ter = Ter::with(['kategori_ters', 'ptkps'])->create($data);
        $successMessage = "Data Ter PPH21 berhasil dibuat.";
        return response()->json(new TER21Resource(Response::HTTP_OK, $successMessage, $ter), Response::HTTP_OK);
    }

    public function show(Ter $ter_pph_21)
    {
        if (!Gate::allows('view ter21', $ter_pph_21)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$ter_pph_21) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data TER PPH21 tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new TER21Resource(Response::HTTP_OK, 'Data TER PPH21 berhasil ditampilkan.', $ter_pph_21), Response::HTTP_OK);
    }

    public function update(Ter $ter_pph_21, UpdateTERRequest $request)
    {
        if (!Gate::allows('edit ter21', $ter_pph_21)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $ter_pph_21->update($data);
        $updatedterTer = $ter_pph_21->fresh();

        $successMessage = "Data TER PPH21 berhasil diubah.";
        return response()->json(new TER21Resource(Response::HTTP_OK, $successMessage, $updatedterTer), Response::HTTP_OK);
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

    public function exportTER(Request $request)
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
}