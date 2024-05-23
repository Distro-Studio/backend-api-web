<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Models\User;
use App\Models\DataKeluarga;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
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

        $keluarga = DataKeluarga::query();
        $keluarga->whereIn('hubungan', ['Ayah', 'Ibu']);

        // Filter
        if ($request->has('status_karyawan')) {
            $statusKaryawan = $request->status_karyawan;

            $keluarga->with('data_karyawans:id,user_id,status_karyawan')
                ->whereHas('data_karyawans', function ($query) use ($statusKaryawan) {
                    if (is_array($statusKaryawan)) {
                        $query->whereIn('status_karyawan', $statusKaryawan);
                    } else {
                        $query->where('status_karyawan', '=', $statusKaryawan);
                    }
                });
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            // get unit kerja dari table data karyawan
            $keluarga->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        // Search
        if ($request->has('search')) {
            $keluarga = $keluarga->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('data_karyawans.users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                }); // nama karyawan

                $query->orWhere('nama_keluarga', 'like', $searchTerm); // nama keluarga
            });
        }

        $keluargaKaryawan = $keluarga->paginate(10);
        
        // Tambahkan perhitungan jumlah keluarga untuk setiap user
        $keluargaKaryawan->getCollection()->transform(function ($keluarga) {
            $user = $keluarga->data_karyawans->users;
            $keluargaCount = DataKeluarga::whereHas('data_karyawans', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count();
            $keluarga->jumlah_keluarga = $keluargaCount;

            return $keluarga;
        });

        if ($keluargaKaryawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data keluarga tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data keluarga karyawan berhasil ditampilkan.',
            'data' => $keluargaKaryawan
        ], Response::HTTP_OK);
    }

    public function show($userId)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari user berdasarkan ID
        $user = User::find($userId);
        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Maaf data pengguna tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Ambil data karyawan user beserta data keluarganya
        $dataKaryawans = $user->data_karyawans()->with('data_keluargas')->get();

        // Tambahkan informasi jumlah keluarga untuk setiap data karyawan
        $dataKaryawansWithTotalFamily = $dataKaryawans->map(function ($dataKaryawan) {
            $totalFamily = $dataKaryawan->data_keluargas->count();
            $dataKaryawan->jumlah_keluarga = $totalFamily;
            return $dataKaryawan;
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data keluarga {$user->nama} berhasil ditampilkan",
            'data' => [
                'data_karyawans' => $dataKaryawansWithTotalFamily,
                'users' => $user
            ],
        ]);
    }

    public function exportKeluargaKaryawan(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new KeluargaKaryawanExport($ids), 'keluarga-karyawans.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data keluarga karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
