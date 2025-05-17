<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use App\Exports\Jadwal\HakCutiExport;
use Carbon\Carbon;
use App\Models\HakCuti;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreHakCutiRequest;
use App\Http\Requests\UpdateHakCutiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\TipeCuti;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DataHakCutiController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view hakCuti')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $limit = $request->input('limit', 10);
            $hakCuti =  HakCuti::join('data_karyawans', 'hak_cutis.data_karyawan_id', '=', 'data_karyawans.id')
                ->orderBy('data_karyawans.nik', 'asc')
                ->select('hak_cutis.*');
            $filters = $request->all();

            // Filter
            if (isset($filters['unit_kerja'])) {
                $namaUnitKerja = $filters['unit_kerja'];
                $hakCuti->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('id', $namaUnitKerja);
                    } else {
                        $query->where('id', '=', $namaUnitKerja);
                    }
                });
            }

            if (isset($filters['jabatan'])) {
                $namaJabatan = $filters['jabatan'];
                $hakCuti->whereHas('data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                    if (is_array($namaJabatan)) {
                        $query->whereIn('id', $namaJabatan);
                    } else {
                        $query->where('id', '=', $namaJabatan);
                    }
                });
            }

            if (isset($filters['status_karyawan'])) {
                $statusKaryawan = $filters['status_karyawan'];
                $hakCuti->whereHas('data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
                    if (is_array($statusKaryawan)) {
                        $query->whereIn('id', $statusKaryawan);
                    } else {
                        $query->where('id', '=', $statusKaryawan);
                    }
                });
            }

            if (isset($filters['masa_kerja'])) {
                $masaKerja = $filters['masa_kerja'];
                $currentDate = Carbon::now('Asia/Jakarta');
                if (is_array($masaKerja)) {
                    $hakCuti->whereHas('data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                        foreach ($masaKerja as $masa) {
                            $bulan = $masa * 12;
                            $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                        }
                    });
                } else {
                    $bulan = $masaKerja * 12;
                    $hakCuti->whereHas('data_karyawans', function ($query) use ($bulan, $currentDate) {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    });
                }
            }

            if (isset($filters['status_aktif'])) {
                $statusAktif = $filters['status_aktif'];
                $hakCuti->whereHas('data_karyawans.users', function ($query) use ($statusAktif) {
                    if (is_array($statusAktif)) {
                        $query->whereIn('status_aktif', $statusAktif);
                    } else {
                        $query->where('status_aktif', '=', $statusAktif);
                    }
                });
            }

            if (isset($filters['tgl_masuk'])) {
                $tglMasuk = $filters['tgl_masuk'];
                if (is_array($tglMasuk)) {
                    $hakCuti->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                        $query->whereIn('tgl_masuk', $tglMasuk);
                    });
                } else {
                    $hakCuti->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                        $query->where('tgl_masuk', $tglMasuk);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $namaAgama = $filters['agama'];
                $hakCuti->whereHas('data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
                    if (is_array($namaAgama)) {
                        $query->whereIn('id', $namaAgama);
                    } else {
                        $query->where('id', '=', $namaAgama);
                    }
                });
            }

            if (isset($filters['jenis_kelamin'])) {
                $jenisKelamin = $filters['jenis_kelamin'];
                if (is_array($jenisKelamin)) {
                    $hakCuti->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    });
                } else {
                    $hakCuti->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    });
                }
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $hakCuti->whereHas('data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
                    if (is_array($namaPendidikan)) {
                        $query->whereIn('id', $namaPendidikan);
                    } else {
                        $query->where('id', '=', $namaPendidikan);
                    }
                });
            }

            if (isset($filters['jenis_karyawan'])) {
                $jenisKaryawan = $filters['jenis_karyawan'];
                if (is_array($jenisKaryawan)) {
                    $hakCuti->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    });
                } else {
                    $hakCuti->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['jenis_kompetensi'])) {
                $jenisKaryawan = $filters['jenis_kompetensi'];
                if (is_array($jenisKaryawan)) {
                    $hakCuti->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_kompetensi', $jk);
                            }
                        });
                    });
                } else {
                    $hakCuti->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_kompetensi', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['tipe_cuti'])) {
                $namaTipeCuti = $filters['tipe_cuti'];
                $hakCuti->whereHas('tipe_cutis', function ($query) use ($namaTipeCuti) {
                    if (is_array($namaTipeCuti)) {
                        $query->whereIn('id', $namaTipeCuti);
                    } else {
                        $query->where('id', '=', $namaTipeCuti);
                    }
                });
            }

            if (isset($filters['status_cuti'])) {
                $namaStatusCuti = $filters['status_cuti'];
                $hakCuti->whereHas('status_cutis', function ($query) use ($namaStatusCuti) {
                    if (is_array($namaStatusCuti)) {
                        $query->whereIn('id', $namaStatusCuti);
                    } else {
                        $query->where('id', '=', $namaStatusCuti);
                    }
                });
            }

            // Search
            if (isset($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $hakCuti->where(function ($query) use ($searchTerm) {
                    $query->whereHas('data_karyawans.users', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhereHas('data_karyawans', function ($query) use ($searchTerm) {
                        $query->where('nik', 'like', $searchTerm);
                    });
                });
            }

            // Ambil semua ID karyawan dari hak_cuti
            $allDataKaryawanIds = $hakCuti->pluck('data_karyawan_id')->unique();

            if ($limit == 0) {
                $paginatedIds = $allDataKaryawanIds;
                $paginationData = null;
            } else {
                $limit = is_numeric($limit) ? (int) $limit : 10;
                $page = $request->input('page', 1);
                $paginatedIds = $allDataKaryawanIds->slice(($page - 1) * $limit, $limit);
                $paginationData = [
                    'links' => [
                        'first' => url()->current() . '?page=1',
                        'last' => url()->current() . '?page=' . ceil($allDataKaryawanIds->count() / $limit),
                        'prev' => $page > 1 ? url()->current() . '?page=' . ($page - 1) : null,
                        'next' => $page < ceil($allDataKaryawanIds->count() / $limit) ? url()->current() . '?page=' . ($page + 1) : null,
                    ],
                    'meta' => [
                        'current_page' => $page,
                        'last_page' => ceil($allDataKaryawanIds->count() / $limit),
                        'per_page' => $limit,
                        'total' => $allDataKaryawanIds->count(),
                    ]
                ];
            }

            // Ambil ulang semua hak cuti berdasarkan data_karyawan_id yang sudah dipaginate
            $dataHakCuti = HakCuti::whereIn('data_karyawan_id', $paginatedIds)
                ->get();
            if ($dataHakCuti->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data hak cuti karyawan tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $groupedHakCuti = $dataHakCuti->groupBy('data_karyawan_id');
            $formattedData = $groupedHakCuti->map(function ($group) use ($baseUrl) {
                $first = $group->first(); // ambil salah satu untuk akses user dan karyawan

                return [
                    'id' => $first->data_karyawan_id,
                    // 'ids' => $group->pluck('id')->toArray(),
                    'user' => [
                        'id' => $first->data_karyawans->users->id,
                        'nama' => $first->data_karyawans->users->nama,
                        'username' => $first->data_karyawans->users->username,
                        'email_verified_at' => $first->data_karyawans->users->email_verified_at,
                        'data_karyawan_id' => $first->data_karyawans->users->data_karyawan_id,
                        'foto_profil' => $first->data_karyawans->users->foto_profiles ? [
                            'id' => $first->data_karyawans->users->foto_profiles->id,
                            'user_id' => $first->data_karyawans->users->foto_profiles->user_id,
                            'file_id' => $first->data_karyawans->users->foto_profiles->file_id,
                            'nama' => $first->data_karyawans->users->foto_profiles->nama,
                            'nama_file' => $first->data_karyawans->users->foto_profiles->nama_file,
                            'path' => $baseUrl . $first->data_karyawans->users->foto_profiles->path,
                            'ext' => $first->data_karyawans->users->foto_profiles->ext,
                            'size' => $first->data_karyawans->users->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $first->data_karyawans->users->data_completion_step,
                        'status_aktif' => $first->data_karyawans->users->status_aktif,
                        'created_at' => $first->data_karyawans->users->created_at,
                        'updated_at' => $first->data_karyawans->users->updated_at
                    ],
                    'nik' => $first->data_karyawans->nik,
                    'hak_cuti' => $group->filter(function ($hakCuti) {
                        return $hakCuti->tipe_cutis !== null;
                    })->map(function ($hakCuti) {
                        return [
                            'id' => $hakCuti->tipe_cutis->id,
                            'nama' => $hakCuti->tipe_cutis->nama,
                            'kuota' => $hakCuti->kuota,
                            'is_need_requirement' => $hakCuti->tipe_cutis->is_need_requirement,
                            'keterangan' => $hakCuti->tipe_cutis->keterangan,
                            'cuti_administratif' => $hakCuti->tipe_cutis->cuti_administratif,
                            'is_unlimited' => $hakCuti->tipe_cutis->is_unlimited,
                            'created_at' => $hakCuti->tipe_cutis->created_at,
                            'updated_at' => $hakCuti->tipe_cutis->updated_at
                        ];
                    })->values(),
                    'created_at' => $first->created_at,
                    'updated_at' => $first->updated_at,
                    'deleted_at' => $first->deleted_at
                ];
            })->values();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data hak cuti karyawan berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Hak Cuti | - Error function index: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreHakCutiRequest $request)
    {
        DB::beginTransaction();

        try {
            if (!Gate::allows('create hakCuti')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();
            $dataKaryawanId = $data['data_karyawan_id'];
            $tipeCutiIds = $data['tipe_cuti_id'];

            // Ambil data nama karyawan
            $user = User::where('data_karyawan_id', $dataKaryawanId)->first();

            // Cek duplikasi
            $existing = HakCuti::where('data_karyawan_id', $dataKaryawanId)
                ->whereIn('tipe_cuti_id', $tipeCutiIds)
                ->pluck('tipe_cuti_id')
                ->toArray();
            if (!empty($existing)) {
                DB::rollBack();
                return response()->json([
                    'status' => Response::HTTP_CONFLICT,
                    'message' => "TIpe cuti yang dipilih sudah pernah diberikan kepada karyawan '{$user->nama}', silahkan cek kembali dan pastikan tidak ada duplikasi data."
                ], Response::HTTP_CONFLICT);
            }

            // Ambil kuota dari tabel tipe_cutis
            $tipeCutiData = TipeCuti::whereIn('id', $tipeCutiIds)->get()->keyBy('id');

            $insertData = [];
            $now = now('Asia/Jakarta');

            foreach ($tipeCutiIds as $tipeCutiId) {
                $insertData[] = [
                    'data_karyawan_id' => $dataKaryawanId,
                    'tipe_cuti_id' => $tipeCutiId,
                    'kuota' => $tipeCutiData[$tipeCutiId]->kuota ?? 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            HakCuti::insert($insertData);

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Data hak cuti karyawan '{$user->nama}' berhasil dibuat.",
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Hak Cuti | - Error function store: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            if (!Gate::allows('view hakCuti')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            if (!$id) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hak cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $hakCuti = HakCuti::where('data_karyawan_id', $id)
                ->get();
            if ($hakCuti->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hak cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $first = $hakCuti->first();

            $data = [
                'id' => $first->data_karyawan_id,
                'user' => [
                    'id' => $first->data_karyawans->users->id,
                    'nama' => $first->data_karyawans->users->nama,
                    'username' => $first->data_karyawans->users->username,
                    'email_verified_at' => $first->data_karyawans->users->email_verified_at,
                    'data_karyawan_id' => $first->data_karyawans->users->data_karyawan_id,
                    'foto_profil' => $first->data_karyawans->users->foto_profiles ? [
                        'id' => $first->data_karyawans->users->foto_profiles->id,
                        'user_id' => $first->data_karyawans->users->foto_profiles->user_id,
                        'file_id' => $first->data_karyawans->users->foto_profiles->file_id,
                        'nama' => $first->data_karyawans->users->foto_profiles->nama,
                        'nama_file' => $first->data_karyawans->users->foto_profiles->nama_file,
                        'path' => $baseUrl . $first->data_karyawans->users->foto_profiles->path,
                        'ext' => $first->data_karyawans->users->foto_profiles->ext,
                        'size' => $first->data_karyawans->users->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $first->data_karyawans->users->data_completion_step,
                    'status_aktif' => $first->data_karyawans->users->status_aktif,
                    'created_at' => $first->data_karyawans->users->created_at,
                    'updated_at' => $first->data_karyawans->users->updated_at
                ],
                'nik' => $first->data_karyawans->nik,
                'hak_cuti' => $hakCuti->map(function ($hakCuti) {
                    return $hakCuti->tipe_cutis !== null;
                })->map(function ($hakCuti) {
                    return [
                        'id' => $hakCuti->tipe_cutis->id,
                        'nama' => $hakCuti->tipe_cutis->nama,
                        'kuota' => $hakCuti->kuota,
                        'is_need_requirement' => $hakCuti->tipe_cutis->is_need_requirement,
                        'keterangan' => $hakCuti->tipe_cutis->keterangan,
                        'cuti_administratif' => $hakCuti->tipe_cutis->cuti_administratif,
                        'is_unlimited' => $hakCuti->tipe_cutis->is_unlimited,
                        'created_at' => $hakCuti->tipe_cutis->created_at,
                        'updated_at' => $hakCuti->tipe_cutis->updated_at
                    ];
                })->values(),
                'created_at' => $first->created_at,
                'updated_at' => $first->updated_at,
                'deleted_at' => $first->deleted_at
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data hak cuti karyawan berhasil ditampilkan.',
                'data' => $data
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Hak Cuti | - Error function show: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateHakCutiRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            if (!Gate::allows('edit hakCuti')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            if (!$id) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hak cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $dataKaryawanId = $id;

            // Validasi keberadaan user
            $user = User::where('data_karyawan_id', $dataKaryawanId)->first();
            if (!$user) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $cutiItems = $request->validated()['hak_cuti'];

            foreach ($cutiItems as $item) {
                HakCuti::updateOrCreate(
                    [
                        'data_karyawan_id' => $dataKaryawanId,
                        'tipe_cuti_id' => $item['id'],
                    ],
                    [
                        'kuota' => $item['kuota'],
                        'updated_at' => now('Asia/Jakarta'),
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data Hak cuti karyawan '{$user->nama}' berhasil diperbarui."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Hak Cuti | - Error function update: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            if (!Gate::allows('delete hakCuti')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            if (!$id) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hak cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil data nama karyawan
            $user = User::where('data_karyawan_id', $id)->first();

            $hakCuti = HakCuti::where('data_karyawan_id', $id)->get();
            if ($hakCuti->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hak cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            HakCuti::where('data_karyawan_id', $id)->delete();

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Semua data hak cuti karyawan '{$user->nama}' berhasil dihapus."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Hak Cuti | - Error function destroy: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            if (!Gate::allows('delete hakCuti')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            if (!$id) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hak cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil data nama karyawan
            $user = User::where('data_karyawan_id', $id)->first();

            $hakCuti = HakCuti::onlyTrashed()->where('data_karyawan_id', $id)->get();
            if ($hakCuti->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hak cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            HakCuti::onlyTrashed()->where('data_karyawan_id', $id)->restore();

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Semua data hak cuti karyawan '{$user->nama}' berhasil dipulihkan."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Hak Cuti | - Error function restore: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function export(Request $request)
    {
        try {
            if (!Gate::allows('export hakCuti')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataCuti = HakCuti::all();
            if ($dataCuti->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data hak cuti karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new HakCutiExport($request->all()), 'hak-cuti-karyawan.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Hak Cuti | - Error function export: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
