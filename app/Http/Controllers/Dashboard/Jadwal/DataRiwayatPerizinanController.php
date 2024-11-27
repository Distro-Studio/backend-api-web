<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\Notifikasi;
use App\Models\RiwayatIzin;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\ModulVerifikasi;
use App\Models\RelasiVerifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataRiwayatPerizinanController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view riwayatPerizinan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Per page
            $limit = $request->input('limit', 10); // Default per page is 10

            $riwayat_izin = RiwayatIzin::query()->orderBy('created_at', 'desc');

            // Ambil semua filter dari request body
            $filters = $request->all();

            // Filter
            if (isset($filters['unit_kerja'])) {
                $namaUnitKerja = $filters['unit_kerja'];
                $riwayat_izin->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('id', $namaUnitKerja);
                    } else {
                        $query->where('id', '=', $namaUnitKerja);
                    }
                });
            }

            if (isset($filters['jabatan'])) {
                $namaJabatan = $filters['jabatan'];
                $riwayat_izin->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                    if (is_array($namaJabatan)) {
                        $query->whereIn('id', $namaJabatan);
                    } else {
                        $query->where('id', '=', $namaJabatan);
                    }
                });
            }

            if (isset($filters['status_karyawan'])) {
                $statusKaryawan = $filters['status_karyawan'];
                $riwayat_izin->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                    $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                        foreach ($masaKerja as $masa) {
                            $bulan = $masa * 12;
                            $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                        }
                    });
                } else {
                    $bulan = $masaKerja * 12;
                    $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($bulan, $currentDate) {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    });
                }
            }

            if (isset($filters['status_aktif'])) {
                $statusAktif = $filters['status_aktif'];
                $riwayat_izin->whereHas('users', function ($query) use ($statusAktif) {
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
                    $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                        $query->whereIn('tgl_masuk', $tglMasuk);
                    });
                } else {
                    $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                        $query->where('tgl_masuk', $tglMasuk);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $namaAgama = $filters['agama'];
                $riwayat_izin->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                    $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    });
                } else {
                    $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    });
                }
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $riwayat_izin->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                    $riwayat_izin->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    });
                } else {
                    $riwayat_izin->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['jenis_kompetensi'])) {
                $jenisKaryawan = $filters['jenis_kompetensi'];
                if (is_array($jenisKaryawan)) {
                    $riwayat_izin->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_kompetensi', $jk);
                            }
                        });
                    });
                } else {
                    $riwayat_izin->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_kompetensi', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['status_izin'])) {
                $namaStatusCuti = $filters['status_izin'];
                $riwayat_izin->whereHas('status_izins', function ($query) use ($namaStatusCuti) {
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
                $riwayat_izin->where(function ($query) use ($searchTerm) {
                    $query->whereHas('users', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                        $query->where('nik', 'like', $searchTerm);
                    });
                });
            }

            // Paginate
            if ($limit == 0) {
                $dataPerizinan = $riwayat_izin->get();
                $paginationData = null;
            } else {
                // Pastikan limit adalah integer
                $limit = is_numeric($limit) ? (int)$limit : 10;
                $dataPerizinan = $riwayat_izin->paginate($limit);

                $paginationData = [
                    'links' => [
                        'first' => $dataPerizinan->url(1),
                        'last' => $dataPerizinan->url($dataPerizinan->lastPage()),
                        'prev' => $dataPerizinan->previousPageUrl(),
                        'next' => $dataPerizinan->nextPageUrl(),
                    ],
                    'meta' => [
                        'current_page' => $dataPerizinan->currentPage(),
                        'last_page' => $dataPerizinan->lastPage(),
                        'per_page' => $dataPerizinan->perPage(),
                        'total' => $dataPerizinan->total(),
                    ]
                ];
            }

            if ($dataPerizinan->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data izin karyawan tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $formattedData = $dataPerizinan->map(function ($dataPerizinan) {
                $userId = $dataPerizinan->users->id ?? null;

                // Ambil max_order dari modul_verifikasis
                $modulVerifikasi = ModulVerifikasi::where('id', 4)->first();
                $maxOrder = $modulVerifikasi ? $modulVerifikasi->max_order : 0;

                // Ambil relasi verifikasi berdasarkan user
                $relasiVerifikasi = $userId ? RelasiVerifikasi::whereJsonContains('user_diverifikasi', (int) $userId)
                    ->where('modul_verifikasi', 4)
                    ->get() : collect();

                // Format data relasi verifikasi dengan loop berdasarkan max_order
                $formattedRelasiVerifikasi = [];
                for ($i = 1; $i <= $maxOrder; $i++) {
                    // Cari data verifikasi untuk order tertentu
                    $verifikasiForOrder = $relasiVerifikasi->firstWhere('order', $i);

                    if ($verifikasiForOrder) {
                        $formattedRelasiVerifikasi[] = [
                            'id' => $verifikasiForOrder->id,
                            'nama' => $verifikasiForOrder->nama,
                            'verifikator' => [
                                'id' => $verifikasiForOrder->users->id,
                                'nama' => $verifikasiForOrder->users->nama,
                                'username' => $verifikasiForOrder->users->username,
                                'email_verified_at' => $verifikasiForOrder->users->email_verified_at,
                                'data_karyawan_id' => $verifikasiForOrder->users->data_karyawan_id,
                                'foto_profil' => $verifikasiForOrder->users->foto_profil,
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
                        ];
                    } else {
                        // Jika tidak ada data untuk order tertentu, isi null
                        $formattedRelasiVerifikasi[] = [
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
                }

                $formattedRelasiVerifikasi = $relasiVerifikasi->isNotEmpty() ? $relasiVerifikasi->map(function ($verifikasi) {
                    return [
                        'id' => $verifikasi->id,
                        'nama' => $verifikasi->nama,
                        'verifikator' => $verifikasi->users, // Nama verifikator
                        'order' => $verifikasi->order,
                        'user_diverifikasi' => $verifikasi->user_diverifikasi,
                        'modul_verifikasi' => $verifikasi->modul_verifikasi,
                        'created_at' => $verifikasi->created_at,
                        'updated_at' => $verifikasi->updated_at
                    ];
                }) : null;
                return [
                    'id' => $dataPerizinan->id,
                    'user' => [
                        'id' => $dataPerizinan->users->id,
                        'nama' => $dataPerizinan->users->nama,
                        'username' => $dataPerizinan->users->username,
                        'email_verified_at' => $dataPerizinan->users->email_verified_at,
                        'data_karyawan_id' => $dataPerizinan->users->data_karyawan_id,
                        'foto_profil' => $dataPerizinan->users->foto_profil,
                        'data_completion_step' => $dataPerizinan->users->data_completion_step,
                        'status_aktif' => $dataPerizinan->users->status_aktif,
                        'created_at' => $dataPerizinan->users->created_at,
                        'updated_at' => $dataPerizinan->users->updated_at
                    ],
                    'tgl_izin' => $dataPerizinan->tgl_izin,
                    'waktu_izin' => $dataPerizinan->waktu_izin,
                    'durasi' => $dataPerizinan->durasi,
                    'keterangan' => $dataPerizinan->keterangan,
                    'status_izin' => $dataPerizinan->status_izins,
                    'relasi_verifikasi' => $formattedRelasiVerifikasi,
                    'created_at' => $dataPerizinan->created_at,
                    'updated_at' => $dataPerizinan->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data izin karyawan berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Riwayat Izin | - Error saat menampilkan data izin karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            if (!Gate::allows('view riwayatPerizinan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataIzin = RiwayatIzin::with(['users', 'status_izins', 'verifikator_izins'])->find($id);
            if (!$dataIzin) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data izin karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $formattedData = [
                'id' => $dataIzin->id,
                'user' => [
                    'id' => $dataIzin->users->id,
                    'nama' => $dataIzin->users->nama,
                    'email_verified_at' => $dataIzin->users->email_verified_at,
                    'data_karyawan_id' => $dataIzin->users->data_karyawan_id,
                    'foto_profil' => $dataIzin->users->foto_profil,
                    'data_completion_step' => $dataIzin->users->data_completion_step,
                    'status_aktif' => $dataIzin->users->status_aktif,
                    'created_at' => $dataIzin->users->created_at,
                    'updated_at' => $dataIzin->users->updated_at
                ],
                'tgl_izin' => $dataIzin->tgl_izin,
                'waktu_izin' => $dataIzin->waktu_izin,
                'durasi' => $dataIzin->durasi,
                'keterangan' => $dataIzin->keterangan,
                'status_izin' => $dataIzin->status_izins,
                'verifikator' => $dataIzin->verifikator_izins ?? null,
                'alasan' => $dataIzin->alasan ?? null,
                'created_at' => $dataIzin->created_at,
                'updated_at' => $dataIzin->updated_at
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data izin karyawan '{$dataIzin->users->nama}' berhasil ditampilkan.",
                'data' => $formattedData,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Riwayat Izin | - Error saat menampilkan detail data izin karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifikasiRiwayatIzin(Request $request, $izinId)
    {
        try {
            // 1. Dapatkan ID user yang login
            $verifikatorId = Auth::id();

            // 2. Dapatkan riwayat izin berdasarkan ID
            $riwayat_izin = RiwayatIzin::find($izinId);
            if (!$riwayat_izin) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Riwayat perizinan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // 3. Jika pengguna bukan Super Admin, lakukan pengecekan relasi verifikasi
            if (!Auth::user()->hasRole('Super Admin')) {
                // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
                $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                    ->where('modul_verifikasi', 4)
                    ->where('order', 1)
                    ->first();

                if (!$relasiVerifikasi) {
                    return response()->json([
                        'status' => Response::HTTP_NOT_FOUND,
                        'message' => "Anda tidak memiliki hak akses untuk verifikasi perizinan dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                        'relasi_verifikasi' => null,
                    ], Response::HTTP_NOT_FOUND);
                }

                // 4. Dapatkan karyawan yang mengajukan izin
                $pengajuIzinUserId = $riwayat_izin->user_id;

                // 5. Samakan user_id pengajuan Izin dengan string array user_diverifikasi di tabel relasi_verifikasis
                $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
                if (!is_array($userDiverifikasi)) {
                    Log::warning('Kesalahan format data user diverifikasi pada verif 1 riwayat perizinan');
                    return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if (!in_array($pengajuIzinUserId, $userDiverifikasi)) {
                    return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi izin ini karena karyawan tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
                }

                // 6. Validasi nilai kolom order dan status_izin_id
                if ($relasiVerifikasi->order != 1) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Izin ini tidak dalam status untuk disetujui.'), Response::HTTP_BAD_REQUEST);
                }
            }

            // Logika untuk menyetujui atau menolak verifikasi tahap 1
            $status_izin_id = $riwayat_izin->status_izin_id;

            if ($request->has('verifikasi_pertama_disetujui') && $request->verifikasi_pertama_disetujui == 1) {
                if ($status_izin_id == 1) {
                    $riwayat_izin->status_izin_id = 2;
                    $riwayat_izin->verifikator_1 = Auth::id();
                    $riwayat_izin->alasan = null;
                    $riwayat_izin->save();

                    $data_karyawan = DB::table('data_karyawans')
                        ->where('user_id', $riwayat_izin->user_id)
                        ->first(['id', 'status_reward_presensi']);

                    if ($data_karyawan && $data_karyawan->status_reward_presensi) {
                        DB::table('data_karyawans')
                            ->where('id', $data_karyawan->id)
                            ->update(['status_reward_presensi' => false]);

                        Log::info("Status reward presensi karyawan ID {$data_karyawan->id} diubah menjadi false.");
                    } else {
                        Log::info("Status reward presensi karyawan ID {$data_karyawan->id} sudah false, tidak dilakukan update.");
                    }

                    // Buat dan simpan notifikasi
                    $this->createNotifikasiIzin($riwayat_izin, 'Disetujui');

                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi perizinan dari '{$riwayat_izin->users->nama}' telah disetujui."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Riwayat izin dari '{$riwayat_izin->users->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
                }
            } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
                if ($status_izin_id == 1) {
                    $riwayat_izin->status_izin_id = 3;
                    $riwayat_izin->verifikator_1 = Auth::id();
                    $riwayat_izin->alasan = $request->input('alasan', null);
                    $riwayat_izin->save();

                    // Buat dan simpan notifikasi
                    $this->createNotifikasiIzin($riwayat_izin, 'Ditolak');

                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi perizinan dari '{$riwayat_izin->users->nama}' telah ditolak."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Riwayat Izin '{$riwayat_izin->id}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            Log::error('| Riwayat Izin | - Error saat melakukan verifikasi izin karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiIzin($riwayat_izin, $status)
    {
        try {
            $statusText = $status === 'Disetujui' ? 'disetujui' : 'ditolak';
            $penerima_notif = $riwayat_izin->users->nama;

            // Pesan untuk karyawan terkait
            $messageForUser = "Pengajuan izin dari '{$penerima_notif}' untuk tanggal '{$riwayat_izin->tgl_izin}' telah {$statusText}.";

            // Pesan untuk Super Admin
            $messageForSuperAdmin = "Notifikasi untuk Super Admin: Pengajuan izin dari '{$penerima_notif}' untuk tanggal '{$riwayat_izin->tgl_izin}' telah {$statusText}.";

            $userIds = [$riwayat_izin->user_id, 1];
            foreach ($userIds as $userId) {
                $message = $userId === 1 ? $messageForSuperAdmin : $messageForUser;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 10, // Kategori notifikasi izin
                    'user_id' => $userId,
                    'message' => $message,
                    'is_read' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);

                Log::info('Notifikasi untuk user_id ' . $userId . ' berhasil dikirim.');
            }
        } catch (\Exception $e) {
            Log::error('| Notifikasi Izin | - Error saat mengirim notifikasi: ' . $e->getMessage());
        }
    }
}
