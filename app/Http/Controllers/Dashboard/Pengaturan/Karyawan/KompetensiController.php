<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Kompetensi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreKompetensiRequest;
use App\Http\Requests\UpdateKompetensiRequest;
use App\Exports\Pengaturan\Karyawan\KompetensiExport;
use App\Imports\Pengaturan\Karyawan\KompetensiImport;
use App\Http\Requests\Excel_Import\ImportKompetensiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class KompetensiController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        $kompetensi = Kompetensi::withTrashed();

        // Filter
        if ($request->has('delete_data')) {
            $softDeleteFilters = $request->delete_data;
            $kompetensi->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->onlyTrashed();
            })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->withoutTrashed();
            });
        }

        // Search
        if ($request->has('search')) {
            $kompetensi = $kompetensi->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhere('nama_kompetensi', 'like', $searchTerm)
                    ->orWhere('jenis_kompetensi', 'like', $searchTerm);
            });
        }

        $dataKompetensi = $kompetensi->get();

        if ($dataKompetensi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kompetensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data Ter PPH21 berhasil ditampilkan.";
        $formattedData = $this->formatData($dataKompetensi);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreKompetensiRequest $request)
    {
        if (!Gate::allows('create kompetensi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kompetensi = Kompetensi::create($data);
        $successMessage = "Data kompetensi '{$kompetensi->nama_kompetensi}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$kompetensi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Kompetensi $kompetensi)
    {
        if (!Gate::allows('view kompetensi', $kompetensi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$kompetensi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kompetensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data kompetensi {$kompetensi->nama_kompetensi} berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$kompetensi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
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
        $formattedData = $this->formatData(collect([$updatedKompetensi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
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

    public function exportKompetensi()
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

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($kompetensi) {
            return [
                'id' => 'KP00' . $kompetensi->id,
                'nama_kompetensi' => $kompetensi->nama_kompetensi,
                'jenis_kompetensi' => $kompetensi->jenis_kompetensi,
                'total_tunjangan' => $kompetensi->total_tunjangan,
                'total_bor' => $kompetensi->total_bor,
                'deleted_at' => $kompetensi->deleted_at,
                'created_at' => $kompetensi->created_at,
                'updated_at' => $kompetensi->updated_at
            ];
        });
    }
}
