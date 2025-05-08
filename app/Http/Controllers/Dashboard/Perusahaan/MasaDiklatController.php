<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use App\Exports\Perusahaan\MasaDiklatExport;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Models\PesertaDiklat;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Maatwebsite\Excel\Facades\Excel;

class MasaDiklatController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (! Gate::allows('view diklat')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $loggedInUser = auth()->user();
            $isSuperAdmin = $loggedInUser->id == 1 || $loggedInUser->nama == 'Super Admin';

            $limit = $request->input('limit', 10);
            $karyawan = DataKaryawan::where('id', '!=', 1)
                ->whereHas('users', function ($query) {
                    $query->where('status_aktif', 2);
                })
                ->orderBy('nik', 'asc');
            $filters = $request->all();

            if (isset($filters['less_than'])) {
                $karyawan->where('masa_diklat', '<=', $filters['less_than']);
            }

            if (isset($filters['more_than'])) {
                $karyawan->where('masa_diklat', '>=', $filters['more_than']);
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
                    'message' => 'Data karyawan tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = $dataKaryawan->map(function ($karyawan) use ($isSuperAdmin, $baseUrl) {
                $role = $karyawan->users->roles->first();
                $joinedDiklat = PesertaDiklat::with('diklats')
                    ->where('peserta', $karyawan->user_id)
                    ->get()
                    ->map(function ($peserta) {
                        return [
                            'id' => $peserta->diklats->id,
                            'nama_diklat' => $peserta->diklats->nama,
                            'kategori_diklat' => $peserta->diklats->kategori_diklats,
                            'status_diklat' => $peserta->diklats->status_diklats,
                            'deskripsi' => $peserta->diklats->deskripsi,
                            'tgl_mulai' => $peserta->diklats->tgl_mulai,
                            'tgl_selesai' => $peserta->diklats->tgl_selesai,
                            'jam_mulai' => $peserta->diklats->jam_mulai,
                            'jam_selesai' => $peserta->diklats->jam_selesai,
                            'durasi' => $peserta->diklats->durasi,
                            'lokasi' => $peserta->diklats->lokasi,
                            'created_at' => $peserta->diklats->created_at,
                            'updated_at' => $peserta->diklats->updated_at
                        ];
                    });

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
                    'masa_diklat' => $karyawan->masa_diklat,
                    'joined_diklat' => $joinedDiklat,
                    'total_diklat' => $joinedDiklat->count(),
                    'created_at' => $karyawan->created_at,
                    'updated_at' => $karyawan->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data karyawan berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Masa Diklat | - Error function index: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportMasaDiklat(Request $request)
    {
        try {
            if (!Gate::allows('export diklat')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            try {
                return Excel::download(new MasaDiklatExport($request->all()), 'perusahaan-masa-diklat.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Masa Diklat | - Error function exportMasaDiklat: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
