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
use Illuminate\Support\Facades\DB;

class RekamJejakController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllRekamJejak()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $rekamJejak = TrackRecord::with('users')->get();
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

        $rekamJejak = TrackRecord::query()
            ->with('users')
            ->select('track_records.*')
            ->join(DB::raw('(SELECT MAX(id) as max_id FROM track_records GROUP BY user_id) as grouped'), 'track_records.id', '=', 'grouped.max_id');

        // Filter
        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $rekamJejak->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        if ($request->has('status_karyawan')) {
            $namaStatus = $request->status_karyawan;

            $rekamJejak->whereHas('users.data_karyawans', function ($query) use ($namaStatus) {
                if (is_array($namaStatus)) {
                    $query->whereIn('status_karyawan', $namaStatus);
                } else {
                    $query->where('status_karyawan', '=', $namaStatus);
                }
            });
        }

        if ($request->has('masa_kerja')) {
            $masa_kerja = $request->masa_kerja;
            $rekamJejak->whereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$masa_kerja]);
        }

        // Search
        if ($request->has('search')) {
            $rekamJejak = $rekamJejak->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })
                    ->orWhere('promosi', 'like', $searchTerm)
                    ->orWhere('mutasi', 'like', $searchTerm)
                    ->orWhere('penghargaan', 'like', $searchTerm);
            });
        }

        $dataRekamJejak = $rekamJejak->paginate(10);
        if ($dataRekamJejak->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data rekam jejak tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataRekamJejak->items();
        $formattedData = array_map(function ($rekam_jejak) {
            return [
                'id' => $rekam_jejak->id,
                'user' => $rekam_jejak->users,
                'tgl_masuk' => $rekam_jejak->tgl_masuk,
                'tgl_keluar' => $rekam_jejak->tgl_keluar,
                // calculation
                'masa_kerja' => $this->calculateMasaKerja($rekam_jejak->tgl_masuk, $rekam_jejak->tgl_keluar),
                // calculation
                'promosi' => $rekam_jejak->promosi,
                'mutasi' => $rekam_jejak->mutasi,
                'penghargaan' => $rekam_jejak->penghargaan,
                'created_at' => $rekam_jejak->created_at,
                'updated_at' => $rekam_jejak->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $dataRekamJejak->url(1),
                'last' => $dataRekamJejak->url($dataRekamJejak->lastPage()),
                'prev' => $dataRekamJejak->previousPageUrl(),
                'next' => $dataRekamJejak->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $dataRekamJejak->currentPage(),
                'last_page' => $dataRekamJejak->lastPage(),
                'per_page' => $dataRekamJejak->perPage(),
                'total' => $dataRekamJejak->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data rekam jejak karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $rekamJejak = TrackRecord::find($id);
        if (!$rekamJejak) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data rekam jejak karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $userData = [
            'user' => $rekamJejak->users,
            'tgl_masuk' => $rekamJejak->tgl_masuk,
            'tgl_keluar' => $rekamJejak->tgl_keluar,
            'masa_kerja' => $this->calculateMasaKerja($rekamJejak->tgl_masuk, $rekamJejak->tgl_keluar)
        ];

        // get semua record dari id
        $rekamJejakList = TrackRecord::where('user_id', $rekamJejak->user_id)->paginate(10);
        $formattedData = $rekamJejakList->items();
        $formattedData = array_map(function ($item) {
            return [
                'id' => $item->id,
                'tgl_masuk' => $item->tgl_masuk,
                'promosi' => $item->promosi,
                'mutasi' => $item->mutasi,
                'penghargaan' => $item->penghargaan,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $rekamJejakList->url(1),
                'last' => $rekamJejakList->url($rekamJejakList->lastPage()),
                'prev' => $rekamJejakList->previousPageUrl(),
                'next' => $rekamJejakList->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $rekamJejakList->currentPage(),
                'last_page' => $rekamJejakList->lastPage(),
                'per_page' => $rekamJejakList->perPage(),
                'total' => $rekamJejakList->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data rekam jejak karyawan {$rekamJejak->users->nama} berhasil ditampilkan.",
            'data' => [
                'data_user' => $userData,
                'transfer_karyawan_id' => $formattedData,
            ],
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function exportRekamJejak(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new RekamJejakExport(), 'rekam-jejak-karyawan.xls');
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

    // masa kerja calculation
    private function calculateMasaKerja($tglMasuk, $tglKeluar)
    {
        if ($tglMasuk) {
            $tglMasuk = Carbon::parse($tglMasuk)->format('Y-m-d');
            $tglSekarang = Carbon::now()->format('Y-m-d');

            if ($tglKeluar) {
                $tglKeluar = Carbon::parse($tglKeluar)->format('Y-m-d');
                return Carbon::parse($tglMasuk)->diffInDays(Carbon::parse($tglKeluar));
            } else {
                return Carbon::parse($tglMasuk)->diffInDays(Carbon::parse($tglSekarang));
            }
        }
        return null;
    }
}
