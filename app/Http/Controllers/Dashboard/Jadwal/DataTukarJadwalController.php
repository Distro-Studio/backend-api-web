<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\Lembur;
use App\Models\Notifikasi;
use App\Models\TukarJadwal;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\ModulVerifikasi;
use App\Models\RelasiVerifikasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\TukarJadwalExport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataTukarJadwalController extends Controller
{
    // ambil jadwal dari user pengajuan
    public function getJadwalPengajuan($userId)
    {
        try {
            if (!Gate::allows('view tukarJadwal')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $user = User::where('id', $userId)->where('nama', '!=', 'Super Admin')->where('status_aktif', 2)
                ->first();
            if (!$user) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Karyawan pengajuan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');
            $jadwal = Jadwal::with('shifts')
                ->where('user_id', $userId)
                ->where(function ($query) use ($today) {
                    // Filter schedules that start today or in the future, or end today or in the future
                    $query->where('tgl_mulai', '>=', $today)
                        ->orWhere('tgl_selesai', '>=', $today);
                })
                ->get();
            // $jadwal = Jadwal::with('shifts')->where('user_id', $userId)->get();
            if ($jadwal->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal karyawan pengajuan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil range tanggal untuk jadwal
            $start_date = $jadwal->min('tgl_mulai');
            $end_date = $jadwal->max('tgl_selesai');
            $date_range = $this->generateDateRange($start_date, $end_date);

            $user_schedule_array = $this->formatSchedules($jadwal, $date_range);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detai jadwall dan karyawan pengajuan berhasil ditampilkan.",
                'data' => [
                    'user' => $user,
                    'list_jadwal' => $user_schedule_array
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat get jadwal pengajuan karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUserDitukar($jadwalId)
    {
        try {
            if (!Gate::allows('view tukarJadwal')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $jadwal = Jadwal::find($jadwalId);
            if (!$jadwal) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal karyawan pengajuan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil data_karyawans dari user yang terkait dengan jadwal
            $dataKaryawan = $jadwal->users->data_karyawans()->first();
            if (!$dataKaryawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil unit kerja yang terkait dengan data karyawan
            $unitKerja = $dataKaryawan->unit_kerjas()->first();
            if (!$unitKerja) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Unit kerja tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $unitKerjaId = $unitKerja->id;

            // Gunakan helper untuk memastikan tanggal dikonversi dari format d/m/Y
            // $tglMulai = Carbon::parse($jadwal->tgl_mulai)->format('Y-m-d');
            // $tglSelesai = Carbon::parse($jadwal->tgl_selesai)->format('Y-m-d');

            $tglMulai = $jadwal->tgl_mulai;
            $tglSelesai = $jadwal->tgl_selesai;

            $users = User::whereHas('jadwals', function ($query) use ($jadwal, $tglMulai, $tglSelesai) {
                $query->where('shift_id', '!=', $jadwal->shift_id)
                    ->where(function ($query) use ($tglMulai, $tglSelesai) {
                        $query->where(function ($q) use ($tglMulai) {
                            $q->where('tgl_mulai', '<=', $tglMulai)
                                ->where('tgl_selesai', '>=', $tglMulai);
                        })
                            ->orWhere(function ($q) use ($tglSelesai) {
                                $q->where('tgl_mulai', '<=', $tglSelesai)
                                    ->where('tgl_selesai', '>=', $tglSelesai);
                            })
                            ->orWhere(function ($q) use ($tglMulai, $tglSelesai) {
                                $q->where('tgl_mulai', '>=', $tglMulai)
                                    ->where('tgl_selesai', '<=', $tglSelesai);
                            });
                    });
            })->whereHas('data_karyawans.unit_kerjas', function ($query) use ($unitKerjaId) {
                $query->where('id', $unitKerjaId);
            })->where('id', '!=', $jadwal->user_id)
                ->where('nama', '!=', 'Super Admin')
                ->get();

            if ($users->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Karyawan ditukar tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Karyawan ditukar berhasil didapatkan.",
                'data' => [
                    'user' => $users,
                    'unit_kerja' => $unitKerja
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat get karyawan ditukar: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ambil jadwal dari user ditukar
    public function getJadwalDitukar($userId)
    {
        try {
            if (!Gate::allows('view tukarJadwal')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $user = User::where('id', $userId)->where('nama', '!=', 'Super Admin')->where('status_aktif', 2)
                ->get();
            if (!$user) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Karyawan ditukar tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');
            $jadwal = Jadwal::with('shifts')
                ->where('user_id', $userId)
                ->where(function ($query) use ($today) {
                    // Filter schedules that start today or in the future, or end today or in the future
                    $query->where('tgl_mulai', '>=', $today)
                        ->orWhere('tgl_selesai', '>=', $today);
                })
                ->get();
            // $jadwal = Jadwal::with('shifts')->where('user_id', $userId)->get();
            if ($jadwal->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal karyawan ditukar tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $start_date = $jadwal->min('tgl_mulai');
            $end_date = $jadwal->max('tgl_selesai');
            $date_range = $this->generateDateRange($start_date, $end_date);

            $user_schedule_array = $this->formatSchedules($jadwal, $date_range);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detai jadwal dan karyawan ditukar berhasil ditampilkan.",
                'data' => [
                    'user' => $user,
                    'list_jadwal' => $user_schedule_array
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat get jadwal karyawan ditukar: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view tukarJadwal')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $user = auth()->user();
            $userId = $user->id;

            // Periksa apakah user adalah Super Admin
            $isSuperAdmin = $user->hasRole('Super Admin');

            // Jika bukan super admin, cek apakah dia verifikator untuk modul tukar jadwal
            $userDivVerifiedIds = [];
            if (!$isSuperAdmin) {
                $relasi = RelasiVerifikasi::where('verifikator', $userId)
                    ->where('modul_verifikasi', 2)
                    ->first();

                if ($relasi) {
                    // Ambil array user_diverifikasi dari relasi_verifikasis
                    $userDivVerifiedIds = $relasi->user_diverifikasi ?? [];
                }
            }

            $limit = $request->input('limit', 10);

            $tukarJadwal = TukarJadwal::query()->orderBy('created_at', 'desc');
            // $tukarJadwal = TukarJadwal::query()->where('acc_user_ditukar', 2)->orderBy('created_at', 'desc');

            // Terapkan filter data berdasarkan role/verifikator
            if (!$isSuperAdmin) {
                if (empty($userDivVerifiedIds)) {
                    // Bukan super admin dan bukan verifikator => kosongkan hasil
                    // Jadi, query dibuat WHERE 0=1 agar kosong
                    $tukarJadwal->whereRaw('0=1');
                } else {
                    // Filter hanya user_id yang termasuk dalam user_diverifikasi
                    $tukarJadwal->whereHas('user_pengajuans', function ($query) use ($userDivVerifiedIds) {
                        $query->whereIn('id', $userDivVerifiedIds);
                    });
                }
            }

            $filters = $request->all();

            // Filter
            if (isset($filters['unit_kerja'])) {
                $namaUnitKerja = $filters['unit_kerja'];
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('id', $namaUnitKerja);
                    } else {
                        $query->where('id', '=', $namaUnitKerja);
                    }
                });
            }

            if (isset($filters['jabatan'])) {
                $namaJabatan = $filters['jabatan'];
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                    if (is_array($namaJabatan)) {
                        $query->whereIn('id', $namaJabatan);
                    } else {
                        $query->where('id', '=', $namaJabatan);
                    }
                });
            }

            if (isset($filters['status_karyawan'])) {
                $statusKaryawan = $filters['status_karyawan'];
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                        foreach ($masaKerja as $masa) {
                            $bulan = $masa * 12;
                            $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                        }
                    });
                } else {
                    $bulan = $masaKerja * 12;
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($bulan, $currentDate) {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    });
                }
            }

            if (isset($filters['status_aktif'])) {
                $statusAktif = $filters['status_aktif'];
                $tukarJadwal->whereHas('user_pengajuans', function ($query) use ($statusAktif) {
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
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($tglMasuk) {
                        $query->whereIn('tgl_masuk', $tglMasuk);
                    });
                } else {
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($tglMasuk) {
                        $query->where('tgl_masuk', $tglMasuk);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $namaAgama = $filters['agama'];
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    });
                } else {
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    });
                }
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    });
                } else {
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['jenis_kompetensi'])) {
                $jenisKaryawan = $filters['jenis_kompetensi'];
                if (is_array($jenisKaryawan)) {
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_kompetensi', $jk);
                            }
                        });
                    });
                } else {
                    $tukarJadwal->whereHas('user_pengajuans.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_kompetensi', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['status_penukaran'])) {
                $namaStatusPenukaran = $filters['status_penukaran'];
                $tukarJadwal->whereHas('status_tukar_jadwals', function ($query) use ($namaStatusPenukaran) {
                    if (is_array($namaStatusPenukaran)) {
                        $query->whereIn('id', $namaStatusPenukaran);
                    } else {
                        $query->where('id', '=', $namaStatusPenukaran);
                    }
                });
            }

            // Search
            if (isset($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $tukarJadwal->where(function ($query) use ($searchTerm) {
                    $query->whereHas('user_pengajuans', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhereHas('user_pengajuans.data_karyawans', function ($query) use ($searchTerm) {
                        $query->where('nik', 'like', $searchTerm);
                    });
                });
            }

            if ($limit == 0) {
                $dataTukarJadwal = $tukarJadwal->get();
                $paginationData = null;
            } else {
                $limit = is_numeric($limit) ? (int)$limit : 10;
                $dataTukarJadwal = $tukarJadwal->paginate($limit);

                $paginationData = [
                    'links' => [
                        'first' => $dataTukarJadwal->url(1),
                        'last' => $dataTukarJadwal->url($dataTukarJadwal->lastPage()),
                        'prev' => $dataTukarJadwal->previousPageUrl(),
                        'next' => $dataTukarJadwal->nextPageUrl(),
                    ],
                    'meta' => [
                        'current_page' => $dataTukarJadwal->currentPage(),
                        'last_page' => $dataTukarJadwal->lastPage(),
                        'per_page' => $dataTukarJadwal->perPage(),
                        'total' => $dataTukarJadwal->total(),
                    ]
                ];
            }

            if ($dataTukarJadwal->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penukaran jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = $dataTukarJadwal->map(function ($tukar_jadwal) use ($baseUrl) {
                $userId_karyawan_pengajuan = $tukar_jadwal->user_pengajuans->id ?? null;
                $userId_karyawan_ditukar = $tukar_jadwal->user_ditukars->id ?? null;

                // Ambil max_order dari modul_verifikasis untuk modul tukar jadwal
                $modulVerifikasi = ModulVerifikasi::where('id', 2)->first();
                $maxOrder = $modulVerifikasi ? $modulVerifikasi->max_order : 0;

                // Ambil relasi verifikasi untuk karyawan pengajuan
                $relasiVerifikasi_karyawan_pengajuan = $userId_karyawan_pengajuan ? RelasiVerifikasi::whereJsonContains('user_diverifikasi', (int) $userId_karyawan_pengajuan)
                    ->where('modul_verifikasi', 2)
                    ->get() : collect();

                // Format data relasi verifikasi untuk karyawan pengajuan dengan loop berdasarkan max_order
                $formattedRelasiVerifikasiPengajuan = [];
                for ($i = 1; $i <= $maxOrder; $i++) {
                    $verifikasiForOrder = $relasiVerifikasi_karyawan_pengajuan->firstWhere('order', $i);
                    $formattedRelasiVerifikasiPengajuan[] = $verifikasiForOrder ? [
                        'id' => $verifikasiForOrder->id,
                        'nama' => $verifikasiForOrder->nama,
                        'verifikator' => [
                            'id' => $verifikasiForOrder->users->id,
                            'nama' => $verifikasiForOrder->users->nama,
                            'username' => $verifikasiForOrder->users->username,
                            'email_verified_at' => $verifikasiForOrder->users->email_verified_at,
                            'data_karyawan_id' => $verifikasiForOrder->users->data_karyawan_id,
                            'foto_profil' => $verifikasiForOrder->users->foto_profiles ? [
                                'id' => $verifikasiForOrder->users->foto_profiles->id,
                                'user_id' => $verifikasiForOrder->users->foto_profiles->user_id,
                                'file_id' => $verifikasiForOrder->users->foto_profiles->file_id,
                                'nama' => $verifikasiForOrder->users->foto_profiles->nama,
                                'nama_file' => $verifikasiForOrder->users->foto_profiles->nama_file,
                                'path' => $baseUrl . $verifikasiForOrder->users->foto_profiles->path,
                                'ext' => $verifikasiForOrder->users->foto_profiles->ext,
                                'size' => $verifikasiForOrder->users->foto_profiles->size,
                            ] : null,
                            'data_completion_step' => $verifikasiForOrder->users->data_completion_step,
                            'status_aktif' => $verifikasiForOrder->users->status_aktif,
                            'created_at' => $verifikasiForOrder->users->created_at,
                            'updated_at' => $verifikasiForOrder->users->updated_at
                        ],
                        'order' => $verifikasiForOrder->order,
                        'user_diverifikasi' => $verifikasiForOrder->user_diverifikasi,
                        'modul_verifikasi' => $verifikasiForOrder->modul_verifikasi,
                        'created_at' => $verifikasiForOrder->created_at,
                        'updated_at' => $verifikasiForOrder->updated_at
                    ] : [
                        'id' => null,
                        'nama' => null,
                        'verifikator' => null,
                        'order' => $i,
                        'user_diverifikasi' => null,
                        'modul_verifikasi' => null,
                        'created_at' => null,
                        'updated_at' => null
                    ];
                }

                // Ambil relasi verifikasi untuk karyawan ditukar
                $relasiVerifikasi_karyawan_ditukar = $userId_karyawan_ditukar ? RelasiVerifikasi::whereJsonContains('user_diverifikasi', (int) $userId_karyawan_ditukar)
                    ->where('modul_verifikasi', 2)
                    ->get() : collect();

                // Format data relasi verifikasi untuk karyawan ditukar dengan loop berdasarkan max_order
                $formattedRelasiVerifikasiDitukar = [];
                for ($i = 1; $i <= $maxOrder; $i++) {
                    $verifikasiForOrder = $relasiVerifikasi_karyawan_ditukar->firstWhere('order', $i);
                    $formattedRelasiVerifikasiDitukar[] = $verifikasiForOrder ? [
                        'id' => $verifikasiForOrder->id,
                        'nama' => $verifikasiForOrder->nama,
                        'verifikator' => [
                            'id' => $verifikasiForOrder->users->id,
                            'nama' => $verifikasiForOrder->users->nama,
                            'username' => $verifikasiForOrder->users->username,
                            'email_verified_at' => $verifikasiForOrder->users->email_verified_at,
                            'data_karyawan_id' => $verifikasiForOrder->users->data_karyawan_id,
                            'foto_profil' => $verifikasiForOrder->users->foto_profiles ? [
                                'id' => $verifikasiForOrder->users->foto_profiles->id,
                                'user_id' => $verifikasiForOrder->users->foto_profiles->user_id,
                                'file_id' => $verifikasiForOrder->users->foto_profiles->file_id,
                                'nama' => $verifikasiForOrder->users->foto_profiles->nama,
                                'nama_file' => $verifikasiForOrder->users->foto_profiles->nama_file,
                                'path' => $baseUrl . $verifikasiForOrder->users->foto_profiles->path,
                                'ext' => $verifikasiForOrder->users->foto_profiles->ext,
                                'size' => $verifikasiForOrder->users->foto_profiles->size,
                            ] : null,
                            'data_completion_step' => $verifikasiForOrder->users->data_completion_step,
                            'status_aktif' => $verifikasiForOrder->users->status_aktif,
                            'created_at' => $verifikasiForOrder->users->created_at,
                            'updated_at' => $verifikasiForOrder->users->updated_at
                        ],
                        'order' => $verifikasiForOrder->order,
                        'user_diverifikasi' => $verifikasiForOrder->user_diverifikasi,
                        'modul_verifikasi' => $verifikasiForOrder->modul_verifikasi,
                        'created_at' => $verifikasiForOrder->created_at,
                        'updated_at' => $verifikasiForOrder->updated_at
                    ] : [
                        'id' => null,
                        'nama' => null,
                        'verifikator' => null,
                        'order' => $i,
                        'user_diverifikasi' => null,
                        'modul_verifikasi' => null,
                        'created_at' => null,
                        'updated_at' => null
                    ];
                }

                return [
                    'id' => $tukar_jadwal->id,
                    'tanggal_pengajuan' => $tukar_jadwal->created_at,
                    'status_penukaran' => $tukar_jadwal->status_tukar_jadwals,
                    'kategori_penukaran' => $tukar_jadwal->kategori_tukar_jadwals,
                    'unit_kerja' => $tukar_jadwal->user_pengajuans->data_karyawans->unit_kerjas,
                    'karyawan_pengajuan' => $tukar_jadwal->user_pengajuans ? [
                        'id' => $tukar_jadwal->user_pengajuans->id,
                        'nama' => $tukar_jadwal->user_pengajuans->nama,
                        'email_verified_at' => $tukar_jadwal->user_pengajuans->email_verified_at,
                        'data_karyawan_id' => $tukar_jadwal->user_pengajuans->data_karyawan_id,
                        'foto_profil' => $tukar_jadwal->user_pengajuans->foto_profiles ? [
                            'id' => $tukar_jadwal->user_pengajuans->foto_profiles->id,
                            'user_id' => $tukar_jadwal->user_pengajuans->foto_profiles->user_id,
                            'file_id' => $tukar_jadwal->user_pengajuans->foto_profiles->file_id,
                            'nama' => $tukar_jadwal->user_pengajuans->foto_profiles->nama,
                            'nama_file' => $tukar_jadwal->user_pengajuans->foto_profiles->nama_file,
                            'path' => $baseUrl . $tukar_jadwal->user_pengajuans->foto_profiles->path,
                            'ext' => $tukar_jadwal->user_pengajuans->foto_profiles->ext,
                            'size' => $tukar_jadwal->user_pengajuans->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $tukar_jadwal->user_pengajuans->data_completion_step,
                        'status_aktif' => $tukar_jadwal->user_pengajuans->status_aktif,
                        'created_at' => $tukar_jadwal->user_pengajuans->created_at,
                        'updated_at' => $tukar_jadwal->user_pengajuans->updated_at
                    ] : null,
                    'relasi_verifikasi_pengajuan' => $formattedRelasiVerifikasiPengajuan,
                    'karyawan_ditukar' => $tukar_jadwal->user_ditukars ? [
                        'id' => $tukar_jadwal->user_ditukars->id,
                        'nama' => $tukar_jadwal->user_ditukars->nama,
                        'email_verified_at' => $tukar_jadwal->user_ditukars->email_verified_at,
                        'data_karyawan_id' => $tukar_jadwal->user_ditukars->data_karyawan_id,
                        'foto_profil' => $tukar_jadwal->user_ditukars->foto_profiles ? [
                            'id' => $tukar_jadwal->user_ditukars->foto_profiles->id,
                            'user_id' => $tukar_jadwal->user_ditukars->foto_profiles->user_id,
                            'file_id' => $tukar_jadwal->user_ditukars->foto_profiles->file_id,
                            'nama' => $tukar_jadwal->user_ditukars->foto_profiles->nama,
                            'nama_file' => $tukar_jadwal->user_ditukars->foto_profiles->nama_file,
                            'path' => $baseUrl . $tukar_jadwal->user_ditukars->foto_profiles->path,
                            'ext' => $tukar_jadwal->user_ditukars->foto_profiles->ext,
                            'size' => $tukar_jadwal->user_ditukars->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $tukar_jadwal->user_ditukars->data_completion_step,
                        'status_aktif' => $tukar_jadwal->user_ditukars->status_aktif,
                        'created_at' => $tukar_jadwal->user_ditukars->created_at,
                        'updated_at' => $tukar_jadwal->user_ditukars->updated_at
                    ] : null,
                    'relasi_verifikasi_ditukar' => $formattedRelasiVerifikasiDitukar,
                    'acc_user_ditukar' => $tukar_jadwal->acc_user_ditukar,
                    'pertukaran_jadwal' => [
                        [
                            'jadwal_karyawan_pengajuan' => $tukar_jadwal->jadwal_pengajuans ? [
                                'id' => $tukar_jadwal->jadwal_pengajuans->id,
                                'tgl_mulai' => $tukar_jadwal->jadwal_pengajuans->tgl_mulai,
                                'tgl_selesai' => $tukar_jadwal->jadwal_pengajuans->tgl_selesai,
                                'shift' => $tukar_jadwal->jadwal_pengajuans->shifts,
                                'created_at' => $tukar_jadwal->jadwal_pengajuans->created_at,
                                'updated_at' => $tukar_jadwal->jadwal_pengajuans->updated_at
                            ] : null,
                            'jadwal_karyawan_ditukar' => $tukar_jadwal->jadwal_ditukars ? [
                                'id' => $tukar_jadwal->jadwal_ditukars->id,
                                'tgl_mulai' => $tukar_jadwal->jadwal_ditukars->tgl_mulai,
                                'tgl_selesai' => $tukar_jadwal->jadwal_ditukars->tgl_selesai,
                                'shift' => $tukar_jadwal->jadwal_ditukars->shifts,
                                'created_at' => $tukar_jadwal->jadwal_ditukars->created_at,
                                'updated_at' => $tukar_jadwal->jadwal_ditukars->updated_at
                            ] : null
                        ]
                    ],
                    'created_at' => $tukar_jadwal->created_at,
                    'updated_at' => $tukar_jadwal->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data tukar jadwal karyawan berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat menampilkan index tukar jadwal: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // super fix super jos
    // public function store(StoreTukarJadwalRequest $request)
    // {
    //     try {
    //         if (!Gate::allows('create tukarJadwal')) {
    //             return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //         }

    //         $verifikatorId = Auth::id();

    //         $data = $request->validated();
    //         $userPengajuan = User::findOrFail($data['user_pengajuan']);
    //         $userDitukar = User::findOrFail($data['user_ditukar']);
    //         $jadwalPengajuan = Jadwal::findOrFail($data['jadwal_pengajuan']);
    //         $jadwalDitukar = Jadwal::findOrFail($data['jadwal_ditukar']);

    //         // Verifikasi unit kerja
    //         if ($userPengajuan->data_karyawans->unit_kerjas->id !== $userDitukar->data_karyawans->unit_kerjas->id) {
    //             return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Karyawan harus berada di unit kerja yang sama untuk menukar jadwal.'), Response::HTTP_BAD_REQUEST);
    //         }

    //         if (Gate::allows('verifikasi2 tukarJadwal')) {
    //             $statusPenukaranId = 4;
    //             $lemburPengajuan = Lembur::where('user_id', $data['user_pengajuan'])->exists();
    //             $lemburDitukar = Lembur::where('user_id', $data['user_ditukar'])->exists();

    //             // Hapus lembur jika ada
    //             if ($lemburPengajuan || $lemburDitukar) {
    //                 Lembur::where('user_id', $data['user_pengajuan'])
    //                     ->orWhere('user_id', $data['user_ditukar'])
    //                     ->delete();
    //             }
    //             $data['verifikator_1'] = $verifikatorId;
    //             $data['verifikator_2'] = $verifikatorId;
    //         } elseif (Gate::allows('verifikasi1 tukarJadwal')) {
    //             $statusPenukaranId = 2;
    //             $data['verifikator_1'] = $verifikatorId;
    //         } else {
    //             $statusPenukaranId = 1;
    //         }

    //         if ($jadwalPengajuan->shift_id != 0 && $jadwalDitukar->shift_id != 0) {
    //             // Konversi tanggal dari string untuk validasi
    //             $tglMulaiPengajuan = RandomHelper::convertToDateString($jadwalPengajuan->tgl_mulai);
    //             $tglMulaiDitukar = RandomHelper::convertToDateString($jadwalDitukar->tgl_mulai);

    //             // Verifikasi tanggal
    //             if ($tglMulaiPengajuan !== $tglMulaiDitukar) {
    //                 return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Jadwal harus pada tanggal yang sama untuk menukar jadwal.'), Response::HTTP_BAD_REQUEST);
    //             }

    //             // Tukar shift dengan shift dengan Tukar user_id
    //             $tempUserId = $jadwalPengajuan->user_id;
    //             $jadwalPengajuan->user_id = $jadwalDitukar->user_id;
    //             $jadwalDitukar->user_id = $tempUserId;

    //             $jadwalPengajuan->save();
    //             $jadwalDitukar->save();

    //             // Simpan permintaan tukar jadwal
    //             $tukarJadwal = new TukarJadwal([
    //                 'user_pengajuan' => $userPengajuan->id,
    //                 'jadwal_pengajuan' => $jadwalPengajuan->id,
    //                 'user_ditukar' => $userDitukar->id,
    //                 'jadwal_ditukar' => $jadwalDitukar->id,
    //                 'status_penukaran_id' => $statusPenukaranId, // Disetujui
    //                 'kategori_penukaran_id' => 1, // Tukar Shift
    //             ]);
    //             $tukarJadwal->save();

    //             // Buat dan simpan notifikasi
    //             $this->createNotifikasiTukarJadwal($userPengajuan, $userDitukar, $jadwalPengajuan, $jadwalDitukar);
    //         } else if ($jadwalPengajuan->shift_id == 0 && $jadwalDitukar->shift_id == 0) {
    //             // Konversi tanggal dari string untuk validasi
    //             $tglMulaiPengajuan = RandomHelper::convertToDateString($jadwalPengajuan->tgl_mulai);
    //             $tglMulaiDitukar = RandomHelper::convertToDateString($jadwalDitukar->tgl_mulai);

    //             // Tukar user_id pada jadwal libur
    //             $jadwalKerjaPengajuan = Jadwal::where('user_id', $userPengajuan->id)
    //                 ->where('tgl_mulai', $tglMulaiDitukar)
    //                 ->whereNotNull('shift_id')
    //                 ->first();

    //             $jadwalKerjaDitukar = Jadwal::where('user_id', $userDitukar->id)
    //                 ->where('tgl_mulai', $tglMulaiPengajuan)
    //                 ->whereNotNull('shift_id')
    //                 ->first();
    //             // dd(
    //             //     "user pengajuan {$userPengajuan->id}, jadwal kerja yang ada {$jadwalKerjaPengajuan}",
    //             //     "user ditukar {$userDitukar->id}, jadwal kerja yang ada {$jadwalKerjaDitukar}"
    //             // );

    //             $tempUserId = $jadwalPengajuan->user_id;
    //             $jadwalPengajuan->user_id = $jadwalDitukar->user_id;
    //             $jadwalDitukar->user_id = $tempUserId;

    //             // Simpan perubahan jadwal libur
    //             // $jadwalPengajuan->save();
    //             // $jadwalDitukar->save();

    //             if ($jadwalKerjaPengajuan && $jadwalKerjaDitukar) {
    //                 // Tukar shift mereka
    //                 $tempShiftId = $jadwalKerjaPengajuan->shift_id;
    //                 $jadwalKerjaPengajuan->shift_id = $jadwalKerjaDitukar->shift_id;
    //                 $jadwalKerjaDitukar->shift_id = $tempShiftId;

    //                 // Tukar user_id pada jadwal kerja
    //                 $tempUserId = $jadwalKerjaPengajuan->user_id;
    //                 $jadwalKerjaPengajuan->user_id = $jadwalKerjaDitukar->user_id;
    //                 $jadwalKerjaDitukar->user_id = $tempUserId;

    //                 // Simpan perubahan jadwal kerja
    //                 // $jadwalKerjaPengajuan->save();
    //                 // $jadwalKerjaDitukar->save();
    //             }

    //             // Simpan permintaan tukar jadwal
    //             $tukarJadwal = new TukarJadwal([
    //                 'user_pengajuan' => $userPengajuan->id,
    //                 'jadwal_pengajuan' => $jadwalPengajuan->id,
    //                 'user_ditukar' => $userDitukar->id,
    //                 'jadwal_ditukar' => $jadwalDitukar->id,
    //                 'status_penukaran_id' => $statusPenukaranId, // Disetujui
    //                 'kategori_penukaran_id' => 2, // Tukar Libur
    //             ]);
    //             $tukarJadwal->save();

    //             // Buat dan simpan notifikasi
    //             $this->createNotifikasiTukarJadwal($userPengajuan, $userDitukar, $jadwalPengajuan, $jadwalDitukar);
    //         } else {
    //             return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak bisa menukar shift dengan libur atau sebaliknya.'), Response::HTTP_BAD_REQUEST);
    //         }

    //         return response()->json([
    //             'status' => Response::HTTP_OK,
    //             'message' => 'Data tukar jadwal karyawan berhasil ditambahkan.',
    //             'data' => [
    //                 [
    //                     'user_pengajuan' => [
    //                         'id' => $tukarJadwal->id,
    //                         'user' => [
    //                             'id' => $userPengajuan->id,
    //                             'nama' => $userPengajuan->nama,
    //                             'username' => $userPengajuan->username,
    //                             'email_verified_at' => $userPengajuan->email_verified_at,
    //                             'data_karyawan_id' => $userPengajuan->data_karyawan_id,
    //                             'foto_profil' => $userPengajuan->foto_profil,
    //                             'data_completion_step' => $userPengajuan->data_completion_step,
    //                             'status_aktif' => $userPengajuan->status_aktif,
    //                             'created_at' => $userPengajuan->created_at,
    //                             'updated_at' => $userPengajuan->updated_at
    //                         ],
    //                         'jadwal' => $jadwalPengajuan,
    //                         'status' => $tukarJadwal->status_tukar_jadwals,
    //                         'kategori' => $tukarJadwal->kategori_tukar_jadwals,
    //                     ]
    //                 ],
    //                 [
    //                     'user_ditukar' => [
    //                         'id' => $tukarJadwal->id,
    //                         'user' => [
    //                             'id' => $userDitukar->id,
    //                             'nama' => $userDitukar->nama,
    //                             'username' => $userDitukar->username,
    //                             'email_verified_at' => $userDitukar->email_verified_at,
    //                             'data_karyawan_id' => $userDitukar->data_karyawan_id,
    //                             'foto_profil' => $userDitukar->foto_profil,
    //                             'data_completion_step' => $userDitukar->data_completion_step,
    //                             'status_aktif' => $userDitukar->status_aktif,
    //                             'created_at' => $userDitukar->created_at,
    //                             'updated_at' => $userDitukar->updated_at
    //                         ],
    //                         'jadwal' => $jadwalDitukar,
    //                         'status' => $tukarJadwal->status_tukar_jadwals,
    //                         'kategori' => $tukarJadwal->kategori_tukar_jadwals,
    //                     ]
    //                 ]
    //             ]
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         Log::error('| Tukar Jadwal | - Error saat menyimpan data tukar jadwal: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
    //             'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    public function show($id)
    {
        try {
            if (!Gate::allows('view tukarJadwal')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $tukarJadwal = TukarJadwal::find($id);
            if (!$tukarJadwal) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tukar jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $userPengajuan = $tukarJadwal->user_pengajuans;
            $jadwalPengajuan = $tukarJadwal->jadwal_pengajuans;
            $userDitukar = $tukarJadwal->user_ditukars;
            $jadwalDitukar = $tukarJadwal->jadwal_ditukars;
            $verifikator_1 = $tukarJadwal->verifikator_1_users;
            $verifikator_2 = $tukarJadwal->verifikator_2_admins;

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = [
                'id' => $tukarJadwal->id,
                'tanggal_pengajuan' => $tukarJadwal->created_at,
                'status_penukaran' => $tukarJadwal->status_tukar_jadwals,
                'kategori_penukaran' => $tukarJadwal->kategori_tukar_jadwals,
                'unit_kerja' => $userPengajuan->data_karyawans->unit_kerjas,
                'karyawan_pengajuan' => [
                    'id' => $userPengajuan->id,
                    'nama' => $userPengajuan->nama,
                    'email_verified_at' => $userPengajuan->email_verified_at,
                    'data_karyawan_id' => $userPengajuan->data_karyawan_id,
                    'foto_profil' => $userPengajuan->foto_profiles ? [
                        'id' => $userPengajuan->foto_profiles->id,
                        'user_id' => $userPengajuan->foto_profiles->user_id,
                        'file_id' => $userPengajuan->foto_profiles->file_id,
                        'nama' => $userPengajuan->foto_profiles->nama,
                        'nama_file' => $userPengajuan->foto_profiles->nama_file,
                        'path' => $baseUrl . $userPengajuan->foto_profiles->path,
                        'ext' => $userPengajuan->foto_profiles->ext,
                        'size' => $userPengajuan->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $userPengajuan->data_completion_step,
                    'status_aktif' => $userPengajuan->status_aktif,
                    'created_at' => $userPengajuan->created_at,
                    'updated_at' => $userPengajuan->updated_at
                ],
                'karyawan_ditukar' => [
                    'id' => $userDitukar->id,
                    'nama' => $userDitukar->nama,
                    'email_verified_at' => $userDitukar->email_verified_at,
                    'data_karyawan_id' => $userDitukar->data_karyawan_id,
                    'foto_profil' => $userDitukar->foto_profiles ? [
                        'id' => $userDitukar->foto_profiles->id,
                        'user_id' => $userDitukar->foto_profiles->user_id,
                        'file_id' => $userDitukar->foto_profiles->file_id,
                        'nama' => $userDitukar->foto_profiles->nama,
                        'nama_file' => $userDitukar->foto_profiles->nama_file,
                        'path' => $baseUrl . $userDitukar->foto_profiles->path,
                        'ext' => $userDitukar->foto_profiles->ext,
                        'size' => $userDitukar->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $userDitukar->data_completion_step,
                    'status_aktif' => $userDitukar->status_aktif,
                    'created_at' => $userDitukar->created_at,
                    'updated_at' => $userDitukar->updated_at
                ],
                'pertukaran_jadwal' => [
                    [
                        'jadwal_karyawan_pengajuan' => [
                            'id' => $jadwalPengajuan->id,
                            'tgl_mulai' => $jadwalPengajuan->tgl_mulai,
                            'tgl_selesai' => $jadwalPengajuan->tgl_selesai,
                            'shift' => $jadwalPengajuan->shifts,
                            'created_at' => $jadwalPengajuan->created_at,
                            'updated_at' => $jadwalPengajuan->updated_at
                        ],
                        'jadwal_karyawan_ditukar' => [
                            'id' => $jadwalDitukar->id,
                            'tgl_mulai' => $jadwalDitukar->tgl_mulai,
                            'tgl_selesai' => $jadwalDitukar->tgl_selesai,
                            'shift' => $jadwalDitukar->shifts,
                            'created_at' => $jadwalDitukar->created_at,
                            'updated_at' => $jadwalDitukar->updated_at
                        ]
                    ]
                ],
                'verifikator_user' => $verifikator_1 ? [
                    'id' => $verifikator_1->id,
                    'nama' => $verifikator_1->nama,
                    'email_verified_at' => $verifikator_1->email_verified_at,
                    'data_karyawan_id' => $verifikator_1->data_karyawan_id,
                    'foto_profil' => $verifikator_1->foto_profiles ? [
                        'id' => $verifikator_1->foto_profiles->id,
                        'user_id' => $verifikator_1->foto_profiles->user_id,
                        'file_id' => $verifikator_1->foto_profiles->file_id,
                        'nama' => $verifikator_1->foto_profiles->nama,
                        'nama_file' => $verifikator_1->foto_profiles->nama_file,
                        'path' => $baseUrl . $verifikator_1->foto_profiles->path,
                        'ext' => $verifikator_1->foto_profiles->ext,
                        'size' => $verifikator_1->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $verifikator_1->data_completion_step,
                    'status_aktif' => $verifikator_1->status_aktif,
                    'created_at' => $verifikator_1->created_at,
                    'updated_at' => $verifikator_1->updated_at
                ] : null,
                'verifikator_user' => $verifikator_2 ? [
                    'id' => $verifikator_2->id,
                    'nama' => $verifikator_2->nama,
                    'email_verified_at' => $verifikator_2->email_verified_at,
                    'data_karyawan_id' => $verifikator_2->data_karyawan_id,
                    'foto_profil' => $verifikator_2->foto_profiles ? [
                        'id' => $verifikator_2->foto_profiles->id,
                        'user_id' => $verifikator_2->foto_profiles->user_id,
                        'file_id' => $verifikator_2->foto_profiles->file_id,
                        'nama' => $verifikator_2->foto_profiles->nama,
                        'nama_file' => $verifikator_2->foto_profiles->nama_file,
                        'path' => $baseUrl . $verifikator_2->foto_profiles->path,
                        'ext' => $verifikator_2->foto_profiles->ext,
                        'size' => $verifikator_2->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $verifikator_2->data_completion_step,
                    'status_aktif' => $verifikator_2->status_aktif,
                    'created_at' => $verifikator_2->created_at,
                    'updated_at' => $verifikator_2->updated_at
                ] : null,
                'alasan' => $tukarJadwal->alasan ? $tukarJadwal->alasan : null,
                'created_at' => $tukarJadwal->created_at,
                'updated_at' => $tukarJadwal->updated_at
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail tukar jadwal karyawan '{$tukarJadwal->user_pengajuans->nama}' dan '{$tukarJadwal->user_ditukars->nama}' berhasil ditampilkan.",
                'data' => $formattedData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat menampilkan detail data tukar jadwal: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportJadwalTukar()
    {
        try {
            if (!Gate::allows('export tukarJadwal')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataTukarJadwal = TukarJadwal::all();
            if ($dataTukarJadwal->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data tukar jadwal karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new TukarJadwalExport(), 'pertukaran-jadwal.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat export data tukar jadwal: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifikasiTahap1(Request $request, $tukarJadwalId)
    {
        try {
            // 1. Dapatkan ID user yang login
            $verifikatorId = Auth::id();

            // 2. Dapatkan tukar jadwal berdasarkan ID
            $tukar_jadwal = TukarJadwal::find($tukarJadwalId);
            if (!$tukar_jadwal) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tukar jadwal tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            if ($tukar_jadwal->acc_user_ditukar != 2) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tukar jadwal tersebut belum diverifikasi oleh karyawan ditukar atau karyawan pengajuan.'), Response::HTTP_BAD_REQUEST);
            }

            // 3. Jika pengguna bukan Super Admin, lakukan pengecekan relasi verifikasi
            if (!Auth::user()->hasRole('Super Admin')) {
                // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
                $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                    ->where('modul_verifikasi', 2) // 2 adalah modul tukar jadwal
                    ->where('order', 1)
                    ->first();

                if (!$relasiVerifikasi) {
                    return response()->json([
                        'status' => Response::HTTP_NOT_FOUND,
                        'message' => "Anda tidak memiliki hak akses untuk verifikasi tukar jadwal tahap 1 dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                        'relasi_verifikasi' => null,
                    ], Response::HTTP_NOT_FOUND);
                }

                // 4. Dapatkan user pengaju tukar jadwal
                $pengajuTukarJadwalUserId = $tukar_jadwal->user_pengajuan;

                // 5. Samakan user_id pengajuan tukar jadwal dengan string array user_diverifikasi di tabel relasi_verifikasis
                $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
                if (!is_array($userDiverifikasi)) {
                    Log::warning('Kesalahan format data user diverifikasi pada verif 1 tukar jadwal');
                    return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if (!in_array($pengajuTukarJadwalUserId, $userDiverifikasi)) {
                    return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi tukar jadwal ini karena karyawan tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
                }

                // 6. Validasi nilai kolom order dan status_penukaran_id
                $status_penukaran_id = $tukar_jadwal->status_penukaran_id;
                if ($relasiVerifikasi->order != 1) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Pertukaran jadwal ini tidak dalam status untuk disetujui pada tahap 1.'), Response::HTTP_BAD_REQUEST);
                }
            }

            // 7. Logika untuk menyetujui atau menolak verifikasi tahap 1
            $status_penukaran_id = $tukar_jadwal->status_penukaran_id;

            if ($request->has('verifikasi_pertama_disetujui') && $request->verifikasi_pertama_disetujui == 1) {
                if ($status_penukaran_id == 1) {
                    $tukar_jadwal->status_penukaran_id = 2;
                    $tukar_jadwal->verifikator_1 = Auth::id();
                    $tukar_jadwal->alasan = null;
                    $tukar_jadwal->save();

                    $this->createNotifikasiVerifikasiTahap1($tukar_jadwal, true);

                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 pertukaran jadwal dari '{$tukar_jadwal->user_pengajuans->nama}' telah disetujui."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pertukaran jadwal dari '{$tukar_jadwal->user_pengajuans->nama}' tidak dalam status untuk disetujui tahap 1."), Response::HTTP_BAD_REQUEST);
                }
            } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
                if ($status_penukaran_id == 1) {
                    $tukar_jadwal->status_penukaran_id = 3;
                    $tukar_jadwal->verifikator_2 = Auth::id();
                    $tukar_jadwal->alasan = $request->input('alasan');
                    $tukar_jadwal->save();

                    $this->createNotifikasiVerifikasiTahap1($tukar_jadwal, false);

                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 pertukaran jadwal dari '{$tukar_jadwal->user_pengajuans->nama}' telah ditolak."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pertukaran jadwal dari '{$tukar_jadwal->user_pengajuans->nama}' tidak dalam status untuk ditolak tahap 1."), Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat melakukan verifikasi tahap 1: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifikasiTahap2(Request $request, $tukarJadwalId)
    {
        try {
            // 1. Dapatkan ID user yang login
            $verifikatorId = Auth::id();

            // 2. Dapatkan tukar jadwal berdasarkan ID
            $tukar_jadwal = TukarJadwal::find($tukarJadwalId);
            if (!$tukar_jadwal) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tukar jadwal tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            if ($tukar_jadwal->acc_user_ditukar != 2) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tukar jadwal tersebut belum diverifikasi oleh karyawan ditukar atau karyawan pengajuan.'), Response::HTTP_BAD_REQUEST);
            }

            // 3. Jika pengguna bukan Super Admin, lakukan pengecekan relasi verifikasi
            if (!Auth::user()->hasRole('Super Admin')) {
                // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
                $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                    ->where('modul_verifikasi', 2) // 2 adalah modul tukar jadwal
                    ->where('order', 2)
                    ->first();

                if (!$relasiVerifikasi) {
                    return response()->json([
                        'status' => Response::HTTP_NOT_FOUND,
                        'message' => "Anda tidak memiliki hak akses untuk verifikasi tukar jadwal tahap 2 dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                        'relasi_verifikasi' => null,
                    ], Response::HTTP_NOT_FOUND);
                }

                // 4. Dapatkan user pengaju tukar jadwal
                $pengajuTukarJadwalUserId = $tukar_jadwal->user_pengajuan;

                // 5. Samakan user_id pengajuan tukar jadwal dengan string array user_diverifikasi di tabel relasi_verifikasis
                $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
                if (!is_array($userDiverifikasi)) {
                    Log::warning('Kesalahan format data user diverifikasi pada verif 2 tukar jadwal');
                    return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if (!in_array($pengajuTukarJadwalUserId, $userDiverifikasi)) {
                    return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi tukar jadwal ini karena karyawan tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
                }

                // 6. Validasi nilai kolom order dan status_penukaran_id
                $status_penukaran_id = $tukar_jadwal->status_penukaran_id;
                if ($relasiVerifikasi->order != 2) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Pertukaran jadwal ini tidak dalam status untuk disetujui pada tahap 2.'), Response::HTTP_BAD_REQUEST);
                }
            }

            // 7. Logika untuk menyetujui atau menolak verifikasi tahap 2
            $status_penukaran_id = $tukar_jadwal->status_penukaran_id;

            if ($request->has('verifikasi_kedua_disetujui') && $request->verifikasi_kedua_disetujui == 1) {
                if ($status_penukaran_id == 2) {
                    $tukar_jadwal->status_penukaran_id = 4;
                    $tukar_jadwal->verifikator_2 = Auth::id();
                    $tukar_jadwal->alasan = null;
                    $tukar_jadwal->save();

                    // Hapus lembur jika ada
                    $lemburPengajuan = Lembur::where('user_id', $tukar_jadwal->user_pengajuan)->exists();
                    $lemburDitukar = Lembur::where('user_id', $tukar_jadwal->user_ditukar)->exists();
                    if ($lemburPengajuan || $lemburDitukar) {
                        Lembur::where('user_id', $tukar_jadwal->user_pengajuan)
                            ->orWhere('user_id', $tukar_jadwal->user_ditukar)
                            ->delete();
                    }

                    // Tukar jadwal
                    $jadwalPengajuan = Jadwal::findOrFail($tukar_jadwal->jadwal_pengajuan);
                    $jadwalDitukar = Jadwal::findOrFail($tukar_jadwal->jadwal_ditukar);

                    // Load actual User objects
                    $userPengajuan = User::findOrFail($tukar_jadwal->user_pengajuan);
                    $userDitukar = User::findOrFail($tukar_jadwal->user_ditukar);

                    // Hari libur di jadwal user ditukar (jika ada)
                    $jadwalLiburDitukar = Jadwal::where('tgl_mulai', $jadwalDitukar->tgl_mulai)
                        ->where('user_id', $userPengajuan->id)
                        ->where('shift_id', 0)
                        ->first();
                    $jadwalLiburPengajuan = Jadwal::where('tgl_mulai', $jadwalPengajuan->tgl_mulai)
                        ->where('user_id', $userDitukar->id)
                        ->where('shift_id', 0)
                        ->first();

                    $this->CreateTukarJadwal($userPengajuan, $userDitukar, $jadwalPengajuan, $jadwalDitukar, $jadwalLiburPengajuan, $jadwalLiburDitukar);

                    $this->createNotifikasiVerifikasiTahap2($tukar_jadwal, true);

                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 pertukaran jadwal dari '{$tukar_jadwal->user_pengajuans->nama}' telah disetujui."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pertukaran jadwal dari '{$tukar_jadwal->user_pengajuans->nama}' tidak dalam status untuk disetujui tahap 2."), Response::HTTP_BAD_REQUEST);
                }
            } elseif ($request->has('verifikasi_kedua_ditolak') && $request->verifikasi_kedua_ditolak == 1) {
                if ($status_penukaran_id == 2) {
                    $tukar_jadwal->status_penukaran_id = 5;
                    $tukar_jadwal->verifikator_2 = Auth::id();
                    $tukar_jadwal->alasan = $request->input('alasan');
                    $tukar_jadwal->save();

                    $this->createNotifikasiVerifikasiTahap2($tukar_jadwal, false);

                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 pertukaran jadwal dari '{$tukar_jadwal->user_pengajuans->nama}' telah ditolak."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pertukaran jadwal dari '{$tukar_jadwal->user_pengajuans->nama}' tidak dalam status untuk ditolak tahap 2."), Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat melakukan verifikasi tahap 2: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function generateDateRange($start_date, $end_date)
    {
        try {
            $dates = [];
            $current = Carbon::parse($start_date);  // Pastikan ini sudah dalam format Y-m-d
            $end = Carbon::parse($end_date);

            while ($current->lte($end)) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }

            return $dates;
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error saat membuat date range: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function formatSchedules($jadwal, $date_range)
    {
        try {
            $user_schedules_by_date = [];
            // Iterasi melalui jadwal dan rentang tanggal, menyimpan semua jadwal yang sesuai
            foreach ($jadwal as $schedule) {
                $tgl_mulai_formatted = Carbon::parse(RandomHelper::convertToDateString($schedule->tgl_mulai));
                $tgl_selesai_formatted = Carbon::parse(RandomHelper::convertToDateString($schedule->tgl_selesai));

                $current_date = $tgl_mulai_formatted->copy();

                // Tentukan apakah ini adalah shift yang berakhir keesokan harinya
                $is_overnight_shift = $tgl_selesai_formatted->greaterThan($tgl_mulai_formatted);

                // Jika ini adalah shift yang berlangsung hingga keesokan hari, hanya tampilkan sekali pada hari `tgl_mulai`
                if ($is_overnight_shift) {
                    $date_key = $tgl_mulai_formatted->format('Y-m-d');
                    if (!isset($user_schedules_by_date[$date_key])) {
                        $user_schedules_by_date[$date_key] = [];
                    }
                    $user_schedules_by_date[$date_key][] = $schedule;
                } else {
                    while ($current_date->lte($tgl_selesai_formatted)) {
                        $date_key = $current_date->format('Y-m-d');
                        if (!isset($user_schedules_by_date[$date_key])) {
                            $user_schedules_by_date[$date_key] = [];
                        }
                        $user_schedules_by_date[$date_key][] = $schedule;
                        $current_date->addDay();
                    }
                }
            }

            $user_schedule_array = [];
            foreach ($date_range as $date) {
                if (isset($user_schedules_by_date[$date])) {
                    foreach ($user_schedules_by_date[$date] as $schedule) {
                        $shift = $schedule->shifts;
                        $user_schedule_array[] = [
                            'id' => $schedule->id,
                            'tanggal' => $date,
                            'nama_shift' => $shift ? $shift->nama : 'Libur',
                            'jam_from' => $shift ? $shift->jam_from : 'N/A',
                            'jam_to' => $shift ? $shift->jam_to : 'N/A',
                        ];
                    }
                }
            }

            return $user_schedule_array;
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error pada function formatSchedules: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // private function createNotifikasiTukarJadwal($userPengajuan, $userDitukar, $jadwalPengajuan, $jadwalDitukar)
    // {
    //     try {
    //         // Konversi tanggal pengajuan
    //         $konversiNotif_tgl_mulai = Carbon::parse($jadwalPengajuan->tgl_mulai)->locale('id')->isoFormat('D MMMM YYYY');
    //         $message = "'{$userPengajuan->nama}', Jadwal anda telah ditukar dengan karyawan '{$userDitukar->nama}' pada tanggal {$konversiNotif_tgl_mulai}.";
    //         $messageSuperAdmin = "Notifikasi untuk Super Admin: Jadwal '{$userPengajuan->nama}' berhasil ditukar dengan karyawan '{$userDitukar->nama}' pada tanggal {$konversiNotif_tgl_mulai}.";
    //         $timezone = Carbon::now('Asia/Jakarta');

    //         // Kirim notifikasi ke user pengajuan dan Super Admin
    //         $userIdsPengajuan = [$userPengajuan->id, 1];
    //         foreach ($userIdsPengajuan as $userIdPengajuan) {
    //             $messageToSend = $userIdPengajuan === 1 ? $messageSuperAdmin : $message;
    //             Notifikasi::create([
    //                 'kategori_notifikasi_id' => 2,
    //                 'user_id' => $userIdPengajuan,
    //                 'message' => $messageToSend,
    //                 'is_read' => false,
    //                 'is_verifikasi' => true,
    //                 'created_at' => $timezone,
    //             ]);
    //         }

    //         // Konversi tanggal jadwal ditukar
    //         $konversiNotif_tgl_mulai_ajuan = Carbon::parse($jadwalDitukar->tgl_mulai)->locale('id')->isoFormat('D MMMM YYYY');
    //         $messageDitukar = "'{$userDitukar->nama}', Jadwal anda telah ditukar dengan karyawan '{$userPengajuan->nama}' pada tanggal {$konversiNotif_tgl_mulai_ajuan}.";
    //         $messageSuperAdminDitukar = "Notifikasi untuk Super Admin: Jadwal '{$userDitukar->nama}' berhasil ditukar dengan karyawan '{$userPengajuan->nama}' pada tanggal {$konversiNotif_tgl_mulai_ajuan}.";

    //         // Kirim notifikasi ke user yang ditukar dan Super Admin
    //         $userIdsDitukar = [$userDitukar->id, 1];
    //         foreach ($userIdsDitukar as $userIdDitukar) {
    //             $messageToSend = $userIdDitukar === 1 ? $messageSuperAdminDitukar : $messageDitukar;
    //             Notifikasi::create([
    //                 'kategori_notifikasi_id' => 2,
    //                 'user_id' => $userIdDitukar,
    //                 'message' => $messageToSend,
    //                 'is_read' => false,
    //                 'is_verifikasi' => true,
    //                 'created_at' => $timezone,
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('| Tukar Jadwal | - Error saat menampilkan detail data tukar jadwal: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
    //             'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    private function createNotifikasiVerifikasiTahap1($tukarJadwal, $isApproved)
    {
        try {
            $userPengajuan = $tukarJadwal->user_pengajuans;
            $userDitukar = $tukarJadwal->user_ditukars;
            $timezone = Carbon::now('Asia/Jakarta');

            // Pesan untuk verifikasi tahap 1 (disetujui atau ditolak)
            if ($isApproved) {
                $messagePengajuan = "Verifikasi tahap 1 untuk pengajuan tukar jadwal '{$userDitukar->nama}' telah disetujui.";
                $messageDitukar = "Verifikasi tahap 1 untuk pengajuan tukar jadwal dari '{$userPengajuan->nama}' telah disetujui.";
            } else {
                $messagePengajuan = "Verifikasi tahap 1 untuk pengajuan tukar jadwal '{$userDitukar->nama}' telah ditolak.";
                $messageDitukar = "Verifikasi tahap 1 untuk pengajuan tukar jadwal dari '{$userPengajuan->nama}' telah ditolak.";
                if ($tukarJadwal->alasan) {
                    $messagePengajuan .= " Alasan penolakan: {$tukarJadwal->alasan}.";
                    $messageDitukar .= " Alasan penolakan: {$tukarJadwal->alasan}.";
                }
            }

            // Pesan khusus untuk Super Admin
            $messageSuperAdminPengajuan = "Notifikasi untuk Super Admin: " . $messagePengajuan;
            $messageSuperAdminDitukar = "Notifikasi untuk Super Admin: " . $messageDitukar;

            // Kirim notifikasi ke user pengajuan dan Super Admin (user_id = 1)
            $userIdsPengajuan = [$userPengajuan->id, 1];
            foreach ($userIdsPengajuan as $userIdPengajuan) {
                $message = $userIdPengajuan === 1 ? $messageSuperAdminPengajuan : $messagePengajuan;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 2,
                    'user_id' => $userIdPengajuan,
                    'message' => $message,
                    'is_read' => false,
                    'is_verifikasi' => true,
                    'created_at' => $timezone,
                ]);
            }

            // Kirim notifikasi ke user yang ditukar dan Super Admin (user_id = 1)
            $userIdsDitukar = [$userDitukar->id, 1];
            foreach ($userIdsDitukar as $userIdDitukar) {
                $message = $userIdDitukar === 1 ? $messageSuperAdminDitukar : $messageDitukar;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 2,
                    'user_id' => $userIdDitukar,
                    'message' => $message,
                    'is_read' => false,
                    'is_verifikasi' => true,
                    'created_at' => $timezone,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error function createNotifikasiVerifikasiTahap1: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiVerifikasiTahap2($tukarJadwal, $isApproved)
    {
        try {
            $userPengajuan = $tukarJadwal->user_pengajuans;
            $userDitukar = $tukarJadwal->user_ditukars;
            $timezone = Carbon::now('Asia/Jakarta');

            // Pesan untuk verifikasi tahap 2 (disetujui atau ditolak)
            if ($isApproved) {
                $messagePengajuan = "Verifikasi tahap 2 untuk pengajuan tukar jadwal '{$userDitukar->nama}' telah disetujui.";
                $messageDitukar = "Verifikasi tahap 2 untuk pengajuan tukar jadwal dari '{$userPengajuan->nama}' telah disetujui.";
            } else {
                $messagePengajuan = "Verifikasi tahap 2 untuk pengajuan tukar jadwal '{$userDitukar->nama}' telah ditolak.";
                $messageDitukar = "Verifikasi tahap 2 untuk pengajuan tukar jadwal dari '{$userPengajuan->nama}' telah ditolak.";
                if ($tukarJadwal->alasan) {
                    $messagePengajuan .= " Alasan penolakan: {$tukarJadwal->alasan}.";
                    $messageDitukar .= " Alasan penolakan: {$tukarJadwal->alasan}.";
                }
            }

            // Pesan khusus untuk Super Admin
            $messageSuperAdminPengajuan = "Notifikasi untuk Super Admin: " . $messagePengajuan;
            $messageSuperAdminDitukar = "Notifikasi untuk Super Admin: " . $messageDitukar;

            // Kirim notifikasi ke user pengajuan dan Super Admin (user_id = 1)
            $userIdsPengajuan = [$userPengajuan->id, 1];
            foreach ($userIdsPengajuan as $userIdPengajuan) {
                $message = $userIdPengajuan === 1 ? $messageSuperAdminPengajuan : $messagePengajuan;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 2,
                    'user_id' => $userIdPengajuan,
                    'message' => $message,
                    'is_read' => false,
                    'created_at' => $timezone,
                ]);
            }

            // Kirim notifikasi ke user yang ditukar dan Super Admin (user_id = 1)
            $userIdsDitukar = [$userDitukar->id, 1];
            foreach ($userIdsDitukar as $userIdDitukar) {
                $message = $userIdDitukar === 1 ? $messageSuperAdminDitukar : $messageDitukar;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 2,
                    'user_id' => $userIdDitukar,
                    'message' => $message,
                    'is_read' => false,
                    'created_at' => $timezone,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('| Tukar Jadwal | - Error function createNotifikasiVerifikasiTahap2: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function CreateTukarJadwal($userPengajuan, $userDitukar, $jadwalPengajuan, $jadwalDitukar, $jadwalLiburPengajuan, $jadwalLiburDitukar)
    {
        Log::info("[CreateTukarJadwal] Mulai proses tukar jadwal user {$userPengajuan->id} dan user {$userDitukar->id}");

        // Jika shift_id bukan libur (tidak sama dengan 0)
        if ($jadwalPengajuan->shift_id != 0 && $jadwalDitukar->shift_id != 0) {
            Log::info("[CreateTukarJadwal] Keduanya bukan libur. Shift pengajuan: {$jadwalPengajuan->shift_id}, Shift ditukar: {$jadwalDitukar->shift_id}");

            // Jika salah satu shift adalah shift malam (shift_id == 3)
            if ($jadwalPengajuan->shift_id == 3 || $jadwalDitukar->shift_id == 3) {
                Log::info("[CreateTukarJadwal] Ada shift malam (shift_id=3), handle khusus...");

                $tanggalMulaiPengajuan = Carbon::createFromFormat('Y-m-d', $jadwalPengajuan->tgl_mulai);
                $tanggalMulaiDitukar = Carbon::createFromFormat('Y-m-d', $jadwalDitukar->tgl_mulai);

                // Handle pengajuan shift malam
                if ($jadwalPengajuan->shift_id == 3) {
                    $nextDay = $tanggalMulaiPengajuan->copy()->addDay();
                    $nextDayShift = Jadwal::where('user_id', $userDitukar->id)
                        ->whereDate('tgl_mulai', $nextDay)
                        ->first();
                    if ($nextDayShift) {
                        Log::info("[CreateTukarJadwal] Hapus jadwal hari berikutnya dari user {$userDitukar->id} tanggal {$nextDay->toDateString()}");

                        // Hapus jadwal hari berikutnya jika ada
                        $nextDayShift->delete();
                    }
                }

                // Handle tukar shift malam
                if ($jadwalDitukar->shift_id == 3) {
                    $nextDay = $tanggalMulaiDitukar->copy()->addDay();
                    $nextDayShift = Jadwal::where('user_id', $userPengajuan->id)
                        ->whereDate('tgl_mulai', $nextDay)
                        ->first();
                    if ($nextDayShift) {
                        Log::info("[CreateTukarJadwal] Hapus jadwal hari berikutnya dari user {$userPengajuan->id} tanggal {$nextDay->toDateString()}");

                        // Hapus jadwal hari berikutnya jika ada
                        $nextDayShift->delete();
                    }
                }
            }

            // Tukar user_id
            Log::info("[CreateTukarJadwal] Tukar user_id pada jadwal.");

            $tempUserId = $jadwalPengajuan->user_id;
            $jadwalPengajuan->user_id = $jadwalDitukar->user_id;
            $jadwalDitukar->user_id = $tempUserId;

            if ($jadwalLiburPengajuan == null && $jadwalLiburDitukar == null) {
                Log::info("[CreateTukarJadwal] Tidak ada jadwal libur yang harus ditukar.");

                // Update bug disini - 15-05-2025
                $jadwalPengajuan->save();
                $jadwalDitukar->save();
                // Update bug disini - 15-05-2025

                return;
            } elseif ($jadwalLiburPengajuan == null) {
                Log::info("[CreateTukarJadwal] Jadwal libur pengajuan null, update jadwal libur ditukar.");

                $jadwalLiburDitukar->tgl_mulai = $jadwalPengajuan->tgl_mulai;
                $jadwalLiburDitukar->tgl_selesai = $jadwalPengajuan->tgl_selesai;

                // Update potensi bug disini - 15-05-2025
                $jadwalLiburDitukar->save();
            } elseif ($jadwalLiburDitukar == null) {
                Log::info("[CreateTukarJadwal] Jadwal libur ditukar null, update jadwal libur pengajuan.");

                $jadwalLiburPengajuan->tgl_mulai = $jadwalDitukar->tgl_mulai;
                $jadwalLiburPengajuan->tgl_selesai = $jadwalDitukar->tgl_selesai;

                // Update potensi bug disini - 15-05-2025
                $jadwalLiburPengajuan->save();
            } else {
                Log::info("[CreateTukarJadwal] Tukar user_id pada jadwal libur.");

                $tempUserIdLibur = $jadwalLiburPengajuan->user_id;
                $jadwalLiburPengajuan->user_id = $jadwalLiburDitukar->user_id;
                $jadwalLiburDitukar->user_id = $tempUserIdLibur;

                // Update potensi bug disini - 15-05-2025
                $jadwalLiburPengajuan->save();
                $jadwalLiburDitukar->save();
            }

            $jadwalPengajuan->save();
            $jadwalDitukar->save();
            // if ($jadwalLiburPengajuan) {
            //     $jadwalLiburPengajuan->save();
            // }
            // if ($jadwalLiburDitukar) {
            //     $jadwalLiburDitukar->save();
            // }

            Log::info("[CreateTukarJadwal] Tukar jadwal berhasil disimpan.");
        }
        // Jika keduanya libur (shift_id == 0)
        else if ($jadwalPengajuan->shift_id == 0 && $jadwalDitukar->shift_id == 0) {
            Log::info("[CreateTukarJadwal] Kedua jadwal adalah libur (shift_id=0).");

            $tglMulaiPengajuan = RandomHelper::convertToDateString($jadwalPengajuan->tgl_mulai);
            $tglMulaiDitukar = RandomHelper::convertToDateString($jadwalDitukar->tgl_mulai);

            $jadwalKerjaPengajuan = Jadwal::where('user_id', $userPengajuan->id)
                ->where('tgl_mulai', $tglMulaiDitukar)
                ->whereNotNull('shift_id')
                ->first();

            $jadwalKerjaDitukar = Jadwal::where('user_id', $userDitukar->id)
                ->where('tgl_mulai', $tglMulaiPengajuan)
                ->whereNotNull('shift_id')
                ->first();

            Log::info("[CreateTukarJadwal] Jadwal kerja pengajuan ditemukan: " . ($jadwalKerjaPengajuan ? 'ada' : 'tidak ada'));
            Log::info("[CreateTukarJadwal] Jadwal kerja ditukar ditemukan: " . ($jadwalKerjaDitukar ? 'ada' : 'tidak ada'));

            // dd(
            //     "user pengajuan {$userPengajuan->id}, jadwal kerja yang ada {$jadwalKerjaPengajuan}",
            //     "user ditukar {$userDitukar->id}, jadwal kerja yang ada {$jadwalKerjaDitukar}"
            // );

            // Tukar user_id pada jadwal libur
            $tempUserId = $jadwalPengajuan->user_id;
            $jadwalPengajuan->user_id = $jadwalDitukar->user_id;
            $jadwalDitukar->user_id = $tempUserId;
            $jadwalPengajuan->save();
            $jadwalDitukar->save();

            if ($jadwalKerjaPengajuan && $jadwalKerjaDitukar) {
                Log::info("[CreateTukarJadwal] Tukar shift dan user_id pada jadwal kerja.");

                // Tukar shift mereka
                $tempShiftId = $jadwalKerjaPengajuan->shift_id;
                $jadwalKerjaPengajuan->shift_id = $jadwalKerjaDitukar->shift_id;
                $jadwalKerjaDitukar->shift_id = $tempShiftId;

                // Tukar user_id pada jadwal kerja
                $tempUserId = $jadwalKerjaPengajuan->user_id;
                $jadwalKerjaPengajuan->user_id = $jadwalKerjaDitukar->user_id;
                $jadwalKerjaDitukar->user_id = $tempUserId;
                $jadwalKerjaPengajuan->save();
                $jadwalKerjaDitukar->save();
            }
            Log::info("[CreateTukarJadwal] Proses tukar jadwal libur selesai.");
        } else {
            // return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak bisa menukar shift dengan libur atau sebaliknya.'), Response::HTTP_BAD_REQUEST);
            Log::warning("[CreateTukarJadwal] Gagal tukar jadwal: tidak bisa menukar shift dengan libur atau sebaliknya.");
            throw new \Exception('Tidak bisa menukar shift dengan libur atau sebaliknya.');
        }
    }
}
