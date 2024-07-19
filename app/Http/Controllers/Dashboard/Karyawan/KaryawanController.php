<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\TrackRecord;
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
use App\Imports\Karyawan\KaryawanImport;
use App\Jobs\Keuangan\StorePenggajianJob;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\StoreDataKaryawanRequest;
use App\Jobs\EmailNotification\AccountEmailJob;
use App\Http\Requests\UpdateDataKaryawanRequest;
use App\Http\Requests\CreateCredentialsDataKaryawan;
use App\Http\Requests\Excel_Import\ImportKaryawanRequest;
use App\Http\Resources\Dashboard\Karyawan\KaryawanResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class KaryawanController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllKaryawan()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKaryawan = DataKaryawan::with('users', 'unit_kerjas', 'jabatans', 'kompetensis', 'kelompok_gajis', 'ptkps')->get();
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

        $karyawan = DataKaryawan::query()->with(['users', 'unit_kerjas', 'jabatans', 'kompetensis', 'kelompok_gajis', 'ptkps']);

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

            $karyawan->whereHas('unit_kerjas', function ($query) use ($namaUnitKerja) {
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
                    ->orWhere('no_rm', 'like', $searchTerm);
            });
        }

        $dataKaryawan = $karyawan->paginate(10);
        if ($dataKaryawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data untuk output
        $formattedData = $dataKaryawan->items();
        $formattedData = array_map(function ($karyawan) {
            return [
                'id' => $karyawan->id,
                'user' => $karyawan->users,
                'nik' => $karyawan->nik,
                'no_rm' => $karyawan->no_rm,
                'unit_kerja' => $karyawan->unit_kerjas ?? null,
                'status_karyawan' => $karyawan->status_karyawan,
                'created_at' => $karyawan->created_at,
                'updated_at' => $karyawan->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $dataKaryawan->url(1),
                'last' => $dataKaryawan->url($dataKaryawan->lastPage()),
                'prev' => $dataKaryawan->previousPageUrl(),
                'next' => $dataKaryawan->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $dataKaryawan->currentPage(),
                'last_page' => $dataKaryawan->lastPage(),
                'per_page' => $dataKaryawan->perPage(),
                'total' => $dataKaryawan->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function generateCredentials(CreateCredentialsDataKaryawan $request)
    {
        $data = $request->validated();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data credentials karyawan berhasil digenerate.',
            'data' => [
                'username' => RandomHelper::generateUniqueUsername($data['nama'], $data['email']),
                'password' => RandomHelper::generatePassword()
            ]
        ], Response::HTTP_OK);
    }

    public function store(StoreDataKaryawanRequest $request)
    {
        if (!Gate::allows('create dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $requestedRoleId = $request->input('role_id');
        $premis = $request->input('potongan', []); // Mengambil daftar premi yang dipilih

        DB::beginTransaction();
        try {
            $userData = [
                'nama' => $data['nama'],
                'role_id' => $data['role_id'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
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
            $createDataKaryawan->save();

            // Masukkan data ke tabel pengurang_gajis jika ada premi yang dipilih
            if (!empty($premis)) {
                $premisData = DB::table('premis')->whereIn('id', $premis)->get();
                if ($premisData->isEmpty()) {
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Potongan yang dipilih tidak valid.'), Response::HTTP_NOT_FOUND);
                }

                foreach ($premisData as $premi) {
                    DB::table('pengurang_gajis')->insert([
                        'data_karyawan_id' => $createDataKaryawan->id,
                        'premi_id' => $premi->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            DB::commit();

            // AccountEmailJob::dispatch($data['email'], $data['username'], $data['password'], $data['nama']);

            // Mail::to($data['email'])->send(new SendAccoundUsersMail($data['username'], $data['password'], $data['nama']));
            return response()->json(new KaryawanResource(Response::HTTP_OK, 'Data karyawan berhasil dibuat.', $createDataKaryawan), Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Maaf sepertinya pembuatan data karyawan bermasalah, Error: ' . $th->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }

    public function show($id)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $karyawan = DataKaryawan::with(['users', 'unit_kerjas', 'jabatans', 'kompetensis', 'kelompok_gajis', 'ptkps'])->find($id);

        if (!$karyawan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = [
            'id' => $karyawan->id,
            'user' => $karyawan->users,
            "email" => $karyawan->email,
            'no_rm' => $karyawan->no_rm,
            'no_manulife' => $karyawan->no_manulife,
            'tgl_masuk' => $karyawan->tgl_masuk,
            'unit_kerja' => $karyawan->unit_kerjas ?? null,
            'jabatan' => $karyawan->jabatans ?? null,
            'kompetensi' => $karyawan->kompetensis ?? null,
            'role' => $karyawan->users->roles ?? null,
            "nik" => $karyawan->nik,
            "nik_ktp" => $karyawan->nik_ktp,
            'status_karyawan' => $karyawan->status_karyawan,
            'tempat_lahir' => $karyawan->tempat_lahir,
            'tgl_lahir' => $karyawan->tgl_lahir,
            'kelompok_gaji' => $karyawan->kelompok_gajis ?? null,
            'no_rekening' => $karyawan->no_rekening,
            'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
            'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
            'tunjangan_khusus' => $karyawan->tunjangan_khusus,
            'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
            'uang_lembur' => $karyawan->uang_lembur,
            'uang_makan' => $karyawan->uang_makan,
            'ptkp' => $karyawan->ptkps ?? null,
            "tgl_keluar" => $karyawan->tgl_keluar,
            "no_kk" => $karyawan->no_kk,
            "alamat" => $karyawan->alamat,
            "gelar_depan" => $karyawan->gelar_depan,
            "no_hp" => $karyawan->no_hp,
            "no_bpjsksh" => $karyawan->no_bpjsksh,
            "no_bpjsktk" => $karyawan->no_bpjsktk,
            "tgl_diangkat" => $karyawan->tgl_diangkat,
            "masa_kerja" => $karyawan->masa_kerja,
            "npwp" => $karyawan->npwp,
            "jenis_kelamin" => $karyawan->jenis_kelamin,
            "agama" => $karyawan->agama,
            "golongan_darah" => $karyawan->golongan_darah,
            "tinggi_badan" => $karyawan->tinggi_badan,
            "berat_badan" => $karyawan->berat_badan,
            "no_ijazah" => $karyawan->no_ijazah,
            "tahun_lulus" => $karyawan->tahun_lulus,
            "no_str" => $karyawan->no_str,
            "masa_berlaku_str" => $karyawan->masa_berlaku_str,
            "no_sip" => $karyawan->no_sip,
            "masa_berlaku_sip" => $karyawan->masa_berlaku_sip,
            "tgl_berakhir_pks" => $karyawan->tgl_berakhir_pks,
            "masa_diklat" => $karyawan->masa_diklat,
            'created_at' => $karyawan->created_at,
            'updated_at' => $karyawan->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Detail karyawan berhasil ditampilkan.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update(UpdateDataKaryawanRequest $request, $id)
    {
        if (!Gate::allows('edit dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $karyawan = DataKaryawan::find($id);
        $karyawan->update($data);

        if (!$karyawan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = [
            'id' => $karyawan->id,
            'user' => $karyawan->users,
            "email" => $karyawan->email,
            'no_rm' => $karyawan->no_rm,
            'no_manulife' => $karyawan->no_manulife,
            'tgl_masuk' => $karyawan->tgl_masuk,
            'unit_kerja' => $karyawan->unit_kerjas ?? null,
            'jabatan' => $karyawan->jabatans ?? null,
            'kompetensi' => $karyawan->kompetensis ?? null,
            'role' => $karyawan->users->roles ?? null,
            "nik" => $karyawan->nik,
            "nik_ktp" => $karyawan->nik_ktp,
            'status_karyawan' => $karyawan->status_karyawan,
            'tempat_lahir' => $karyawan->tempat_lahir,
            'tgl_lahir' => $karyawan->tgl_lahir,
            'kelompok_gaji' => $karyawan->kelompok_gajis ?? null,
            'no_rekening' => $karyawan->no_rekening,
            'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
            'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
            'tunjangan_khusus' => $karyawan->tunjangan_khusus,
            'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
            'uang_lembur' => $karyawan->uang_lembur,
            'uang_makan' => $karyawan->uang_makan,
            'ptkp' => $karyawan->ptkps ?? null,
            "tgl_keluar" => $karyawan->tgl_keluar,
            "no_kk" => $karyawan->no_kk,
            "alamat" => $karyawan->alamat,
            "gelar_depan" => $karyawan->gelar_depan,
            "no_hp" => $karyawan->no_hp,
            "no_bpjsksh" => $karyawan->no_bpjsksh,
            "no_bpjsktk" => $karyawan->no_bpjsktk,
            "tgl_diangkat" => $karyawan->tgl_diangkat,
            "masa_kerja" => $karyawan->masa_kerja,
            "npwp" => $karyawan->npwp,
            "jenis_kelamin" => $karyawan->jenis_kelamin,
            "agama" => $karyawan->agama,
            "golongan_darah" => $karyawan->golongan_darah,
            "tinggi_badan" => $karyawan->tinggi_badan,
            "berat_badan" => $karyawan->berat_badan,
            "no_ijazah" => $karyawan->no_ijazah,
            "tahun_lulus" => $karyawan->tahun_lulus,
            "no_str" => $karyawan->no_str,
            "masa_berlaku_str" => $karyawan->masa_berlaku_str,
            "no_sip" => $karyawan->no_sip,
            "masa_berlaku_sip" => $karyawan->masa_berlaku_sip,
            "tgl_berakhir_pks" => $karyawan->tgl_berakhir_pks,
            "masa_diklat" => $karyawan->masa_diklat,
            'created_at' => $karyawan->created_at,
            'updated_at' => $karyawan->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data karyawan berhasil diperbarui.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function exportKaryawan(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new KaryawanExport(), 'data-karyawan.xls');
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
