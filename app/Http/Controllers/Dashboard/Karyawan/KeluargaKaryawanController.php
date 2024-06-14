<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Models\User;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exports\Karyawan\KeluargaKaryawanExport;
use App\Http\Requests\UpdateKeluargaKaryawanRequest;
use App\Http\Resources\Dashboard\Karyawan\KeluargaResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class KeluargaKaryawanController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllKeluargaKaryawan()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $keluargaKaryawan = DataKeluarga::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all keluarga karyawan for dropdown',
            'data' => $keluargaKaryawan
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $keluargaQuery = DataKeluarga::query();

        if ($request->has('status_karyawan')) {
            $statusKaryawan = $request->status_karyawan;
            $keluargaQuery->whereHas('data_karyawans', function ($query) use ($statusKaryawan) {
                if (is_array($statusKaryawan)) {
                    $query->whereIn('status_karyawan', $statusKaryawan);
                } else {
                    $query->where('status_karyawan', '=', $statusKaryawan);
                }
            });
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;
            $keluargaQuery->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $keluargaQuery->where(function ($query) use ($searchTerm) {
                $query->whereHas('data_karyawans.users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                });
                $query->orWhere('nama_keluarga', 'like', $searchTerm);
            });
        }

        // Ambil semua data keluarga yang cocok dengan pencarian
        $searchedDataKeluarga = $keluargaQuery->with('data_karyawans.users')->get();

        if ($searchedDataKeluarga->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data keluarga tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Ambil semua data keluarga berdasarkan data_karyawan_id yang ditemukan dalam pencarian
        $dataKaryawanIds = $searchedDataKeluarga->pluck('data_karyawan_id')->unique();
        $allDataKeluarga = DataKeluarga::whereIn('data_karyawan_id', $dataKaryawanIds)
            ->with('data_karyawans.users')
            ->get();

        // Group by data_karyawan_id
        $groupedData = $allDataKeluarga->groupBy('data_karyawan_id');

        // Format data untuk setiap grup
        $formattedData = $groupedData->map(function ($item) {
            $firstItem = $item->first();
            $ayah = $item->where('hubungan', 'Ayah')->first();
            $ibu = $item->where('hubungan', 'Ibu')->first();
            return [
                'id' => $firstItem->data_karyawans->id,
                'user' => $firstItem->data_karyawans->users,
                'ayah' => $ayah ? $ayah->nama_keluarga : null,
                'ibu' => $ibu ? $ibu->nama_keluarga : null,
                'jumlah_keluarga' => $item->count(),
            ];
        })->values();

        // Paginasi sederhana
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = $formattedData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedData = new LengthAwarePaginator($currentPageItems, $formattedData->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data keluarga karyawan berhasil ditampilkan.',
            'data' => $paginatedData,
        ], Response::HTTP_OK);
    }

    public function show($dataKaryawanId)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melihat data ini.'), Response::HTTP_FORBIDDEN);
        }

        $keluarga = DataKeluarga::where('data_karyawan_id', $dataKaryawanId)
            ->with('data_karyawans.users')
            ->get();

        if ($keluarga->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data keluarga tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $dataKaryawan = $keluarga->first()->data_karyawans;
        $user = $dataKaryawan->users;

        $formattedData = $keluarga->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama_keluarga,
                'hubungan' => $item->hubungan,
                'pendidikan_terakhir' => $item->pendidikan_terakhir,
                'status_hidup' => $item->status_hidup,
                'pekerjaan' => $item->pekerjaan,
                'no_hp' => $item->no_hp,
                'email' => $item->email,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail keluarga karyawan {$user->nama} berhasil ditampilkan.",
            'data' => [
                'id' => $dataKaryawan->id,
                'user' => $user,
                'data_karyawan' => $dataKaryawan,
                'jumlah_keluarga' => $keluarga->count(),
                'data_keluarga' => $formattedData,
            ],
        ], Response::HTTP_OK);
    }

    public function update(UpdateKeluargaKaryawanRequest $request, $dataKeluargaId)
    {
        if (!Gate::allows('edit dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $dataKeluarga = DataKeluarga::find($dataKeluargaId);

        if (!$dataKeluarga) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data keluarga karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $dataKeluarga->update($data);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data keluarga {$dataKeluarga->nama_keluarga} berhasil diperbarui.",
            'data' => $dataKeluarga,
        ], Response::HTTP_OK);
    }

    public function exportKeluargaKaryawan(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new KeluargaKaryawanExport(), 'keluarga-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data keluarga karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
