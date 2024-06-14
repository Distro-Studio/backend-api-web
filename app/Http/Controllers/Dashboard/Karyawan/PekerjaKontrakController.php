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
        if ($request->has('status_kontrak') && $request->status_kontrak != 'semua_status') {
            $statusAktif = $request->status_kontrak;
            $pekerjaKontrak->where(function ($query) use ($statusAktif) {
                if ($statusAktif == 'aktif') {
                    $query->whereNotNull('tgl_masuk')
                        ->whereNull('tgl_keluar');
                } elseif ($statusAktif == 'tidak_aktif') {
                    $query->whereNotNull('tgl_masuk')
                        ->whereNotNull('tgl_keluar');
                }
            });
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $pekerjaKontrak->whereHas('unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        if ($request->has('tgl_masuk')) {
            $tglMasuk = $request->tgl_masuk;
            $tglMasuk = Carbon::parse($tglMasuk)->format('Y-m-d');
            $pekerjaKontrak->where('tgl_masuk', $tglMasuk);
        }

        // Search
        if ($request->has('search')) {
            $pekerjaKontrak = $pekerjaKontrak->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('unit_kerjas', function ($query) use ($searchTerm) {
                    $query->where('nama_unit', 'like', $searchTerm);
                });
            });
        }

        $dataKontrak = $pekerjaKontrak->paginate(10);
        if ($dataKontrak->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan kontrak tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data untuk output
        $formattedData = $dataKontrak->items();
        $formattedData = array_map(function ($kontrak) {
            $statusAktif = ($kontrak->tgl_masuk && !$kontrak->tgl_keluar) ? 'Aktif' : 'Tidak Aktif';
            return [
                'id' => $kontrak->id,
                'user' => $kontrak->users,
                'unit_kerja' => $kontrak->unit_kerjas,
                'tgl_masuk' => $kontrak->tgl_masuk,
                'tgl_keluar' => $kontrak->tgl_keluar,
                'status_kontrak' => $statusAktif,
                'created_at' => $kontrak->created_at,
                'updated_at' => $kontrak->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $dataKontrak->url(1),
                'last' => $dataKontrak->url($dataKontrak->lastPage()),
                'prev' => $dataKontrak->previousPageUrl(),
                'next' => $dataKontrak->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $dataKontrak->currentPage(),
                'last_page' => $dataKontrak->lastPage(),
                'per_page' => $dataKontrak->perPage(),
                'total' => $dataKontrak->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data karyawan kontrak berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function exportPekerjaKontrak(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new PekerjaKontrakExport(), 'karyawan-kontrak.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan kontrak berhasil di download.'), Response::HTTP_OK);
    }
}
