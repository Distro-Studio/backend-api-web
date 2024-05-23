<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\Karyawan\PekerjaKontrakExport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Karyawan\PekerjaKontrakResource;

class PekerjaKontrakController extends Controller
{
    public function getAllPekerjaKontrak()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pekerjaKontrak = DataKaryawan::where('status_karyawan', 'Kontrak')->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all pekerja kontrak for dropdown',
            'data' => $pekerjaKontrak
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pekerjaKontrak = DataKaryawan::where('status_karyawan', 'Kontrak');

        // Filter
        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $pekerjaKontrak->with('unit_kerjas:id,nama_unit')
                ->whereHas('unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('nama_unit', $namaUnitKerja);
                    } else {
                        $query->where('nama_unit', '=', $namaUnitKerja);
                    }
                });
        }

        if ($request->has('tgl_masuk')) {
            $tglMasuk = $request->tgl_masuk;

            if (is_array($tglMasuk)) {
                $tglMasuk = array_map(function ($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                }, $tglMasuk);
                $pekerjaKontrak->whereIn('tgl_masuk', $tglMasuk);
            } else {
                $tglMasuk = Carbon::parse($tglMasuk)->format('Y-m-d');
                $pekerjaKontrak->where('tgl_masuk', $tglMasuk);
            }
        }

        // Search
        if ($request->has('search')) {
            $pekerjaKontrak = $pekerjaKontrak->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                });
                $query->orWhereHas('unit_kerjas', function ($query) use ($searchTerm) {
                    $query->where('nama_unit', 'like', $searchTerm);
                });
            });
        }

        $dataKontrak = $pekerjaKontrak->paginate(10);
        if ($dataKontrak->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan kontrak tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new PekerjaKontrakResource(Response::HTTP_OK, 'Data karyawan kontrak berhasil ditampilkan.', $dataKontrak), Response::HTTP_OK);
    }

    public function exportPekerjaKontrak(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new PekerjaKontrakExport($ids), 'pekerja-kontrak.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan kontrak berhasil di download.'), Response::HTTP_OK);
    }
}
