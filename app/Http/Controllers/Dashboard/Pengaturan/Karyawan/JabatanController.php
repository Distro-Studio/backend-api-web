<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreJabatanRequest;
use App\Http\Requests\UpdateJabatanRequest;
use App\Exports\Pengaturan\Karyawan\JabatanExport;
use App\Imports\Pengaturan\Karyawan\JabatanImport;
use App\Http\Requests\Excel_Import\ImportJabatanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan = Jabatan::withTrashed();

        // Filter
        if ($request->has('delete_data')) {
            $softDeleteFilters = $request->delete_data;
            $jabatan->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->onlyTrashed();
            })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->withoutTrashed();
            });
        }

        // Search
        if ($request->has('search')) {
            $jabatan = $jabatan->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhere('nama_jabatan', 'like', $searchTerm);
            });
        }

        $dataJabatan = $jabatan->get();
        if ($dataJabatan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jabatan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data jabatan berhasil ditampilkan.";
        $formattedData = $this->formatData($dataJabatan);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreJabatanRequest $request)
    {
        if (!Gate::allows('create jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jabatan = Jabatan::create($data);
        $successMessage = "Data jabatan '{$jabatan->nama_jabatan}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$jabatan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Jabatan $jabatan)
    {
        if (!Gate::allows('view jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$jabatan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jabatan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data jabatan {$jabatan->nama_jabatan} berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$jabatan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update(Jabatan $jabatan, UpdateJabatanRequest $request)
    {
        if (!Gate::allows('edit jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jabatan->update($data);
        $updatedJabatan = $jabatan->fresh();
        $successMessage = "Data jabatan '{$updatedJabatan->nama_jabatan}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$jabatan]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Jabatan $jabatan)
    {
        if (!Gate::allows('delete jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan->delete();

        $successMessage = 'Data jabatan berhasil dihapus.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $jabatan = Jabatan::withTrashed()->find($id);

        if (!Gate::allows('delete jabatan', $jabatan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan->restore();

        if (is_null($jabatan->deleted_at)) {
            $successMessage = "Data jabatan {$jabatan->nama_jabatan} berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportJabatan()
    {
        if (!Gate::allows('export jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new JabatanExport(), 'data-jabatan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jabatan berhasil di download.'), Response::HTTP_OK);
    }

    public function importJabatan(ImportJabatanRequest $request)
    {
        if (!Gate::allows('import jabatan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new JabatanImport, $file['jabatan_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jabatan berhasil di import kedalam table.'), Response::HTTP_OK);
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($jabatan) {
            return [
                'id' => $jabatan->id,
                'nama_jabatan' => $jabatan->nama_jabatan,
                'is_struktural' => $jabatan->is_struktural,
                'tunjangan' => $jabatan->tunjangan,
                'deleted_at' => $jabatan->deleted_at,
                'created_at' => $jabatan->created_at,
                'updated_at' => $jabatan->updated_at
            ];
        });
    }
}
