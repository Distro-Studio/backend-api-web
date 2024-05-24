<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Models\User;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Mail\SendAccoundUsersMail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Karyawan\KaryawanExport;
use App\Http\Requests\Excel_Import\ImportKaryawanRequest;
use App\Imports\Karyawan\KaryawanImport;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreDataKaryawanRequest;

use App\Http\Resources\Dashboard\Karyawan\KaryawanResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\TrackRecord;

class KaryawanController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllKaryawan()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKaryawan = DataKaryawan::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all karyawan for dropdown',
            'data' => $dataKaryawan
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $karyawan = DataKaryawan::query();

        // Filter
        if ($request->has('status_karyawan')) {
            if (is_array($request->status_karyawan)) {
                $karyawan->whereIn('status_karyawan', $request->status_karyawan);
            } else {
                $karyawan->where('status_karyawan', $request->status_karyawan);
            }
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $karyawan->with('unit_kerjas:id,nama_unit')
                ->whereHas('unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('nama_unit', $namaUnitKerja);
                    } else {
                        $query->where('nama_unit', '=', $namaUnitKerja);
                    }
                });
        }

        // Search
        if ($request->has('search')) {
            $karyawan = $karyawan->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                });
                $query->orWhereHas('unit_kerjas', function ($query) use ($searchTerm) {
                    $query->where('nama_unit', 'like', $searchTerm);
                });

                $query->orWhere('nik', 'like', $searchTerm)
                    ->orWhere('no_rm', 'like', $searchTerm)
                    ->orWhere('nik_ktp', 'like', $searchTerm)
                    ->orWhere('status_karyawan', 'like', $searchTerm)
                    ->orWhere('tempat_lahir', 'like', $searchTerm)
                    ->orWhere('tgl_lahir', 'like', $searchTerm);
            });
        }

        $dataKaryawan = $karyawan->paginate(10);
        if ($dataKaryawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new KaryawanResource(Response::HTTP_OK, 'Data karyawan berhasil ditampilkan.', $dataKaryawan), Response::HTTP_OK);
    }

    public function store(StoreDataKaryawanRequest $request)
    {
        if (!Gate::allows('create dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $requestedRoleId = $request->input('role_id');

        DB::beginTransaction();
        try {
            $username = empty($data['username'])
                ? RandomHelper::generateUniqueUsername($data['nama'])
                : $data['username'];

            $password = empty($data['password'])
                ? RandomHelper::generatePassword($data['email'])
                : $data['password'];

            $userData = [
                'nama' => $data['nama'],
                'role_id' => $data['role_id'],
                'username' => $username,
                'password' => Hash::make($password)
            ];

            $createUser = User::create($userData);
            $createUser->roles()->attach($requestedRoleId);

            $createDataKaryawan = new DataKaryawan([
                'user_id' => $createUser->id,
                'email' => $data['email'],
                'no_rm' => $data['no_rm'],
                'no_manulife' => $data['no_manulife'],
                'tgl_masuk' => $data['tgl_masuk'],
                'unit_kerja_id' => $data['unit_kerja_id'],
                'jabatan_id' => $data['jabatan_id'],
                'kompetensi_id' => $data['kompetensi_id'],
                'status_karyawan' => $data['status_karyawan'],
                'kelompok_gaji_id' => $data['kelompok_gaji_id'],
                'no_rekening' => $data['no_rekening'],
                'tunjangan_jabatan' => $data['tunjangan_jabatan'],
                'tunjangan_fungsional' => $data['tunjangan_fungsional'],
                'tunjangan_khusus' => $data['tunjangan_khusus'],
                'tunjangan_lainnya' => $data['tunjangan_lainnya'],
                'uang_makan' => $data['uang_makan'],
                'uang_lembur' => $data['uang_lembur'],
                'ptkp_id' => $data['ptkp_id'],
            ]);

            $createRekamJejak = new TrackRecord([
                'user_id' => $createUser->id,
                'tgl_masuk' => $data['tgl_masuk'],
            ]);

            $createDataKaryawan->save();
            $createRekamJejak->save();

            DB::commit();

            Mail::to($data['email'])->send(new SendAccoundUsersMail($username, $password, $data['nama']));

            return response()->json(new KaryawanResource(Response::HTTP_OK, 'Data karyawan berhasil dibuat.', $createDataKaryawan), Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Maaf sepertinya pembuatan data karyawan bermasalah, Error: ' . $th->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }

    public function show(DataKaryawan $data_karyawan)
    {
        if (!Gate::allows('view dataKaryawan', $data_karyawan)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$data_karyawan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new KaryawanResource(Response::HTTP_OK, 'Data karyawan ditemukan.', $data_karyawan), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKaryawan = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:data_karyawans,id'
        ]);

        if ($dataKaryawan->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataKaryawan->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');

        $employeesToDelete = DataKaryawan::whereIn('id', $ids)
            ->with('users') // relationship
            ->get();

        $deletedCount = 0;

        foreach ($employeesToDelete as $employee) {
            if ($employee->users) {
                $employee->users->delete(); // Delete associated user
                $employee->delete(); // Delete employee data
                $deletedCount++;
            }
        }
        $message = "Total $deletedCount karyawan berhasil dihapus beserta user terkait.";

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportKaryawan(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new KaryawanExport($ids), 'data-karyawans.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function importKaryawan(ImportKaryawanRequest $request)
    {
        if (!Gate::allows('import dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new KaryawanImport, $file['karyawan_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
