<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Kompetensi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreKompetensiRequest;
use App\Http\Requests\UpdateKompetensiRequest;
use App\Exports\Pengaturan\Karyawan\KompetensiExport;
use App\Http\Requests\Excel_Import\ImportKompetensiRequest;
use App\Imports\Pengaturan\Karyawan\KompetensiImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Karyawan\KompetensiResource;

class KompetensiController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllKompetensi()
    {
        if (!Gate::allows('view kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kompetensi = Kompetensi::whereNull('deleted_at')->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kompetensi for dropdown',
            'data' => $kompetensi
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        $kompetensi = Kompetensi::query();

        // Filter
        if ($request->has('jenis_kompetensi')) {
            if (is_array($request->jenis_kompetensi)) {
                $kompetensi->whereIn('jenis_kompetensi', $request->jenis_kompetensi);
            } else {
                $kompetensi->where('jenis_kompetensi', $request->jenis_kompetensi);
            }
        }

        if ($request->has('range_min')) {
            if (is_array($request->range_min)) {
                $kompetensi->where(function ($query) use ($request) {
                    foreach ($request->range_min as $min) {
                        $query->orWhere('total_tunjangan', '>', $min);
                    }
                });
            } else {
                $kompetensi->where('total_tunjangan', '>', $request->range_min);
            }
        }

        if ($request->has('range_max')) {
            if (is_array($request->range_max)) {
                $kompetensi->where(function ($query) use ($request) {
                    foreach ($request->range_max as $min) {
                        $query->orWhere('total_tunjangan', '<', $min);
                    }
                });
            } else {
                $kompetensi->where('total_tunjangan', '<', $request->range_max);
            }
        }

        if ($request->has('range_min') && $request->has('range_max')) {
            if (is_array($request->range_min) && is_array($request->range_max)) {
                $kompetensi->where(function ($query) use ($request) {
                    foreach ($request->range_min as $index => $min) {
                        if (isset($request->range_max[$index])) {
                            $query->orWhereBetween('total_tunjangan', [$min, $request->range_max[$index]]);
                        }
                    }
                });
            } else if (!is_array($request->range_min) && !is_array($request->range_max)) {
                $kompetensi->whereBetween('total_tunjangan', [$request->range_min, $request->range_max]);
            } else {
                // Handle case where one is array and the other is not, if needed
                // Example: Assume single range_min and multiple range_max, or vice versa
                if (is_array($request->range_min)) {
                    $kompetensi->where(function ($query) use ($request) {
                        foreach ($request->range_min as $min) {
                            $query->orWhere('total_tunjangan', '>=', $min);
                        }
                    });
                } else {
                    $kompetensi->where('total_tunjangan', '>=', $request->range_min);
                }

                if (is_array($request->range_max)) {
                    $kompetensi->where(function ($query) use ($request) {
                        foreach ($request->range_max as $max) {
                            $query->orWhere('total_tunjangan', '<=', $max);
                        }
                    });
                } else {
                    $kompetensi->where('total_tunjangan', '<=', $request->range_max);
                }
            }
        }

        // Search
        if ($request->has('search')) {
            $kompetensi = $kompetensi->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhere('nama_kompetensi', 'like', $searchTerm)
                    ->orWhere('jenis_kompetensi', 'like', $searchTerm);
            });
        }

        $dataKompetensi = $kompetensi->paginate(10);

        if ($dataKompetensi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kompetensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new KompetensiResource(Response::HTTP_OK, 'Data kompetensi berhasil ditampilkan.', $dataKompetensi), Response::HTTP_OK);
    }

    public function store(StoreKompetensiRequest $request)
    {
        if (!Gate::allows('create kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kompetensi = Kompetensi::create($data);
        $successMessage = "Data kompetensi '{$kompetensi->nama_kompetensi}' berhasil dibuat.";
        return response()->json(new KompetensiResource(Response::HTTP_OK, $successMessage, $kompetensi), Response::HTTP_OK);
    }

    public function show(Kompetensi $kompetensi)
    {
        if (!Gate::allows('view kompetensi', $kompetensi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$kompetensi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kompetensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new KompetensiResource(Response::HTTP_OK, 'Data kompetensi berhasil ditampilkan.', $kompetensi), Response::HTTP_OK);
    }

    public function update(Kompetensi $kompetensi, UpdateKompetensiRequest $request)
    {
        if (!Gate::allows('edit kompetensi', $kompetensi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $kompetensi->update($data);
        $updatedKompetensi = $kompetensi->fresh();

        $successMessage = "Data kompetensi '{$updatedKompetensi->nama_kompetensi}' berhasil diubah.";
        return response()->json(new KompetensiResource(Response::HTTP_OK, $successMessage, $updatedKompetensi), Response::HTTP_OK);
    }

    public function destroy(Kompetensi $kompetensi)
    {
        if (!Gate::allows('delete kompetensi', $kompetensi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kompetensi->delete();

        $successMessage = 'Data kompetensi berhasil dihapus.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $kompetensi = Kompetensi::withTrashed()->find($id);

        if (!Gate::allows('delete kompetensi', $kompetensi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kompetensi->restore();

        if (is_null($kompetensi->deleted_at)) {
            $successMessage = "Data kompetensi kuesioner dari jabatan {$kompetensi->nama_kompetensi} berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportKompetensi(Request $request)
    {
        if (!Gate::allows('export kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new KompetensiExport(), 'data-kompetensi.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data kompetensi berhasil di download.'), Response::HTTP_OK);
    }

    public function importKompetensi(ImportKompetensiRequest $request)
    {
        if (!Gate::allows('import kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new KompetensiImport, $file['kompetensi_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data kompetensi berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
