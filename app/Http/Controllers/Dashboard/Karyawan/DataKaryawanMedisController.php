<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Karyawan\KaryawanMedisExport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataKaryawanMedisController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $loggedInUser = auth()->user();
            $isSuperAdmin = $loggedInUser->id == 1 || $loggedInUser->nama == 'Super Admin';

            $limit = $request->input('limit', 10);
            $karyawan = DataKaryawan::where('id', '!=', 1)
                ->whereHas('users', function ($query) {
                    $query->where('status_aktif', 2);
                })
                ->whereHas('kompetensis', function ($query) {
                    $query->where('jenis_kompetensi', 1); // Khusus karyawan medis
                })
                ->orderBy('nik', 'asc');
            $filters = $request->all();

            if (isset($filters['masa_sip']) && is_numeric($filters['masa_sip'])) {
                $masaSip = (int) $filters['masa_sip'];
                $tanggalTargetSip = now('Asia/Jakarta')->addMonths($masaSip)->startOfDay();

                $karyawan->whereDate('masa_berlaku_sip', '>=', now('Asia/Jakarta')->startOfDay())
                    ->whereDate('masa_berlaku_sip', '<=', $tanggalTargetSip->endOfMonth());
            }

            if (isset($filters['masa_str']) && is_numeric($filters['masa_str'])) {
                $masaStr = (int) $filters['masa_str'];
                $tanggalTargetStr = now('Asia/Jakarta')->addMonths($masaStr)->startOfDay();

                $karyawan->whereDate('masa_berlaku_str', '>=', now('Asia/Jakarta')->startOfDay())
                    ->whereDate('masa_berlaku_str', '<=', $tanggalTargetStr->endOfMonth());
            }

            if (isset($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $karyawan->where(function ($query) use ($searchTerm) {
                    $query->whereHas('users', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhere('nik', 'like', $searchTerm);
                });
            }

            if ($limit == 0) {
                $dataKaryawan = $karyawan->get();
                $paginationData = null;
            } else {
                $limit = is_numeric($limit) ? (int)$limit : 10;
                $dataKaryawan = $karyawan->paginate($limit);

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
            }

            if ($dataKaryawan->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data karyawan medis tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = $dataKaryawan->map(function ($karyawan) use ($isSuperAdmin, $baseUrl) {
                $role = $karyawan->users->roles->first();

                return [
                    'id' => $karyawan->id,
                    'user' => [
                        'id' => $karyawan->users->id,
                        'nama' => $karyawan->users->nama,
                        'username' => $karyawan->users->username,
                        'email_verified_at' => $karyawan->users->email_verified_at,
                        'data_karyawan_id' => $karyawan->users->data_karyawan_id,
                        'foto_profil' => $karyawan->users->foto_profiles ? [
                            'id' => $karyawan->users->foto_profiles->id,
                            'user_id' => $karyawan->users->foto_profiles->user_id,
                            'file_id' => $karyawan->users->foto_profiles->file_id,
                            'nama' => $karyawan->users->foto_profiles->nama,
                            'nama_file' => $karyawan->users->foto_profiles->nama_file,
                            'path' => $baseUrl . $karyawan->users->foto_profiles->path,
                            'ext' => $karyawan->users->foto_profiles->ext,
                            'size' => $karyawan->users->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $karyawan->users->data_completion_step,
                        'status_aktif' => $karyawan->users->status_aktif,
                        'tgl_dinonaktifkan' => $karyawan->users->tgl_dinonaktifkan,
                        'alasan' => $karyawan->users->alasan,
                        'created_at' => $karyawan->users->created_at,
                        'updated_at' => $karyawan->users->updated_at
                    ],
                    'role' => $isSuperAdmin ? [
                        'id' => $role->id,
                        'name' => $role->name,
                        'deskripsi' => $role->deskripsi,
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at
                    ] : null,
                    'email' => $karyawan->email,
                    'nik' => $karyawan->nik,
                    'nik_ktp' => $karyawan->nik_ktp,
                    'status_karyawan' => $karyawan->status_karyawans, // status_karyawan_id
                    'tempat_lahir' => $karyawan->tempat_lahir,
                    'tgl_lahir' => $karyawan->tgl_lahir,
                    'no_kk' => $karyawan->no_kk,
                    'alamat' => $karyawan->alamat,
                    'gelar_depan' => $karyawan->gelar_depan,
                    'gelar_belakang' => $karyawan->gelar_belakang,
                    'no_hp' => $karyawan->no_hp,
                    'jenis_kelamin' => $karyawan->jenis_kelamin,

                    // Core data
                    'kompetensi' => $karyawan->kompetensis, // kompetensi_id
                    'no_str' => $karyawan->no_str,
                    'created_str' => $karyawan->created_str,
                    'masa_berlaku_str' => $karyawan->masa_berlaku_str,
                    'no_sip' => $karyawan->no_sip,
                    'created_sip' => $karyawan->created_sip,
                    'masa_berlaku_sip' => $karyawan->masa_berlaku_sip,
                    'created_at' => $karyawan->created_at,
                    'updated_at' => $karyawan->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data karyawan medis berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan Medis | - Error function index: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportKaryawanMedis(Request $request)
    {
        try {
            if (!Gate::allows('export dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            try {
                return Excel::download(new KaryawanMedisExport($request->all()), 'data-karyawan-medis.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Karyawan Medis | - Error function exportKaryawanMedis: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
