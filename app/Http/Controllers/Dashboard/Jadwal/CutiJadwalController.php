<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use App\Models\Cuti;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\CutiJadwalExport;
use App\Http\Requests\StoreCutiJadwalRequest;
use App\Http\Resources\Dashboard\Jadwal\CutiJadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class CutiJadwalController extends Controller
{
    public function getAllJadwalCuti()
    {
        if (!Gate::allows('view cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataJadwalCuti = Cuti::with(['users', 'tipe_cutis'])->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieve data cuti for dropdown.',
            'data' => $dataJadwalCuti
        ]);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cuti = Cuti::query()->with(['users', 'tipe_cutis']);

        if ($request->has('tipe_cuti')) {
            $statuskaryawan = $request->tipe_cuti;
            $cuti->whereHas('tipe_cutis', function ($query) use ($statuskaryawan) {
                if (is_array($statuskaryawan)) {
                    $query->whereIn('nama', $statuskaryawan);
                } else {
                    $query->where('nama', '=', $statuskaryawan);
                }
            });
        }

        $currentDate = now();

        if ($request->has('status_cuti') && $request->status_cuti != 'semua_cuti') {
            $statusCuti = $request->status_cuti;

            $cuti->where(function ($query) use ($statusCuti, $currentDate) {
                if ($statusCuti == 'dijadwalkan') {
                    $query->where('tgl_from', '>', $currentDate);
                } elseif ($statusCuti == 'berlangsung') {
                    $query->where('tgl_from', '<=', $currentDate)
                        ->where('tgl_to', '>=', $currentDate);
                } elseif ($statusCuti == 'selesai') {
                    $query->where('tgl_to', '<', $currentDate);
                }
            });
        }

        if ($request->has('search')) {
            $cuti = $cuti->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })
                    ->orWhereHas('users.data_karyawans.unit_kerjas', function ($query) use ($searchTerm) {
                        $query->where('nama_unit', 'like', $searchTerm);
                    })
                    ->orWhereHas('tipe_cutis', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    });
            });
        }

        $dataCuti = $cuti->paginate(10);
        if ($dataCuti->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataCuti->items();
        $formattedData = array_map(function ($cuti_jadwal) use ($currentDate) {
            if ($currentDate->lessThan($cuti_jadwal->tgl_from)) {
                $status_cuti = 'Dijadwalkan';
            } elseif ($currentDate->between($cuti_jadwal->tgl_from, $cuti_jadwal->tgl_to)) {
                $status_cuti = 'Berlangsung';
            } else {
                $status_cuti = 'Selesai';
            }

            return [
                'id' => $cuti_jadwal->id,
                'user' => $cuti_jadwal->users,
                'unit_kerja' => $cuti_jadwal->users->data_karyawans->unit_kerjas,
                'tipe_cuti' => $cuti_jadwal->tipe_cutis,
                'tgl_from' => $cuti_jadwal->tgl_from,
                'tgl_to' => $cuti_jadwal->tgl_to,
                'catatan' => $cuti_jadwal->catatan,
                'durasi' => $cuti_jadwal->durasi,
                'status_cuti' => $status_cuti,
                'created_at' => $cuti_jadwal->created_at,
                'updated_at' => $cuti_jadwal->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $dataCuti->url(1),
                'last' => $dataCuti->url($dataCuti->lastPage()),
                'prev' => $dataCuti->previousPageUrl(),
                'next' => $dataCuti->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $dataCuti->currentPage(),
                'last_page' => $dataCuti->lastPage(),
                'per_page' => $dataCuti->perPage(),
                'total' => $dataCuti->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data cuti karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreCutiJadwalRequest $request)
    {
        if (!Gate::allows('create cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $dataCuti = Cuti::create($data);

        $message = "Data cuti karyawan {$dataCuti->users->nama} berhasil dibuat.";
        return response()->json(new CutiJadwalResource(Response::HTTP_OK, $message, $dataCuti), Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        $cuti = Cuti::with('users', 'tipe_cutis')->find($id);
        if (!$cuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $currentDate = now();

        if ($currentDate->lessThan($cuti->tgl_from)) {
            $status_cuti = 'Dijadwalkan';
        } elseif ($currentDate->between($cuti->tgl_from, $cuti->tgl_to)) {
            $status_cuti = 'Berlangsung';
        } else {
            $status_cuti = 'Selesai';
        }

        $formattedData = [
            'id' => $cuti->id,
            'user' => $cuti->users,
            'tipe_cuti' => $cuti->tipe_cutis,
            'tgl_from' => $cuti->tgl_from,
            'tgl_to' => $cuti->tgl_to,
            'catatan' => $cuti->catatan,
            'durasi' => $cuti->durasi,
            'status_cuti' => $status_cuti,
            'created_at' => $cuti->created_at,
            'updated_at' => $cuti->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail data cuti karyawan {$cuti->users->nama} berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function exportJadwalCuti(Request $request)
    {
        if (!Gate::allows('export cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new CutiJadwalExport(), 'jadwal-cuti-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jadwal cuti karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
