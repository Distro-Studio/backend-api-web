<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jawaban;
use App\Models\Penilaian;
use App\Models\Notifikasi;
use App\Models\Pertanyaan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\JenisPenilaian;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Perusahaan\PenilaianExport;
use App\Http\Requests\StorePenilaianKaryawanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PenilaianController extends Controller
{
    public function getUserDinilai(Request $request)
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jenisPenilaian = JenisPenilaian::find($request->input('jenis_penilaian_id'));
        if (!$jenisPenilaian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jenis penilaian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $jabatanDinilaiId = $jenisPenilaian->jabatan_dinilai;
        $statusKaryawanId = $jenisPenilaian->status_karyawan_id;

        // Ambil user yang sesuai dengan jabatan_dinilai, status_karyawan, dan belum pernah dinilai pada periode ini
        $usersDinilai = User::whereHas('data_karyawans', function ($query) use ($jabatanDinilaiId, $statusKaryawanId) {
            $query->where('jabatan_id', $jabatanDinilaiId)
                ->where('status_karyawan_id', $statusKaryawanId);
        })->whereDoesntHave('user_penilaian_dinilais', function ($query) use ($jenisPenilaian, $statusKaryawanId) {
            $query->where('jenis_penilaian_id', $jenisPenilaian->id)
                ->where(function ($query) use ($statusKaryawanId) {
                    if ($statusKaryawanId == 3) {
                        // Periode 3 bulan sekali untuk status_karyawan_id = 3
                        $query->where('created_at', '>=', now()->subMonths(3));
                    } else {
                        // Periode 1 tahun sekali untuk status_karyawan_id = 1 & 2
                        $query->where('created_at', '>=', now()->subYear());
                    }
                })
                ->whereColumn('user_dinilai', 'users.id');
        })->where('nama', '!=', 'Super Admin')->get();

        if ($usersDinilai->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Tidak ada karyawan yang sesuai dengan kriteria yang belum dinilai.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Daftar karyawan yang belum dinilai berhasil ditampilkan.',
            'data' => $usersDinilai,
        ], Response::HTTP_OK);
    }

    public function getUserPenilai()
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Ambil user yang sedang login sebagai user penilai
        $userPenilai = auth()->user();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Karyawan penilai berhasil diambil.',
            'data' => $userPenilai,
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        // Ambil penilaian terbaru untuk setiap user_dinilai
        $penilaian = Penilaian::query()->orderBy('created_at', 'desc');

        $filters = $request->all();

        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $penilaian->whereHas('user_dinilais.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $penilaian->whereHas('user_dinilais.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $penilaian->whereHas('user_dinilais.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($bulan, $currentDate) {
                    $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $penilaian->whereHas('users', function ($query) use ($statusAktif) {
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
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->whereIn('tgl_masuk', $tglMasuk);
                });
            } else {
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->where('tgl_masuk', $tglMasuk);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $penilaian->whereHas('user_dinilais.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $penilaian->whereHas('user_dinilais.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $penilaian->whereHas('user_dinilais.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $penilaian->whereHas('user_dinilais.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        if (isset($filters['jenis_kompetensi'])) {
            $jenisKaryawan = $filters['jenis_kompetensi'];
            if (is_array($jenisKaryawan)) {
                $penilaian->whereHas('user_dinilais.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_kompetensi', $jk);
                        }
                    });
                });
            } else {
                $penilaian->whereHas('user_dinilais.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_kompetensi', $jenisKaryawan);
                });
            }
        }

        if (isset($filters['kategori_transfer'])) {
            $namaTransferKategori = $filters['kategori_transfer'];
            $penilaian->whereHas('user_dinilais.tranfer_karyawans.kategori_transfer_karyawans', function ($query) use ($namaTransferKategori) {
                if (is_array($namaTransferKategori)) {
                    $query->whereIn('id', $namaTransferKategori);
                } else {
                    $query->where('id', '=', $namaTransferKategori);
                }
            });
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $penilaian->where(function ($query) use ($searchTerm) {
                $query->whereHas('user_dinilais', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('user_dinilais.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($limit == 0) {
            $dataPenilaian = $penilaian->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataPenilaian = $penilaian->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataPenilaian->url(1),
                    'last' => $dataPenilaian->url($dataPenilaian->lastPage()),
                    'prev' => $dataPenilaian->previousPageUrl(),
                    'next' => $dataPenilaian->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataPenilaian->currentPage(),
                    'last_page' => $dataPenilaian->lastPage(),
                    'per_page' => $dataPenilaian->perPage(),
                    'total' => $dataPenilaian->total(),
                ]
            ];
        }

        // Format Data
        $formatData = $dataPenilaian->map(function ($penilaian) {
            return [
                'id' => $penilaian->id,
                'user_dinilai' => $penilaian->user_dinilais,
                'user_penilai' => $penilaian->user_penilais,
                'jenis_penilaian' => $penilaian->jenis_penilaians,
                'pertanyaan_jawaban' => $penilaian->pertanyaan_jawaban,
                'total_pertanyaan' => $penilaian->total_pertanyaan,
                'rata_rata' => $penilaian->rata_rata,
                'created_at' => $penilaian->created_at,
                'updated_at' => $penilaian->updated_at,
            ];
        });

        if ($dataPenilaian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penilaian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data penilaian berhasil ditampilkan.',
            'data' => $formatData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StorePenilaianKaryawanRequest $request)
    {
        if (!Gate::allows('create penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jenisPenilaianId = $data['jenis_penilaian_id'];

        // a. Cek apakah jenis penilaian tersedia
        $jenisPenilaian = JenisPenilaian::find($jenisPenilaianId);
        if (!$jenisPenilaian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jenis penilaian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // b. Cek apakah pertanyaan untuk jenis penilaian sudah ada
        $pertanyaans = Pertanyaan::where('jenis_penilaian_id', $jenisPenilaianId)->get();
        if ($pertanyaans->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada pertanyaan untuk jenis penilaian '{$jenisPenilaian->nama}'."), Response::HTTP_NOT_FOUND);
        }

        // Ambil user_dinilai dan validasi keberadaannya
        $userDinilai = User::find($data['user_dinilai']);
        if (!$userDinilai) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Karyawan dinilai tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // c. Cek apakah tgl_selesai dari jenis penilaian sudah terlewat
        $tglMulai = Carbon::parse($userDinilai->data_karyawans->tgl_masuk);
        $statusKaryawanId = $userDinilai->data_karyawans->status_karyawan_id;
        $currentDate = Carbon::now();

        if ($statusKaryawanId == 3) {
            // Periode 3 bulan
            if ($tglMulai->diffInMonths($currentDate) < 3) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Penilaian belum dapat dilakukan karena periode 3 bulan belum tercapai.'), Response::HTTP_BAD_REQUEST);
            }
        } elseif (in_array($statusKaryawanId, [1, 2])) {
            // Periode 1 tahun
            if ($tglMulai->diffInYears($currentDate) < 1) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Penilaian belum dapat dilakukan karena periode 1 tahun belum tercapai.'), Response::HTTP_BAD_REQUEST);
            }
        }

        $userDinilai = User::find($data['user_dinilai']);
        if (!$userDinilai) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Karyawan dinilai tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $penilaian = Penilaian::create([
            'user_dinilai' => $userDinilai->id,
            'user_penilai' => auth()->user()->id,
            'jenis_penilaian_id' => $data['jenis_penilaian_id'],
            'pertanyaan_jawaban' => json_decode($data['pertanyaan_jawaban']),
            'total_pertanyaan' => $data['total_pertanyaan'],
            'rata_rata' => $data['rata_rata'],
        ]);

        // Kirim notifikasi kepada user yang dinilai
        $this->createNotifikasiPenilaian($penilaian);

        return response()->json([
            'status' => Response::HTTP_CREATED,
            'message' => "Penilaian '{$jenisPenilaian->nama}' berhasil disimpan pada karyawan '{$userDinilai->nama}'.",
            'data' => $penilaian,
        ], Response::HTTP_CREATED);
    }

    public function getKaryawanBelumDinilai(Request $request)
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $filters = $request->all();

        $karyawanBelumDinilaiQuery = User::whereDoesntHave('user_penilaian_dinilais')
            ->whereHas('data_karyawans', function ($query) use ($filters) {
                if (isset($filters['jabatan'])) {
                    $jabatan = $filters['jabatan'];
                    if (is_array($jabatan)) {
                        $query->whereIn('jabatan_id', $jabatan);
                    } else {
                        $query->where('jabatan_id', '=', $jabatan);
                    }
                }

                if (isset($filters['status_karyawan'])) {
                    $status_karyawan = $filters['status_karyawan'];
                    if (is_array($status_karyawan)) {
                        $query->whereIn('status_karyawan_id', $status_karyawan);
                    } else {
                        $query->where('status_karyawan_id', '=', $status_karyawan);
                    }
                }
            })
            ->where('nama', '!=', 'Super Admin');
        $karyawanBelumDinilai = $karyawanBelumDinilaiQuery->get();

        if ($karyawanBelumDinilai->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Tidak ada karyawan yang belum dinilai.'
            ], Response::HTTP_NOT_FOUND);
        }

        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = $karyawanBelumDinilai->map(function ($user) use ($baseUrl) {
            return [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email_verified_at' => $user->email_verified_at,
                    'data_karyawan_id' => $user->data_karyawan_id,
                    'foto_profil' => $user->foto_profiles ? [
                        'id' => $user->foto_profiles->id,
                        'user_id' => $user->foto_profiles->user_id,
                        'file_id' => $user->foto_profiles->file_id,
                        'nama' => $user->foto_profiles->nama,
                        'nama_file' => $user->foto_profiles->nama_file,
                        'path' => $baseUrl . $user->foto_profiles->path,
                        'ext' => $user->foto_profiles->ext,
                        'size' => $user->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'status_karyawan' => $user->data_karyawans->status_karyawans ?? null,
                'jabatan' => $user->data_karyawans->jabatans ?? null
            ];
        });
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Daftar karyawan yang belum dinilai berhasil diambil.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function exportPenilaian()
    {
        if (!Gate::allows('export penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPenilaian = Penilaian::all();
        if ($dataPenilaian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data penilaian karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new PenilaianExport(), 'perusahaan-penilaian-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti atau hubungi SIM RS.'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiPenilaian($penilaian)
    {
        try {
            // Dapatkan user yang dinilai
            $userDinilai = $penilaian->user_dinilais;
            $message = "Anda memiliki penilaian '{$penilaian->jenis_penilaians->nama}', Silakan cek detail penilaian Anda.";
            $messageSuperAdmin = "Notifikasi untuk Super Admin: Karyawan '{$userDinilai->nama}' telah menerima penilaian '{$penilaian->jenis_penilaians->nama}'.";

            $userIds = [$userDinilai->id, 1];
            foreach ($userIds as $userId) {
                $messageToSend = $userId === 1 ? $messageSuperAdmin : $message;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 7,
                    'user_id' => $userId,
                    'message' => $messageToSend,
                    'is_read' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }

            Log::info("Notifikasi penilaian untuk user {$userDinilai->nama} berhasil dibuat.");
        } catch (\Exception $e) {
            Log::error('| Penilaian | - Error function createNotifikasiPenilaian: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti atau hubungi SIM RS.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // ini v2
    // public function store(Request $request)
    // {
    //     if (!Gate::allows('create penilaianKaryawan')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $jenisPenilaianId = $request->input('jenis_penilaian_id');

    //     // a. Cek apakah jenis penilaian tersedia
    //     $jenisPenilaian = JenisPenilaian::find($jenisPenilaianId);
    //     if (!$jenisPenilaian) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jenis penilaian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    //     }

    //     // b. Cek apakah pertanyaan untuk jenis penilaian sudah ada
    //     $pertanyaans = Pertanyaan::where('jenis_penilaian_id', $jenisPenilaianId)->get();
    //     if ($pertanyaans->isEmpty()) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada pertanyaan untuk jenis penilaian '{$jenisPenilaian->nama}'."), Response::HTTP_NOT_FOUND);
    //     }

    //     // c. Cek apakah ada jawaban untuk pertanyaan yang terkait dengan jenis penilaian ini
    //     $jawabanAda = Jawaban::whereIn('pertanyaan_id', $pertanyaans->pluck('id'))->exists();

    //     if (!$jawabanAda) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tidak ada jawaban untuk pertanyaan dengan jenis penilaian '{$jenisPenilaian->nama}'."), Response::HTTP_BAD_REQUEST);
    //     }

    //     // c. Cek apakah tgl_selesai dari jenis penilaian sudah terlewat
    //     $currentDate = Carbon::now();
    //     if (Carbon::parse($jenisPenilaian->tgl_selesai)->isAfter($currentDate)) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Penilaian belum dapat dilakukan karena tanggal selesai yang ditentukan pada jenis penilaian belum terlewat.'), Response::HTTP_BAD_REQUEST);
    //     }

    //     // d. Cek apakah penilaian sudah dilakukan untuk periode ini
    //     $penilaian = Penilaian::where('jenis_penilaian_id', $jenisPenilaianId)
    //         ->where('periode', $jenisPenilaian->tgl_mulai)
    //         ->first();

    //     if ($penilaian) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Penilaian '{$jenisPenilaian->nama}' untuk periode ini sudah dilakukan."), Response::HTTP_BAD_REQUEST);
    //         return response()->json(['message' => 'Penilaian untuk periode ini sudah dilakukan.'], 400);
    //     }

    //     // e. Loop melalui semua karyawan berdasarkan status_karyawan_id dan lakukan validasi
    //     $users = User::whereHas('data_karyawans', function ($query) use ($jenisPenilaian) {
    //         $query->where('status_karyawan_id', $jenisPenilaian->status_karyawan_id);
    //     })->get();

    //     foreach ($users as $user) {
    //         $tglMasuk = Carbon::parse($user->data_karyawans->tgl_masuk);
    //         $interval = $jenisPenilaian->status_karyawan_id == 3 ? 3 : 12; // 3 bulan untuk status 3, 12 bulan untuk status 1 dan 2

    //         // Validasi apakah karyawan ini sudah mencapai interval yang dibutuhkan
    //         if ($currentDate->diffInMonths($tglMasuk) >= $interval) {
    //             // Lakukan perhitungan rata-rata untuk karyawan ini
    //             $rataRata = $this->calculateRataRata($jenisPenilaianId, $user->id);;

    //             // Simpan hasil penilaian untuk karyawan ini
    //             $newPenilaian = Penilaian::create([
    //                 'periode' => $jenisPenilaian->tgl_selesai,
    //                 'jenis_penilaian_id' => $jenisPenilaianId,
    //                 'total_pertanyaan' => $pertanyaans->count(),
    //                 'rata_rata' => $rataRata,
    //                 'user_dinilai' => $user->id, // Jika Anda ingin menyimpan penilaian per user
    //             ]);

    //             // Logika tambahan jika Anda perlu melakukan sesuatu setelah menyimpan penilaian
    //         }
    //     }

    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => "Penilaian berhasil dijalankan untuk semua karyawan '{$jenisPenilaian->status_karyawans->label}' yang memenuhi syarat.",
    //         // 'data' => $newPenilaian ?? 'Tidak ada penilaian yang dijalankan', // Jika tidak ada penilaian yang dilakukan
    //     ], Response::HTTP_OK);
    // }
}
