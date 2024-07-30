<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use App\Exports\Jadwal\LemburJadwalExport;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreLemburKaryawanRequest;
use App\Http\Requests\UpdateLemburKaryawanRequest;
use App\Http\Resources\Dashboard\Jadwal\LemburJadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class LemburJadwalController extends Controller
{
    public function getAllJadwalLembur()
    {
        if (!Gate::allows('view lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataLemburs = Lembur::with(['users', 'shifts'])->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieve all data lemburs for dropdown.',
            'data' => $dataLemburs
        ]);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $lembur = Lembur::query()->with(['users', 'shifts']);

        if ($request->has('status_kompensasi') && $request->status_kompensasi != 'semua_kompensasi') {
            $statusKompensasi = $request->status_kompensasi;
            $lembur->where('kompensasi', $statusKompensasi);
        }

        if ($request->has('search')) {
            $lembur = $lembur->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })
                    ->orWhere('kompensasi', 'like', $searchTerm);
            });
        }

        $currentDate = now()->startOfDay();
        if ($request->has('status_lembur') && $request->status_lembur != 'semua_lembur') {
            $statusLembur = $request->status_lembur;

            $lembur->where(function ($query) use ($statusLembur, $currentDate) {
                if ($statusLembur == 'dijadwalkan') {
                    $query->where('tgl_pengajuan', '>', $currentDate);
                } elseif ($statusLembur == 'berlangsung') {
                    $query->where('tgl_pengajuan', '=', $currentDate);
                } elseif ($statusLembur == 'selesai') {
                    $query->where('tgl_pengajuan', '<', $currentDate);
                }
            });
        }

        $dataLembur = $lembur->paginate(10);

        if ($dataLembur->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataLembur->items();
        $formattedData = array_map(function ($lembur) use ($currentDate) {
            if ($currentDate->lessThan($lembur->tgl_pengajuan)) {
                $status_lembur = 'Dijadwalkan';
            } elseif ($currentDate->isSameDay($lembur->tgl_pengajuan)) {
                $status_lembur = 'Berlangsung';
            } else {
                $status_lembur = 'Selesai';
            }
            return [
                'id' => $lembur->id,
                'user' => $lembur->users,

                // TODO: ini menjadi jadwal
                'shift' => $lembur->shifts,
                // TODO: ini menjadi jadwal
                
                'tgl_pengajuan' => $lembur->tgl_pengajuan,
                'kompensasi' => $lembur->kompensasi,
                'tipe' => $lembur->tipe,
                'durasi' => $lembur->durasi,
                'catatan' => $lembur->catatan,
                'status_lembur' => $status_lembur,
                'created_at' => $lembur->created_at,
                'updated_at' => $lembur->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $dataLembur->url(1),
                'last' => $dataLembur->url($dataLembur->lastPage()),
                'prev' => $dataLembur->previousPageUrl(),
                'next' => $dataLembur->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $dataLembur->currentPage(),
                'last_page' => $dataLembur->lastPage(),
                'per_page' => $dataLembur->perPage(),
                'total' => $dataLembur->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data lembur karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreLemburKaryawanRequest $request)
    {
        if (!Gate::allows('create lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $durasiJam = $request->input('durasi_jam');
        $durasiMenit = $request->input('durasi_menit');
        $totalDurasi = "{$durasiJam}j {$durasiMenit}m";
        $data['durasi'] = $totalDurasi;

        $dataLembur = Lembur::create($data);

        $message = "Lembur karyawan {$dataLembur->users->name} berhasil ditambahkan.";
        return response()->json(new LemburJadwalResource(Response::HTTP_OK, $message, $dataLembur), Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataLembur = Lembur::with(['users', 'shifts'])->find($id);
        if (!$dataLembur) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $currentDate = now();
        if ($currentDate->lessThan($dataLembur->tgl_pengajuan)) {
            $status_lembur = 'Dijadwalkan';
        } elseif ($currentDate->isSameDay($dataLembur->tgl_pengajuan)) {
            $status_lembur = 'Berlangsung';
        } else {
            $status_lembur = 'Selesai';
        }

        $formattedData = [
            'id' => $dataLembur->id,
            'user' => $dataLembur->users,
            'shift' => $dataLembur->shifts,
            'tgl_pengajuan' => $dataLembur->tgl_pengajuan,
            'kompensasi' => $dataLembur->kompensasi,
            'tipe' => $dataLembur->tipe,
            'durasi' => $dataLembur->durasi,
            'catatan' => $dataLembur->catatan,
            'status_lembur' => $status_lembur,
            'created_at' => $dataLembur->created_at,
            'updated_at' => $dataLembur->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail data lembur karyawan {$dataLembur->users->nama} berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function update(UpdateLemburKaryawanRequest $request, $id)
    {
        if (!Gate::allows('edit lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $dataLembur = Lembur::find($id);
        if (!$dataLembur) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $durasiJam = $request->input('durasi_jam');
        $durasiMenit = $request->input('durasi_menit');
        $totalDurasi = "{$durasiJam}j {$durasiMenit}m";
        $data['durasi'] = $totalDurasi;

        $dataLembur->update($data);
        $message = "Lembur karyawan {$dataLembur->users->nama} berhasil diperbarui.";
        return response()->json(new LemburJadwalResource(Response::HTTP_OK, $message, $dataLembur), Response::HTTP_OK);
    }

    public function exportJadwalLembur(Request $request)
    {
        if (!Gate::allows('export lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new LemburJadwalExport(), 'lembur-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jadwal lembur karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
