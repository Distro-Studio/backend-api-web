<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Exports\Pengaturan\Karyawan\PertanyaanExport;
use App\Models\Pertanyaan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Excel_Import\ImportPertanyaanRequest;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StorePertanyaanRequest;
use App\Http\Requests\UpdatePertanyaanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\PertanyaanResource;
use App\Imports\Pengaturan\Karyawan\PertanyaanImport;

class PertanyaanController extends Controller
{
    public function getAllPertanyaan()
    {
        if (!Gate::allows('view kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pertanyaan = Pertanyaan::with('jabatans')->get();
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

        $pertanyaan = Pertanyaan::query()->with('jabatans');

        // search
        if ($request->has('search')) {
            $pertanyaan = $pertanyaan->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('jabatans', function ($query) use ($searchTerm) {
                    $query->where('nama_jabatan', 'like', $searchTerm);
                })->orWhere('pertanyaan', 'like', $searchTerm);
            });
        }

        $dataPertanyaan = $pertanyaan->paginate(10);
        return response()->json(new PertanyaanResource(Response::HTTP_OK, 'Data pertanyaan kuesioner berhasil ditampilkan.', $dataPertanyaan), Response::HTTP_OK);
    }

    public function store(StorePertanyaanRequest $request)
    {
        if (!Gate::allows('create kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $pertanyaan = Pertanyaan::create($data);
        $successMessage = "Data pertanyaan kuesioner untuk jabatan {$pertanyaan->jabatans->nama_jabatan} berhasil dibuat.";
        return response()->json(new PertanyaanResource(Response::HTTP_OK, $successMessage, $pertanyaan), Response::HTTP_OK);
    }

    public function show(Pertanyaan $pertanyaan)
    {
        if (!Gate::allows('view kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$pertanyaan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data pertanyaan kuesioner tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new PertanyaanResource(Response::HTTP_OK, 'Data pertanyaan kuesioner berhasil ditampilkan.', $pertanyaan), Response::HTTP_OK);
    }

    public function update(Pertanyaan $pertanyaan, UpdatePertanyaanRequest $request)
    {
        if (!Gate::allows('edit kuesioner', $pertanyaan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $pertanyaan->update($data);
        $updatedPertanyaan = $pertanyaan->fresh();

        $successMessage = "Data pertanyaan kuesioner untuk jabatan {$updatedPertanyaan->jabatans->nama_jabatan} diubah.";
        return response()->json(new PertanyaanResource(Response::HTTP_OK, $successMessage, $updatedPertanyaan), Response::HTTP_OK);
    }

    public function destroy(Pertanyaan $pertanyaan)
    {
        if (!Gate::allows('delete kuesioner', $pertanyaan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pertanyaan->delete();

        $successMessage = 'Data pertanyaan kuesioner berhasil dihapus.';
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
            $successMessage = "Data pertanyaan kuesioner dari jabatan {$pertanyaan->jabatans->nama_jabatan} berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportPertanyaan(Request $request)
    {
        if (!Gate::allows('export kueasioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new PertanyaanExport(), 'data-pertanyaan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data pertanyaan berhasil di download.'), Response::HTTP_OK);
    }

    public function importPertanyaan(ImportPertanyaanRequest $request)
    {
        if (!Gate::allows('import kuesioner')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new PertanyaanImport, $file['pertanyaan_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data pertanyaan berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
