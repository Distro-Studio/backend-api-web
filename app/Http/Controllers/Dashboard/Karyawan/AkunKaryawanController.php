<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\Karyawan\AkunKaryawanExport;
use App\Http\Resources\Dashboard\Karyawan\AkunResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class AkunKaryawanController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllAkunKaryawan()
    {
        if (!Gate::allows('view dataKaryawan') || !Gate::allows('view user')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $akunKaryawan = User::with('data_karyawans')
            ->where('username', '!=', 'super_admin')
            ->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all akun karyawan for dropdown',
            'data' => $akunKaryawan
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan') || !Gate::allows('view user')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $akunKaryawan = User::query()->where('username', '!=', 'super_admin');

        // Filter
        if ($request->has('status_karyawan')) {
            $namaStatus = $request->status_karyawan;

            $akunKaryawan->whereHas('data_karyawans', function ($query) use ($namaStatus) {
                if (is_array($namaStatus)) {
                    $query->whereIn('status_karyawan', $namaStatus);
                } else {
                    $query->where('status_karyawan', '=', $namaStatus);
                }
            });
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $akunKaryawan->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        // Search
        if ($request->has('search')) {
            $akunKaryawan = $akunKaryawan->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->orWhereHas('data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm)
                        ->orWhere('status_karyawan', 'like', $searchTerm);
                });

                $query->orWhere('nama', 'like', $searchTerm)
                    ->orWhere('username', 'like', $searchTerm);
            });
        }

        $dataAkunKaryawan = $akunKaryawan->paginate(10);
        if ($dataAkunKaryawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data akun karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new AkunResource(Response::HTTP_OK, 'Data akun karyawan berhasil ditampilkan.', $dataAkunKaryawan), Response::HTTP_OK);
    }

    public function exportAkunKaryawan(Request $request)
    {
        if (!Gate::allows('export dataKaryawan') || !Gate::allows('export user')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new AkunKaryawanExport(), 'akun-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data akun karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
