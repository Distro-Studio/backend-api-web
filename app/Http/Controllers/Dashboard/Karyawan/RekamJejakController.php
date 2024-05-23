<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Carbon\Carbon;
use App\Models\TrackRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Karyawan\KaryawanImport;
use Illuminate\Support\Facades\Validator;
use App\Exports\Karyawan\RekamJejakExport;
use App\Imports\Karyawan\RekamJejakImport;
use App\Http\Resources\Dashboard\Karyawan\RekamJejakResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Requests\Excel_Import\ImportRekamJejakKaryawanRequest;

class RekamJejakController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllRekamJejak()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $rekamJejak = TrackRecord::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all rekam jejak karyawan for dropdown',
            'data' => $rekamJejak
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $rekamJejak = TrackRecord::query();

        // Filter
        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            // filter kalo gak punya relasi
            $rekamJejak->join('users', 'track_records.user_id', '=', 'users.id')
                ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
                ->join('unit_kerjas', 'data_karyawans.unit_kerja_id', '=', 'unit_kerjas.id')
                ->where(function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('unit_kerjas.nama_unit', $namaUnitKerja);
                    } else {
                        $query->where('unit_kerjas.nama_unit', '=', $namaUnitKerja);
                    }
                });
        }

        if ($request->has('status_karyawan')) {
            $namaStatus = $request->status_karyawan;

            $rekamJejak->join('users', 'track_records.user_id', '=', 'users.id')
                ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
                ->where(function ($query) use ($namaStatus) {
                    if (is_array($namaStatus)) {
                        $query->whereIn('data_karyawans.status_karyawan', $namaStatus);
                    } else {
                        $query->where('data_karyawans.status_karyawan', '=', $namaStatus);
                    }
                });
        }

        // calculate masa kerja
        if ($request->has('tahun_max')) {
            $tahun_max = $request->tahun_max;
            $rekamJejak->whereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$tahun_max]);
        }

        // Search
        if ($request->has('search')) {
            $rekamJejak = $rekamJejak->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                });

                $query->orWhere('promosi', 'like', $searchTerm)
                    ->orWhere('mutasi', 'like', $searchTerm)
                    ->orWhere('penghargaan', 'like', $searchTerm);
            });
        }

        $dataKaryawan = $rekamJejak->paginate(10);
        if ($dataKaryawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data rekam jejak tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new RekamJejakResource(Response::HTTP_OK, 'Data rekam jejak karyawan berhasil ditampilkan.', $dataKaryawan), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataRecords = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:track_records,id'
        ]);

        if ($dataRecords->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataRecords->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        TrackRecord::whereIn('id', $ids)->delete();

        $message = 'Data rekam jejak berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportRekamJejak(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new RekamJejakExport($ids), 'rekam-jejak-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function importRekamJejak(ImportRekamJejakKaryawanRequest $request)
    {
        if (!Gate::allows('import dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new RekamJejakImport, $file['rekam_jejak_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
