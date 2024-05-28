<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Mail\SendNotifyTransfer;
use App\Models\TransferKaryawan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Karyawan\TransferExport;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreTransferKaryawanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Karyawan\TransferKaryawanResource;

class TransferKaryawanController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllTransferKaryawan()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $transfer = TransferKaryawan::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all transfer karyawan for dropdown',
            'data' => $transfer
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $transfer = TransferKaryawan::query();

        // Filter
        if ($request->has('status_karyawan')) {
            $namaStatus = $request->status_karyawan;

            $transfer->join('users', 'transfer_karyawans.user_id', '=', 'users.id')
                ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
                ->where(function ($query) use ($namaStatus) {
                    if (is_array($namaStatus)) {
                        $query->whereIn('data_karyawans.status_karyawan', $namaStatus);
                    } else {
                        $query->where('data_karyawans.status_karyawan', '=', $namaStatus);
                    }
                });
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            // filter kalo gak punya relasi
            $transfer->join('users', 'transfer_karyawans.user_id', '=', 'users.id')
                ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
                ->join('unit_kerjas as unit_kerja_tos', 'transfer_karyawans.unit_kerja_to', '=', 'unit_kerja_tos.id')
                ->where(function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('unit_kerja_tos.nama_unit', $namaUnitKerja);
                    } else {
                        $query->where('unit_kerja_tos.nama_unit', '=', $namaUnitKerja);
                    }
                });
        }

        // Search
        if ($request->has('search')) {
            $transfer = $transfer->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                });
                $query->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
                $query->orWhereHas('unit_kerja_tos', function ($query) use ($searchTerm) {
                    $query->where('nama_unit', 'like', $searchTerm);
                });

                $query->orWhere('tipe', 'like', $searchTerm)
                    ->orWhere('alasan', 'like', $searchTerm);
            });
        }

        $dataKaryawan = $transfer->paginate(10);
        if ($dataKaryawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data transfer karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new TransferKaryawanResource(Response::HTTP_OK, 'Data transfer karyawan berhasil ditampilkan.', $dataKaryawan), Response::HTTP_OK);
    }

    public function store(StoreTransferKaryawanRequest $request)
    {
        if (!Gate::allows('create dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $transfer = TransferKaryawan::create($data);

        $users = $transfer->users;
        // $email = $transfer->data_karyawans;
        $unit_kerja_asals = $transfer->unit_kerja_asals->nama_unit;
        $unit_kerja_tujuans = $transfer->unit_kerja_tujuans->nama_unit;
        $jabatan_asals = $transfer->jabatan_asals->nama_jabatan;
        $jabatan_tujuans = $transfer->jabatan_tujuans->nama_jabatan;
        $alasan = $transfer->alasan;
        $tanggal_mulai = $transfer->tanggal_mulai;

        if ($request->has('notify_manager_direktur') && $request->notify_manager_direktur == true) {
            // TODO: ganti email
            Mail::to('manager@example.com')->cc('direktur@example.com')->send(new SendNotifyTransfer(
                $users->nama,
                $users->data_karyawans->email,
                $unit_kerja_asals,
                $unit_kerja_tujuans,
                $jabatan_asals,
                $jabatan_tujuans,
                $alasan,
                $tanggal_mulai
            ));
        }
        if ($request->has('notify_users') && $request->notify_users == true) {
            Mail::to($users->data_karyawans->email)->send(new SendNotifyTransfer(
                $users->nama,
                $users->data_karyawans->email,
                $unit_kerja_asals,
                $unit_kerja_tujuans,
                $jabatan_asals,
                $jabatan_tujuans,
                $alasan,
                $tanggal_mulai
            ));
        }

        return response()->json(new TransferKaryawanResource(Response::HTTP_OK, "Berhasil melakukan transfer karyawan {$users->nama}.", $transfer), Response::HTTP_OK);
    }

    public function exportTransferKaryawan(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new TransferExport($ids), 'transfer-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data transfer karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
