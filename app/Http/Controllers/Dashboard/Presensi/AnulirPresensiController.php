<?php

namespace App\Http\Controllers\Dashboard\Presensi;

use Carbon\Carbon;
use App\Models\Berkas;
use App\Models\Presensi;
use App\Helpers\LogHelper;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AnulirPresensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreAnulirPresensiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class AnulirPresensiController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view anulirPresensi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $loggedInUser = auth()->user();
            $isSuperAdmin = $loggedInUser->id == 1 || $loggedInUser->nama == 'Super Admin';

            $limit = $request->input('limit', 10);
            $presensiAnulir = AnulirPresensi::whereHas('data_karyawans', function ($query) {
                $query->where('id', '!=', 1)
                    ->orderBy('nik', 'asc');
            });
            $filters = $request->all();

            if (isset($filters['unit_kerja'])) {
                $namaUnitKerja = $filters['unit_kerja'];
                $presensiAnulir->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('id', $namaUnitKerja);
                    } else {
                        $query->where('id', '=', $namaUnitKerja);
                    }
                });
            }

            if (isset($filters['jabatan'])) {
                $namaJabatan = $filters['jabatan'];
                $presensiAnulir->whereHas('data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                    if (is_array($namaJabatan)) {
                        $query->whereIn('id', $namaJabatan);
                    } else {
                        $query->where('id', '=', $namaJabatan);
                    }
                });
            }

            if (isset($filters['status_karyawan'])) {
                $statusKaryawan = $filters['status_karyawan'];
                $presensiAnulir->whereHas('data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                        foreach ($masaKerja as $masa) {
                            $bulan = $masa * 12;
                            $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                        }
                    });
                } else {
                    $bulan = $masaKerja * 12;
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($bulan, $currentDate) {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    });
                }
            }

            if (isset($filters['status_aktif'])) {
                $statusAktif = $filters['status_aktif'];
                $presensiAnulir->whereHas('users', function ($query) use ($statusAktif) {
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
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                        $query->whereIn('tgl_masuk', $tglMasuk);
                    });
                } else {
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                        $query->where('tgl_masuk', $tglMasuk);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $namaAgama = $filters['agama'];
                $presensiAnulir->whereHas('data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    });
                } else {
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    });
                }
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $presensiAnulir->whereHas('data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                    $presensiAnulir->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    });
                } else {
                    $presensiAnulir->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['jenis_kompetensi'])) {
                $jenisKaryawan = $filters['jenis_kompetensi'];
                if (is_array($jenisKaryawan)) {
                    $presensiAnulir->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_kompetensi', $jk);
                            }
                        });
                    });
                } else {
                    $presensiAnulir->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_kompetensi', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $presensiAnulir->where(function ($query) use ($searchTerm) {
                    $query->whereHas('data_karyawans.users', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhere('nik', 'like', $searchTerm);
                });
            }

            if ($limit == 0) {
                $dataKaryawanAnulir = $presensiAnulir->get();
                $paginationData = null;
            } else {
                $limit = is_numeric($limit) ? (int)$limit : 10;
                $dataKaryawanAnulir = $presensiAnulir->paginate($limit);

                $paginationData = [
                    'links' => [
                        'first' => $dataKaryawanAnulir->url(1),
                        'last' => $dataKaryawanAnulir->url($dataKaryawanAnulir->lastPage()),
                        'prev' => $dataKaryawanAnulir->previousPageUrl(),
                        'next' => $dataKaryawanAnulir->nextPageUrl(),
                    ],
                    'meta' => [
                        'current_page' => $dataKaryawanAnulir->currentPage(),
                        'last_page' => $dataKaryawanAnulir->lastPage(),
                        'per_page' => $dataKaryawanAnulir->perPage(),
                        'total' => $dataKaryawanAnulir->total(),
                    ]
                ];
            }

            if ($dataKaryawanAnulir->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data karyawan anulir presensi tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = $dataKaryawanAnulir->map(function ($karyawanAnulir) use ($isSuperAdmin, $baseUrl) {
                $baseUrl = env('STORAGE_SERVER_DOMAIN');
                $cariBerkasAnulir = Berkas::where('id', $karyawanAnulir->dokumen_anulir_id)->first();
                $berkasAnulir = $cariBerkasAnulir ? $baseUrl . $cariBerkasAnulir->path : null;
                $role = $karyawanAnulir->data_karyawans->users->roles->first();

                return [
                    'id' => $karyawanAnulir->id,
                    'data_karyawan' => $karyawanAnulir->data_karyawans ? [
                        'id' => $karyawanAnulir->data_karyawans->id,
                        'user' => [
                            'id' => $karyawanAnulir->data_karyawans->users->id,
                            'nama' => $karyawanAnulir->data_karyawans->users->nama,
                            'username' => $karyawanAnulir->data_karyawans->users->username,
                            'email_verified_at' => $karyawanAnulir->data_karyawans->users->email_verified_at,
                            'data_karyawan_id' => $karyawanAnulir->data_karyawans->users->data_karyawan_id,
                            'foto_profil' => $karyawanAnulir->data_karyawans->users->foto_profiles ? [
                                'id' => $karyawanAnulir->data_karyawans->users->foto_profiles->id,
                                'user_id' => $karyawanAnulir->data_karyawans->users->foto_profiles->user_id,
                                'file_id' => $karyawanAnulir->data_karyawans->users->foto_profiles->file_id,
                                'nama' => $karyawanAnulir->data_karyawans->users->foto_profiles->nama,
                                'nama_file' => $karyawanAnulir->data_karyawans->users->foto_profiles->nama_file,
                                'path' => $baseUrl . $karyawanAnulir->data_karyawans->users->foto_profiles->path,
                                'ext' => $karyawanAnulir->data_karyawans->users->foto_profiles->ext,
                                'size' => $karyawanAnulir->data_karyawans->users->foto_profiles->size,
                            ] : null,
                            'data_completion_step' => $karyawanAnulir->data_karyawans->users->data_completion_step,
                            'status_aktif' => $karyawanAnulir->data_karyawans->users->status_aktif,
                            'tgl_dinonaktifkan' => $karyawanAnulir->data_karyawans->users->tgl_dinonaktifkan,
                            'alasan' => $karyawanAnulir->data_karyawans->users->alasan,
                            'created_at' => $karyawanAnulir->data_karyawans->users->created_at,
                            'updated_at' => $karyawanAnulir->data_karyawans->users->updated_at
                        ],
                        'role' => $isSuperAdmin ? [
                            'id' => $role->id,
                            'name' => $role->name,
                            'deskripsi' => $role->deskripsi,
                            'created_at' => $role->created_at,
                            'updated_at' => $role->updated_at
                        ] : null,
                        'email' => $karyawanAnulir->data_karyawans->email,
                        'nik' => $karyawanAnulir->data_karyawans->nik,
                        'nik_ktp' => $karyawanAnulir->data_karyawans->nik_ktp,
                        'status_karyawan' => $karyawanAnulir->data_karyawans->status_karyawans,
                        'tempat_lahir' => $karyawanAnulir->data_karyawans->tempat_lahir,
                        'tgl_lahir' => $karyawanAnulir->data_karyawans->tgl_lahir,
                        'no_kk' => $karyawanAnulir->data_karyawans->no_kk,
                        'alamat' => $karyawanAnulir->data_karyawans->alamat,
                        'gelar_depan' => $karyawanAnulir->data_karyawans->gelar_depan,
                        'gelar_belakang' => $karyawanAnulir->data_karyawans->gelar_belakang,
                        'no_hp' => $karyawanAnulir->data_karyawans->no_hp,
                        'jenis_kelamin' => $karyawanAnulir->data_karyawans->jenis_kelamin,
                        'status_reward_presensi' => $karyawanAnulir->data_karyawans->status_reward_presensi,
                        'created_at' => $karyawanAnulir->data_karyawans->created_at,
                        'updated_at' => $karyawanAnulir->data_karyawans->updated_at
                    ] : null,
                    'presensi' => $karyawanAnulir->presensis ? [
                        'id' => $karyawanAnulir->presensis->id,
                        'user' => $karyawanAnulir->presensis->users,
                        'unit_kerja' => $karyawanAnulir->presensis->data_karyawans->unit_kerjas,
                        'jadwal' => [
                            'id' => $karyawanAnulir->presensis->jadwals->id ?? null,
                            'tgl_mulai' => $karyawanAnulir->presensis->jadwals->tgl_mulai ?? null,
                            'tgl_selesai' => $karyawanAnulir->presensis->jadwals->tgl_selesai ?? null,
                            'shift' => $karyawanAnulir->presensis->jadwals->shifts ?? null,
                        ],
                        'jam_masuk' => $karyawanAnulir->presensis->jam_masuk,
                        'jam_keluar' => $karyawanAnulir->presensis->jam_keluar,
                        'durasi' => $karyawanAnulir->presensis->durasi,
                        'kategori_presensi' => $karyawanAnulir->presensis->kategori_presensis,
                        'created_at' => $karyawanAnulir->presensis->created_at,
                        'updated_at' => $karyawanAnulir->presensis->updated_at
                    ] : null,
                    'alasan' => $karyawanAnulir->alasan,
                    'dokumen_anulir' => $karyawanAnulir->dokumen_anulir ? [
                        'id' => $karyawanAnulir->dokumen_anulir->id,
                        'user_id' => $karyawanAnulir->dokumen_anulir->user_id,
                        'file_id' => $karyawanAnulir->dokumen_anulir->file_id,
                        'nama' => $karyawanAnulir->dokumen_anulir->nama,
                        'nama_file' => $karyawanAnulir->dokumen_anulir->nama_file,
                        'path' => $berkasAnulir,
                        'ext' => $karyawanAnulir->dokumen_anulir->ext,
                        'size' => $karyawanAnulir->dokumen_anulir->size,
                    ] : null,
                    'created_at' => $karyawanAnulir->created_at,
                    'updated_at' => $karyawanAnulir->updated_at,
                    'deleted_at' => $karyawanAnulir->deleted_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data karyawan anulir presensi berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Anulir Presensi | - Error function index: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreAnulirPresensiRequest $request, $presensi_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('Super Admin')) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            if (!Gate::allows('create anulirPresensi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $presensi = Presensi::where('id', $presensi_id)->first();
            if (!$presensi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Presensi tersebut tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $karyawanAnulir = DataKaryawan::where('id', $presensi->data_karyawan_id)->first();
            if (!$karyawanAnulir) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Karyawan yang melakukan presensi tersebut tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $request->validated();

            // Validasi 4 tahap, Penggajian-Izin-Cuti-Presensi (bulan lalu)
            // Validasi 4 tahap, Izin-Cuti-Presensi (bulan ini)

            DB::beginTransaction();
            $dataAnulir = AnulirPresensi::create([
                'data_karyawan_id' => $presensi->data_karyawan_id,
                'presensi_id' => $presensi->id,
                'alasan' => $data['alasan'],
                'dokumen_anulir_id' => $data['dokumen_anulir_id'],
                'created_at' => now('Asia/Jakarta'),
                'updated_at' => now('Asia/Jakarta'),
            ]);

            if ($karyawanAnulir->status_reward_presensi === 1) {
                $karyawanAnulir->update([
                    'status_reward_presensi' => 0,
                ]);
            }
            DB::commit();

            LogHelper::logAction('Anulir Presensi', 'create', $presensi->data_karyawan_id);

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Data anulir presensi dari karyawan '{$karyawanAnulir->users->nama}' berhasil ditambahkan."
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Anulir Presensi | - Error function store: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
