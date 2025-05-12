<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\TipeCuti;
use App\Models\Notifikasi;
use App\Models\TukarJadwal;
use App\Models\DataKaryawan;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\CutiJadwalExport;
use App\Http\Requests\StoreCutiJadwalRequest;
use App\Http\Requests\UpdateCutiJadwalRequest;
use App\Http\Resources\Dashboard\Jadwal\CutiJadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\RiwayatIzin;

class DataCutiController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view cutiKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Per page
            $limit = $request->input('limit', 10);

            $cuti = Cuti::query()->orderBy('created_at', 'desc');

            // Ambil semua filter dari request body
            $filters = $request->all();

            // Filter
            if (isset($filters['unit_kerja'])) {
                $namaUnitKerja = $filters['unit_kerja'];
                $cuti->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('id', $namaUnitKerja);
                    } else {
                        $query->where('id', '=', $namaUnitKerja);
                    }
                });
            }

            if (isset($filters['jabatan'])) {
                $namaJabatan = $filters['jabatan'];
                $cuti->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                    if (is_array($namaJabatan)) {
                        $query->whereIn('id', $namaJabatan);
                    } else {
                        $query->where('id', '=', $namaJabatan);
                    }
                });
            }

            if (isset($filters['status_karyawan'])) {
                $statusKaryawan = $filters['status_karyawan'];
                $cuti->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                    $cuti->whereHas('users.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                        foreach ($masaKerja as $masa) {
                            $bulan = $masa * 12;
                            $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                        }
                    });
                } else {
                    $bulan = $masaKerja * 12;
                    $cuti->whereHas('users.data_karyawans', function ($query) use ($bulan, $currentDate) {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    });
                }
            }

            if (isset($filters['status_aktif'])) {
                $statusAktif = $filters['status_aktif'];
                $cuti->whereHas('users', function ($query) use ($statusAktif) {
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
                    $cuti->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                        $query->whereIn('tgl_masuk', $tglMasuk);
                    });
                } else {
                    $cuti->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                        $query->where('tgl_masuk', $tglMasuk);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $namaAgama = $filters['agama'];
                $cuti->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                    $cuti->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    });
                } else {
                    $cuti->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    });
                }
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $cuti->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                    $cuti->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    });
                } else {
                    $cuti->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['jenis_kompetensi'])) {
                $jenisKaryawan = $filters['jenis_kompetensi'];
                if (is_array($jenisKaryawan)) {
                    $cuti->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_kompetensi', $jk);
                            }
                        });
                    });
                } else {
                    $cuti->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_kompetensi', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['tipe_cuti'])) {
                $namaTipeCuti = $filters['tipe_cuti'];
                $cuti->whereHas('tipe_cutis', function ($query) use ($namaTipeCuti) {
                    if (is_array($namaTipeCuti)) {
                        $query->whereIn('id', $namaTipeCuti);
                    } else {
                        $query->where('id', '=', $namaTipeCuti);
                    }
                });
            }

            if (isset($filters['status_cuti'])) {
                $namaStatusCuti = $filters['status_cuti'];
                $cuti->whereHas('status_cutis', function ($query) use ($namaStatusCuti) {
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
                $cuti->where(function ($query) use ($searchTerm) {
                    $query->whereHas('users', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                        $query->where('nik', 'like', $searchTerm);
                    });
                });
            }

            // Paginate
            if ($limit == 0) {
                $dataCuti = $cuti->get();
                $paginationData = null;
            } else {
                // Pastikan limit adalah integer
                $limit = is_numeric($limit) ? (int)$limit : 10;
                $dataCuti = $cuti->paginate($limit);

                $paginationData = [
                    'links' => [
                        'first' => $dataCuti->url(1),
                        'last' => $dataCuti->url($dataCuti->lastPage()),
                        'prev' => $dataCuti->previousPageUrl(),
                        'next' => $dataCuti->nextPageUrl(),
                    ],
                    'meta' => [
                        'current_page' => $dataCuti->currentPage(),
                        'last_page' => $dataCuti->lastPage(),
                        'per_page' => $dataCuti->perPage(),
                        'total' => $dataCuti->total(),
                    ]
                ];
            }

            if ($dataCuti->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data cuti karyawan tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $formattedData = $dataCuti->map(function ($dataCuti) {
                $userId = $dataCuti->users->id ?? null;

                // Ambil kuota cuti berdasarkan tipe cuti
                $leaveType = TipeCuti::find($dataCuti->tipe_cuti_id);
                $quota = $leaveType->kuota;

                // Hitung jumlah hari cuti yang sudah digunakan dalam tahun ini
                $usedDays = Cuti::where('tipe_cuti_id', $leaveType->id)
                    ->whereNotIn('status_cuti_id', [3, 5])
                    ->where('user_id', $userId)
                    ->whereYear('created_at', Carbon::now('Asia/Jakarta')->year)
                    ->get()
                    ->sum(function ($cuti) {
                        $tglFrom = Carbon::parse($cuti->tgl_from);
                        $tglTo = Carbon::parse($cuti->tgl_to);
                        return $tglFrom->diffInDays($tglTo) + 1;
                    });
                // dd($usedDays);

                // Hitung sisa kuota
                $sisaKuota = $quota - $usedDays;

                // Ambil max_order dari modul_verifikasis
                $modulVerifikasi = ModulVerifikasi::where('id', 3)->first(); // 3 untuk modul cuti
                $maxOrder = $modulVerifikasi ? $modulVerifikasi->max_order : 0;

                // Ambil relasi verifikasi berdasarkan user
                $relasiVerifikasi = $userId ? RelasiVerifikasi::whereJsonContains('user_diverifikasi', (int) $userId)
                    ->where('modul_verifikasi', 3) // 3 adalah modul cuti
                    ->get() : collect();

                // Format data relasi verifikasi dengan loop berdasarkan max_order
                $formattedRelasiVerifikasi = [];
                for ($i = 1; $i <= $maxOrder; $i++) {
                    // Cari data verifikasi untuk order tertentu
                    $verifikasiForOrder = $relasiVerifikasi->firstWhere('order', $i);

                    $baseUrl = env('STORAGE_SERVER_DOMAIN');
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

                return [
                    'id' => $dataCuti->id,
                    'user' => [
                        'id' => $dataCuti->users->id,
                        'nama' => $dataCuti->users->nama,
                        'username' => $dataCuti->users->username,
                        'email_verified_at' => $dataCuti->users->email_verified_at,
                        'data_karyawan_id' => $dataCuti->users->data_karyawan_id,
                        'foto_profil' => $dataCuti->users->foto_profiles ? [
                            'id' => $dataCuti->users->foto_profiles->id,
                            'user_id' => $dataCuti->users->foto_profiles->user_id,
                            'file_id' => $dataCuti->users->foto_profiles->file_id,
                            'nama' => $dataCuti->users->foto_profiles->nama,
                            'nama_file' => $dataCuti->users->foto_profiles->nama_file,
                            'path' => $baseUrl . $dataCuti->users->foto_profiles->path,
                            'ext' => $dataCuti->users->foto_profiles->ext,
                            'size' => $dataCuti->users->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $dataCuti->users->data_completion_step,
                        'status_aktif' => $dataCuti->users->status_aktif,
                        'created_at' => $dataCuti->users->created_at,
                        'updated_at' => $dataCuti->users->updated_at
                    ],
                    'unit_kerja' => $dataCuti->users->data_karyawans->unit_kerjas,
                    'tipe_cuti' => $dataCuti->tipe_cutis,
                    'keterangan' => $dataCuti->keterangan ?? null,
                    'tgl_from' => $dataCuti->tgl_from,
                    'tgl_to' => $dataCuti->tgl_to,
                    'catatan' => $dataCuti->catatan,
                    'durasi' => $dataCuti->durasi,
                    'total_kuota' => $quota,
                    'sisa_kuota' => $sisaKuota,
                    'status_cuti' => $dataCuti->status_cutis,
                    'alasan' => $dataCuti->alasan ?? null,
                    'relasi_verifikasi' => $formattedRelasiVerifikasi,
                    'created_at' => $dataCuti->created_at,
                    'updated_at' => $dataCuti->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data cuti karyawan berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat mengambil data cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreCutiJadwalRequest $request)
    {
        try {
            if (!Gate::allows('create cutiKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();
            $verifikatorId = Auth::id();

            $tglFrom = Carbon::createFromFormat('d-m-Y', $data['tgl_from'])->format('Y-m-d');
            $tglTo = Carbon::createFromFormat('d-m-Y', $data['tgl_to'])->format('Y-m-d');

            $overlappingCuti = Cuti::where('user_id', $data['user_id'])
                ->where(function ($query) use ($tglFrom, $tglTo) {
                    $query->whereBetween(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), [$tglFrom, $tglTo])
                        ->orWhereBetween(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), [$tglFrom, $tglTo])
                        ->orWhere(function ($query) use ($tglFrom, $tglTo) {
                            $query->where(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', $tglFrom)
                                ->where(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', $tglTo);
                        });
                })
                ->exists();
            if ($overlappingCuti) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Karyawan tersebut sudah memiliki cuti pada rentang tanggal yang diajukan.'), Response::HTTP_BAD_REQUEST);
            }

            // Mengambil informasi tipe cuti berdasarkan tipe_cuti_id
            $tipeCuti = TipeCuti::find($data['tipe_cuti_id']);
            if (!$tipeCuti) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tipe cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            DB::beginTransaction();

            // Mengonversi tanggal dari request menggunakan helper hanya untuk perhitungan durasi
            $tglFrom = Carbon::createFromFormat('d-m-Y', $data['tgl_from'])->format('Y-m-d');
            $tglTo = Carbon::createFromFormat('d-m-Y', $data['tgl_to'])->format('Y-m-d');

            // Menghitung durasi cuti dalam hari
            $durasi = Carbon::parse($tglFrom)->diffInDays($tglTo) + 1;

            // Validasi durasi cuti terhadap kuota tipe cuti
            if ($durasi > $tipeCuti->kuota) {
                $message = "Durasi cuti ({$durasi} hari) melebihi kuota yang diizinkan untuk tipe cuti '{$tipeCuti->nama}'. Kuota maksimal: {$tipeCuti->kuota} hari.";
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $message), Response::HTTP_BAD_REQUEST);
            }

            // Ambil semua data cuti dengan tipe_cuti_id dan user_id yang sama pada tahun berjalan
            $currentYear = Carbon::now('Asia/Jakarta')->year;
            $cutiRecords = Cuti::where('user_id', $data['user_id'])
                ->where('tipe_cuti_id', $data['tipe_cuti_id'])
                ->where('status_cuti_id', 4)
                ->where(function ($query) use ($currentYear) {
                    $query->whereYear(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), $currentYear);
                })
                ->get();

            // Hitung total durasi cuti yang sudah diambil
            $totalDurasiDiambil = $cutiRecords->sum('durasi');

            // Tambahkan durasi baru ke total durasi yang sudah diambil
            $totalDurasi = $totalDurasiDiambil + $durasi;

            // Validasi total durasi terhadap kuota
            if ($totalDurasi > $tipeCuti->kuota) {
                $sisaCuti = $tipeCuti->kuota - $totalDurasiDiambil;
                $message = "Durasi cuti melebihi kuota yang diizinkan untuk tipe cuti '{$tipeCuti->nama}'. Sisa kuota cuti tahun ini: {$sisaCuti} hari. Total durasi yang diajukan: {$totalDurasi} hari.";
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $message), Response::HTTP_BAD_REQUEST);
            }

            // if (Gate::allows('verifikasi2 cutiKaryawan')) {
            //     $statusCutiId = 4;

            //     // Step 1: Cek jadwal kerja shift, jika ada yang cuti mendadak dan jika masih ada jadwal
            //     $jadwalConflicts = Jadwal::where('user_id', $data['user_id'])
            //         ->where(function ($query) use ($tglFrom, $tglTo) {
            //             $query->whereBetween('tgl_mulai', [$tglFrom, $tglTo])
            //                 ->orWhereBetween('tgl_selesai', [$tglFrom, $tglTo])
            //                 ->orWhere(function ($query) use ($tglFrom, $tglTo) {
            //                     $query->where('tgl_mulai', '<=', $tglFrom)
            //                         ->where('tgl_selesai', '>=', $tglTo);
            //                 });
            //         })->get();

            //     if ($jadwalConflicts->isNotEmpty()) {
            //         foreach ($jadwalConflicts as $jadwal) {
            //             $tukarJadwal = TukarJadwal::where('jadwal_pengajuan', $jadwal->id)
            //                 ->orWhere('jadwal_ditukar', $jadwal->id)
            //                 ->first();

            //             if ($tukarJadwal) {
            //                 // Kembalikan user_id dari jadwal_pengajuan ke user_pengajuan
            //                 $jadwalPengajuan = Jadwal::find($tukarJadwal->jadwal_pengajuan);
            //                 if ($jadwalPengajuan) {
            //                     $jadwalPengajuan->user_id = $tukarJadwal->user_pengajuan;
            //                     $jadwalPengajuan->save();

            //                     $this->createNotifikasiJadwalKembali($jadwalPengajuan->user_id, $jadwalPengajuan->id);
            //                 }

            //                 // Kembalikan user_id dari jadwal_ditukar ke user_ditukar
            //                 $jadwalDitukar = Jadwal::find($tukarJadwal->jadwal_ditukar);
            //                 if ($jadwalDitukar) {
            //                     $jadwalDitukar->user_id = $tukarJadwal->user_ditukar;
            //                     $jadwalDitukar->save();

            //                     $this->createNotifikasiJadwalKembali($jadwalDitukar->user_id, $jadwalDitukar->id);
            //                 }

            //                 // Hapus tukar jadwal setelah dikembalikan
            //                 $tukarJadwal->delete();
            //             }

            //             // Hapus lembur
            //             Lembur::where('jadwal_id', $jadwal->id)->delete();

            //             // Hapus jadwal
            //             Jadwal::where('user_id', $data['user_id'])->delete();
            //         }
            //     }

            //     $data['verifikator_1'] = $verifikatorId;
            //     $data['verifikator_2'] = $verifikatorId;
            // } elseif (Gate::allows('verifikasi1 cutiKaryawan')) {
            //     $statusCutiId = 2;
            //     $data['verifikator_1'] = $verifikatorId;
            // } else {
            //     $statusCutiId = 1;
            // }

            // Menambahkan durasi ke data sebelum menyimpan
            $data['durasi'] = $durasi;
            $data['status_cuti_id'] = 1;

            // New Update
            $data['presensi_ids'] = $this->getPresensiIds($data['user_id'], $data['tgl_from'], $data['tgl_to']);
            $data['jadwal_ids'] = $this->getJadwalIds($data['user_id'], $data['tgl_from'], $data['tgl_to']);
            $data['izin_ids'] = $this->getIzinIds($data['user_id'], $data['tgl_from'], $data['tgl_to']);
            $data['lembur_ids'] = $this->getLemburIds($data['user_id'], $data['tgl_from'], $data['tgl_to']);
            // $data['status_cuti_id'] = $statusCutiId;
            $dataCuti = Cuti::create($data);
            // dd("Presensi: {$data['presensi_ids']}, Jadwal: {$data['jadwal_ids']}, Izin: {$data['izin_ids']}, Lembur: {$data['lembur_ids']}");

            // Setelah pembuatan cuti, cari cuti yang baru dibuat dan periksa cuti_administratif
            // $cutiTerbaru = Cuti::where('user_id', $data['user_id'])->latest()->first();
            // if ($cutiTerbaru && !$cutiTerbaru->tipe_cutis->cuti_administratif) {
            //     DB::table('data_karyawans')
            //         ->where('user_id', $data['user_id'])
            //         ->update(['status_reward_presensi' => false]);
            // }

            $message = "Data cuti karyawan '{$dataCuti->users->nama}' berhasil dibuat untuk tipe cuti '{$dataCuti->tipe_cutis->nama}' dengan durasi {$dataCuti->durasi} hari.";

            $konversiNotif_tgl_from = Carbon::parse($dataCuti->tgl_from)->locale('id')->isoFormat('D MMMM YYYY');
            $konversiNotif_tgl_to = Carbon::parse($dataCuti->tgl_to)->locale('id')->isoFormat('D MMMM YYYY');

            $userIds = [$dataCuti->user_id, $verifikatorId];

            foreach ($userIds as $userId) {
                Notifikasi::create([
                    'kategori_notifikasi_id' => 1,
                    'user_id' => $userId,
                    'message' => "'{$dataCuti->users->nama}', mendapatkan cuti {$dataCuti->tipe_cutis->nama} dengan durasi {$dataCuti->durasi} hari yang dimulai pada {$konversiNotif_tgl_from} s/d {$konversiNotif_tgl_to}.",
                    'is_read' => false,
                    'is_verifikasi' => true,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }

            DB::commit();

            return response()->json(new CutiJadwalResource(Response::HTTP_OK, $message, $dataCuti), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat menyimpan data cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            if (!Gate::allows('view cutiKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataCuti = Cuti::with(['users.data_karyawans.unit_kerjas', 'users.cutis', 'tipe_cutis', 'status_cutis'])->find($id);
            if (!$dataCuti) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $listCuti = $dataCuti->users->cutis->map(function ($cuti) use ($baseUrl) {
                return [
                    'id' => $cuti->id,
                    'tipe_cuti' => $cuti->tipe_cutis,
                    'keterangan' => $cuti->keterangan ?? null,
                    'tgl_from' => $cuti->tgl_from,
                    'tgl_to' => $cuti->tgl_to,
                    'catatan' => $cuti->catatan,
                    'durasi' => $cuti->durasi,
                    'status_cuti' => $cuti->status_cutis,
                    'verifikator_1' => $cuti->verifikator_1_cutis ? [
                        'id' => $cuti->verifikator_1_cutis->id,
                        'nama' => $cuti->verifikator_1_cutis->nama,
                        'email_verified_at' => $cuti->verifikator_1_cutis->email_verified_at,
                        'data_karyawan_id' => $cuti->verifikator_1_cutis->data_karyawan_id,
                        'foto_profil' => $cuti->verifikator_1_cutis->foto_profiles ? [
                            'id' => $cuti->verifikator_1_cutis->foto_profiles->id,
                            'user_id' => $cuti->verifikator_1_cutis->foto_profiles->user_id,
                            'file_id' => $cuti->verifikator_1_cutis->foto_profiles->file_id,
                            'nama' => $cuti->verifikator_1_cutis->foto_profiles->nama,
                            'nama_file' => $cuti->verifikator_1_cutis->foto_profiles->nama_file,
                            'path' => $baseUrl . $cuti->verifikator_1_cutis->foto_profiles->path,
                            'ext' => $cuti->verifikator_1_cutis->foto_profiles->ext,
                            'size' => $cuti->verifikator_1_cutis->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $cuti->verifikator_1_cutis->data_completion_step,
                        'status_aktif' => $cuti->verifikator_1_cutis->status_aktif,
                        'created_at' => $cuti->verifikator_1_cutis->created_at,
                        'updated_at' => $cuti->verifikator_1_cutis->updated_at
                    ] : null,
                    'verifikator_2' => $cuti->verifikator_2_cutis ? [
                        'id' => $cuti->verifikator_2_cutis->id,
                        'nama' => $cuti->verifikator_2_cutis->nama,
                        'email_verified_at' => $cuti->verifikator_2_cutis->email_verified_at,
                        'data_karyawan_id' => $cuti->verifikator_2_cutis->data_karyawan_id,
                        'foto_profil' => $cuti->verifikator_2_cutis->foto_profiles ? [
                            'id' => $cuti->verifikator_2_cutis->foto_profiles->id,
                            'user_id' => $cuti->verifikator_2_cutis->foto_profiles->user_id,
                            'file_id' => $cuti->verifikator_2_cutis->foto_profiles->file_id,
                            'nama' => $cuti->verifikator_2_cutis->foto_profiles->nama,
                            'nama_file' => $cuti->verifikator_2_cutis->foto_profiles->nama_file,
                            'path' => $baseUrl . $cuti->verifikator_2_cutis->foto_profiles->path,
                            'ext' => $cuti->verifikator_2_cutis->foto_profiles->ext,
                            'size' => $cuti->verifikator_2_cutis->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $cuti->verifikator_2_cutis->data_completion_step,
                        'status_aktif' => $cuti->verifikator_2_cutis->status_aktif,
                        'created_at' => $cuti->verifikator_2_cutis->created_at,
                        'updated_at' => $cuti->verifikator_2_cutis->updated_at
                    ] : null,
                    'alasan' => $cuti->alasan ?? null,
                    'created_at' => $cuti->created_at,
                    'updated_at' => $cuti->updated_at
                ];
            });

            $formattedData = [
                'id' => $dataCuti->id,
                'user' => [
                    'id' => $dataCuti->users->id,
                    'nama' => $dataCuti->users->nama,
                    'username' => $dataCuti->users->username,
                    'email_verified_at' => $dataCuti->users->email_verified_at,
                    'data_karyawan_id' => $dataCuti->users->data_karyawan_id,
                    'foto_profil' => $dataCuti->users->foto_profiles ? [
                        'id' => $dataCuti->users->foto_profiles->id,
                        'user_id' => $dataCuti->users->foto_profiles->user_id,
                        'file_id' => $dataCuti->users->foto_profiles->file_id,
                        'nama' => $dataCuti->users->foto_profiles->nama,
                        'nama_file' => $dataCuti->users->foto_profiles->nama_file,
                        'path' => $baseUrl . $dataCuti->users->foto_profiles->path,
                        'ext' => $dataCuti->users->foto_profiles->ext,
                        'size' => $dataCuti->users->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $dataCuti->users->data_completion_step,
                    'status_aktif' => $dataCuti->users->status_aktif,
                    'created_at' => $dataCuti->users->created_at,
                    'updated_at' => $dataCuti->users->updated_at
                ],
                'unit_kerja' => $dataCuti->users->data_karyawans->unit_kerjas,
                'list_cuti' => $listCuti
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data cuti karyawan '{$dataCuti->users->nama}' berhasil ditampilkan.",
                'data' => $formattedData,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat menampilkan detail data cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateCutiJadwalRequest $request, $id)
    {
        try {
            if (!Gate::allows('edit cutiKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();
            $dataCuti = Cuti::find($id);
            if (!$dataCuti) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Mengonversi tanggal dari request menggunakan helper hanya untuk perhitungan durasi
            $tglFrom = Carbon::createFromFormat('d-m-Y', $data['tgl_from'])->format('Y-m-d');
            $tglTo = Carbon::createFromFormat('d-m-Y', $data['tgl_to'])->format('Y-m-d');

            // Menghitung durasi cuti dalam hari
            $durasi = Carbon::parse($tglFrom)->diffInDays(Carbon::parse($tglTo));

            // Validasi durasi cuti terhadap kuota tipe cuti
            $tipeCuti = TipeCuti::find($data['tipe_cuti_id']);
            if (!$tipeCuti) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tipe cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Periksa cuti yang telah diambil pada tahun berjalan
            $currentYear = Carbon::now()->year;
            $cutiTakenThisYear = Cuti::where('user_id', $dataCuti->user_id)
                ->where('tipe_cuti_id', $data['tipe_cuti_id'])
                ->whereYear('tgl_from', $currentYear)
                ->where('id', '!=', $id)
                ->sum('durasi');

            // Tambahkan durasi cuti yang sedang diupdate
            $totalCutiTaken = $cutiTakenThisYear + $durasi;

            // Hitung sisa kuota cuti
            $sisaCuti = $tipeCuti->kuota - $cutiTakenThisYear;
            if ($tipeCuti->kuota > 0 && $totalCutiTaken > $tipeCuti->kuota) {
                $message = "Durasi cuti ({$durasi} hari) melebihi sisa kuota yang diizinkan untuk tipe cuti '{$tipeCuti->nama}' ({$sisaCuti} hari tersisa).";
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $message), Response::HTTP_BAD_REQUEST);
            }

            // Menambahkan durasi ke data sebelum memperbarui
            $data['durasi'] = $durasi;
            $data['status_cuti_id'] = 2;

            $dataCuti->update($data);

            $message = "Data cuti karyawan '{$dataCuti->users->nama}' berhasil diperbarui untuk tipe cuti '{$dataCuti->tipe_cutis->nama}' dengan durasi {$dataCuti->durasi} hari.";

            return response()->json(new CutiJadwalResource(Response::HTTP_OK, $message, $dataCuti), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat update data cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteCuti(Request $request)
    {
        try {
            if (!Gate::allows('delete cutiKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Validasi request
            $request->validate([
                'ids_cuti' => 'required|array',
                'ids_cuti.*' => 'integer|exists:cutis,id'
            ]);

            $idsCuti = $request->input('ids_cuti');

            DB::beginTransaction();

            // Looping untuk mendapatkan ID yang perlu di-restore sebelum menghapus cuti
            foreach ($idsCuti as $cutiId) {
                $cuti = Cuti::find($cutiId);
                if ($cuti) {
                    $restoreData = [
                        Presensi::class => json_decode($cuti->presensi_ids, true) ?? [],
                        Jadwal::class => json_decode($cuti->jadwal_ids, true) ?? [],
                        RiwayatIzin::class => json_decode($cuti->izin_ids, true) ?? [],
                        Lembur::class => json_decode($cuti->lembur_ids, true) ?? []
                    ];

                    // Loop untuk restore semua entitas
                    foreach ($restoreData as $model => $ids) {
                        $this->restoreEntities($model, $ids);
                    }
                }
            }

            // Setelah semua data di-restore, hapus cuti
            $deletedCount = Cuti::whereIn('id', $idsCuti)->delete();

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Total $deletedCount data cuti berhasil dihapus."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat restore data cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportJadwalCuti(Request $request)
    {
        try {
            if (!Gate::allows('export cutiKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataCuti = Cuti::all();
            if ($dataCuti->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data cuti karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new CutiJadwalExport($request->all()), 'cuti-karyawan.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat export cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifikasiTahap1(Request $request, $cutiId)
    {
        try {
            // 1. Dapatkan ID user yang login
            $verifikatorId = Auth::id();

            // 3. Dapatkan cuti berdasarkan ID
            $cuti = Cuti::find($cutiId);
            if (!$cuti) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            if (!Auth::user()->hasRole('Super Admin')) {
                // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
                $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                    ->where('modul_verifikasi', 3)
                    ->where('order', 1)
                    ->first();

                if (!$relasiVerifikasi) {
                    return response()->json([
                        'status' => Response::HTTP_NOT_FOUND,
                        'message' => "Anda tidak memiliki hak akses untuk verifikasi cuti tahap 1 dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                        'relasi_verifikasi' => null,
                    ], Response::HTTP_NOT_FOUND);
                }

                // 4. Dapatkan karyawan yang mengajukan cuti dengan user_id di tabel cutis
                $pengajuCutiUserId = $cuti->user_id;

                // 5. Samakan user_id pengajuan cuti dengan string array user_diverifikasi di tabel relasi_verifikasis
                $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
                if (!is_array($userDiverifikasi)) {
                    Log::warning('Kesalahan format data user diverifikasi pada verif 1 cuti');
                    return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if (!in_array($pengajuCutiUserId, $userDiverifikasi)) {
                    return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi cuti ini karena karyawan tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
                }

                // 6. Validasi nilai kolom order dan status_cuti_id
                if ($relasiVerifikasi->order != 1) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Cuti ini tidak dalam status untuk disetujui pada tahap 1.'), Response::HTTP_BAD_REQUEST);
                }
            }

            $status_cuti_id = $cuti->status_cuti_id;

            if ($request->has('verifikasi_pertama_disetujui') && $request->verifikasi_pertama_disetujui == 1) {
                if ($status_cuti_id == 1) {
                    $cuti->status_cuti_id = 2; // Update status ke tahap 1 disetujui
                    $cuti->verifikator_1 = $verifikatorId;
                    $cuti->alasan = null;
                    $cuti->save();

                    // Buat dan simpan notifikasi
                    $this->createNotifikasiCutiTahap1($cuti, 'Disetujui');

                    // Cek apakah cuti_administratif pada tipe_cutis adalah true atau false
                    if (!$cuti->tipe_cutis->cuti_administratif) {
                        // Jika cuti_administratif = false, lakukan update status_reward_presensi menjadi false
                        $user_id = $cuti->user_id;
                        $data_karyawan = DB::table('data_karyawans')
                            ->where('user_id', $user_id)
                            ->first(['id', 'status_reward_presensi']);

                        if ($data_karyawan && $data_karyawan->status_reward_presensi) {
                            DB::table('data_karyawans')
                                ->where('id', $data_karyawan->id)
                                ->update(['status_reward_presensi' => false]);

                            Log::info("Status reward presensi karyawan ID {$data_karyawan->id} diubah menjadi false.");
                        } else {
                            Log::info("Status reward presensi karyawan ID {$data_karyawan->id} sudah false, tidak dilakukan update.");
                        }
                    }


                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 untuk Cuti '{$cuti->tipe_cutis->nama}' telah disetujui."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Cuti '{$cuti->tipe_cutis->nama}' tidak dalam status untuk disetujui pada tahap 1."), Response::HTTP_BAD_REQUEST);
                }
            } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
                if ($status_cuti_id == 1) {
                    $cuti->status_cuti_id = 3; // Update status ke tahap 1 ditolak
                    $cuti->verifikator_1 = $verifikatorId;
                    $cuti->alasan = $request->input('alasan');
                    $cuti->save();

                    // Buat dan simpan notifikasi
                    $this->createNotifikasiCutiTahap1($cuti, 'Ditolak');


                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 untuk Cuti '{$cuti->tipe_cutis->nama}' telah ditolak."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Cuti '{$cuti->tipe_cutis->nama}' tidak dalam status untuk ditolak pada tahap 1."), Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat verif 1 cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifikasiTahap2(Request $request, $cutiId)
    {
        try {
            $verifikatorId = Auth::id();

            // 3. Dapatkan cuti berdasarkan ID
            $cuti = Cuti::find($cutiId);
            // dd($cuti);
            if (!$cuti) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // 3. Jika pengguna bukan Super Admin, lakukan pengecekan relasi verifikasi
            if (!Auth::user()->hasRole('Super Admin')) {
                // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
                $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                    ->where('modul_verifikasi', 3)
                    ->where('order', 2)
                    ->first();

                if (!$relasiVerifikasi) {
                    return response()->json([
                        'status' => Response::HTTP_NOT_FOUND,
                        'message' => "Anda tidak memiliki hak akses untuk verifikasi cuti tahap 2 dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                        'relasi_verifikasi' => null,
                    ], Response::HTTP_NOT_FOUND);
                }

                // 4. Dapatkan karyawan yang mengajukan cuti dengan user_id di tabel cutis
                $pengajuCutiUserId = $cuti->user_id;

                // 5. Samakan user_id pengajuan cuti dengan string array user_diverifikasi di tabel relasi_verifikasis
                $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
                if (!is_array($userDiverifikasi)) {
                    Log::warning('Kesalahan format data user diverifikasi pada verif 2 cuti');
                    return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if (!in_array($pengajuCutiUserId, $userDiverifikasi)) {
                    return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi cuti ini karena karyawan tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
                }

                // 6. Validasi nilai kolom order dan status_cuti_id
                if ($relasiVerifikasi->order != 2) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Cuti ini tidak dalam status untuk disetujui pada tahap 2.'), Response::HTTP_BAD_REQUEST);
                }
            }

            // Logika untuk menyetujui verifikasi tahap 2
            $status_cuti_id = $cuti->status_cuti_id;

            if ($request->has('verifikasi_kedua_disetujui') && $request->verifikasi_kedua_disetujui == 1) {
                if ($status_cuti_id == 2) {
                    $cuti->status_cuti_id = 4;
                    $cuti->verifikator_2 = $verifikatorId;
                    $cuti->alasan = null;
                    $cuti->save();

                    // Step 1: Cek Hapus jadwal kerja shift dan non shift jika cuti mendadak dan jika masih ada jadwal
                    $tglFrom = Carbon::createFromFormat('d-m-Y', $cuti->tgl_from)->format('Y-m-d');
                    $tglTo = Carbon::createFromFormat('d-m-Y', $cuti->tgl_to)->format('Y-m-d');

                    $userId = $cuti->user_id;
                    $data_karyawan = DataKaryawan::with('unit_kerjas')->where('user_id', $userId)->first();
                    if (!$data_karyawan) {
                        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
                    }

                    // ini untuk karyawan non shifts dan shift
                    $jenis_karyawan = $data_karyawan->unit_kerjas->jenis_karyawan ?? null;
                    if ($jenis_karyawan === null) {
                        Log::error("| Cuti | - Get Jenis karyawan tidak ditemukan untuk data karyawan {$data_karyawan->id}");
                    }

                    if ($jenis_karyawan === 0) {
                        $this->deletePresensi($userId, $tglFrom, $tglTo);
                        $this->deleteLembur($userId, $cuti);
                        $this->deleteIzin($userId, $tglFrom, $tglTo, $cuti);
                    } else if ($jenis_karyawan === 1) {
                        $this->deletePresensiJadwalIzin($userId, $tglFrom, $tglTo, $cuti);
                    }

                    $this->createNotifikasiCutiTahap2($cuti, 'Disetujui');

                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 untuk Cuti '{$cuti->tipe_cutis->nama}' telah disetujui."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Cuti '{$cuti->tipe_cutis->nama}' tidak dalam status untuk disetujui pada tahap 1."), Response::HTTP_BAD_REQUEST);
                }
            } elseif ($request->has('verifikasi_kedua_ditolak') && $request->verifikasi_kedua_ditolak == 1) {
                if ($status_cuti_id == 2) {
                    $cuti->status_cuti_id = 5;
                    $cuti->verifikator_2 = $verifikatorId;
                    $cuti->alasan = $request->input('alasan');
                    $cuti->save();

                    $this->createNotifikasiCutiTahap2($cuti, 'Ditolak');

                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 untuk Cuti '{$cuti->tipe_cutis->nama}' telah ditolak."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Cuti '{$cuti->tipe_cutis->nama}' tidak dalam status untuk ditolak pada tahap 1."), Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat verif 2 data cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function deletePresensiJadwalIzin($userId, $tglFrom, $tglTo, $cuti)
    {
        $this->deletePresensi($userId, $tglFrom, $tglTo);
        $this->deleteLembur($userId, $cuti);
        $this->deleteIzin($userId, $tglFrom, $tglTo, $cuti);

        $jadwalConflicts = Jadwal::where('user_id', $userId)
            ->where(function ($query) use ($tglFrom, $tglTo) {
                $query->whereBetween('tgl_mulai', [$tglFrom, $tglTo])
                    ->orWhereBetween('tgl_selesai', [$tglFrom, $tglTo])
                    ->orWhere(function ($query) use ($tglFrom, $tglTo) {
                        $query->where('tgl_mulai', '<=', $tglFrom)
                            ->where('tgl_selesai', '>=', $tglTo);
                    });
            })->get();
        foreach ($jadwalConflicts as $jadwal) {
            Lembur::where('jadwal_id', $jadwal->id)->delete();

            $tukarJadwal = TukarJadwal::where('jadwal_pengajuan', $jadwal->id)
                ->orWhere('jadwal_ditukar', $jadwal->id)
                ->first();
            if ($tukarJadwal) {
                $this->returnTukarJadwal($tukarJadwal);
            }

            $jadwal->delete();
        }
    }

    private function deletePresensi($userId, $tglFrom, $tglTo)
    {
        $presensiIds = Presensi::where('user_id', $userId)
            ->whereBetween(DB::raw("DATE(jam_masuk)"), [$tglFrom, $tglTo])
            ->pluck('id');
        if ($presensiIds->isNotEmpty()) {
            Presensi::whereIn('id', $presensiIds)->delete();
            Log::info("| Cuti | - Presensi dihapus untuk user_id {$userId} pada rentang {$tglFrom} - {$tglTo}");
        }
    }

    private function deleteLembur($userId, $cuti)
    {
        $lemburIds = Lembur::where('user_id', $userId)
            ->whereBetween('tgl_pengajuan', [$cuti->tgl_from, $cuti->tgl_to])
            ->pluck('id');
        if ($lemburIds->isNotEmpty()) {
            Log::info("| Cuti | - Lembur dihapus untuk user_id {$userId} pada rentang {$cuti->tgl_from} - {$cuti->tgl_to}");
            Lembur::whereIn('id', $lemburIds)->delete();
        }
    }

    private function deleteIzin($userId, $tglFrom, $tglTo, $cuti)
    {
        $izinIds = RiwayatIzin::where('user_id', $userId)
            ->whereBetween(DB::raw("DATE(tgl_izin)"), [$tglFrom, $tglTo])
            ->pluck('id');
        if ($izinIds->isNotEmpty()) {
            Log::info("| Cuti | - Izin dihapus untuk user_id {$userId} pada rentang {$cuti->tgl_from} - {$cuti->tgl_to}");
            RiwayatIzin::whereIn('id', $izinIds)->delete();
        }
    }

    // New Features
    // (1) - Get id Presensi, Jadwal, Izin, Lembur
    private function getPresensiIds($user_id_cuti, $tgl_from_cuti, $tgl_to_cuti)
    {
        $tgl_from = Carbon::createFromFormat('d-m-Y', $tgl_from_cuti)->format('Y-m-d');
        $tgl_to = Carbon::createFromFormat('d-m-Y', $tgl_to_cuti)->format('Y-m-d');

        return json_encode(
            Presensi::where('user_id', $user_id_cuti)
                ->whereBetween(DB::raw("DATE(jam_masuk)"), [$tgl_from, $tgl_to])
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray()
        );
    }

    private function getJadwalIds($user_id_cuti, $tgl_from_cuti, $tgl_to_cuti)
    {
        $tgl_from = Carbon::createFromFormat('d-m-Y', $tgl_from_cuti)->format('Y-m-d');
        $tgl_to = Carbon::createFromFormat('d-m-Y', $tgl_to_cuti)->format('Y-m-d');

        return json_encode(
            Jadwal::where('user_id', $user_id_cuti)
                ->where(function ($query) use ($tgl_from, $tgl_to) {
                    $query->where('tgl_mulai', '<=', $tgl_to)
                        ->where('tgl_selesai', '>=', $tgl_from);
                })
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray()
        );
    }

    private function getIzinIds($user_id_cuti, $tgl_from_cuti, $tgl_to_cuti)
    {
        $tgl_from = Carbon::createFromFormat('d-m-Y', $tgl_from_cuti)->format('Y-m-d');
        $tgl_to = Carbon::createFromFormat('d-m-Y', $tgl_to_cuti)->format('Y-m-d');

        return json_encode(
            RiwayatIzin::where('user_id', $user_id_cuti)
                ->whereBetween(DB::raw("DATE(tgl_izin)"), [$tgl_from, $tgl_to])
                ->where('status_izin_id', 2)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray()
        );
    }

    private function getLemburIds($user_id_cuti, $tgl_from_cuti, $tgl_to_cuti)
    {
        return json_encode(
            Lembur::where('user_id', $user_id_cuti)
                ->whereBetween('tgl_pengajuan', [$tgl_from_cuti, $tgl_to_cuti]) // Karena tgl_pengajuan sudah dalam format d-m-Y
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray()
        );
    }

    // (2) - Restore Presensi, Jadwal, Izin, Lembur
    private function restoreEntities($model, $ids)
    {
        if (!empty($ids)) {
            $model::whereIn('id', $ids)->restore();
        }
    }

    private function returnTukarJadwal($tukarJadwal)
    {
        // Kembalikan user_id dari jadwal_pengajuan ke user_pengajuan
        $jadwalPengajuan = Jadwal::find($tukarJadwal->jadwal_pengajuan);
        if ($jadwalPengajuan) {
            $jadwalPengajuan->user_id = $tukarJadwal->user_pengajuan;
            $jadwalPengajuan->save();
            $this->createNotifikasiJadwalKembali($jadwalPengajuan->user_id, $jadwalPengajuan->id);
        }

        // Kembalikan user_id dari jadwal_ditukar ke user_ditukar
        $jadwalDitukar = Jadwal::find($tukarJadwal->jadwal_ditukar);
        if ($jadwalDitukar) {
            $jadwalDitukar->user_id = $tukarJadwal->user_ditukar;
            $jadwalDitukar->save();
            $this->createNotifikasiJadwalKembali($jadwalDitukar->user_id, $jadwalDitukar->id);
        }

        // Hapus Tukar Jadwal
        $tukarJadwal->delete();
    }

    private function createNotifikasiCutiTahap1($cuti, $status)
    {
        try {
            $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
            $penerima_notif = $cuti->users->nama;
            $konversiTgl = Carbon::parse(RandomHelper::convertToDateString($cuti->tgl_mulai))->locale('id')->isoFormat('D MMMM YYYY');

            $messageForUser = "Pengajuan cuti '{$cuti->tipe_cutis->nama}' pada tanggal '{$konversiTgl}' telah '{$statusText}' tahap 1.";

            $messageForSuperAdmin = "Notifikasi untuk Super Admin: Pengajuan cuti '{$cuti->tipe_cutis->nama}' dari '{$penerima_notif}' pada tanggal '{$konversiTgl}' telah '{$statusText}' tahap 1.";

            // Daftar userId yang akan menerima notifikasi
            $userIds = [$cuti->user_id, 1];

            foreach ($userIds as $userId) {
                // Tentukan pesan berdasarkan user
                $message = $userId === 1 ? $messageForSuperAdmin : $messageForUser;

                // Buat notifikasi untuk user terkait atau Super Admin
                Notifikasi::create([
                    'kategori_notifikasi_id' => 1,
                    'user_id' => $userId,
                    'message' => $message,
                    'is_read' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat notifikasi tahap 1 data cuti karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiCutiTahap2($cuti, $status)
    {
        try {
            $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
            $penerima_notif = $cuti->users->nama;
            $konversiTgl = Carbon::parse(RandomHelper::convertToDateString($cuti->tgl_mulai))->locale('id')->isoFormat('D MMMM YYYY');

            $messageForUser = "Pengajuan cuti '{$cuti->tipe_cutis->nama}' pada tanggal '{$konversiTgl}' telah '{$statusText}' tahap 2.";
            $messageForSuperAdmin = "Notifikasi untuk Super Admin: Pengajuan cuti '{$cuti->tipe_cutis->nama}' dari '{$penerima_notif}' pada tanggal '{$konversiTgl}' telah '{$statusText}' tahap 2.";

            $userIds = [$cuti->user_id, 1];
            foreach ($userIds as $userId) {
                $message = $userId === 1 ? $messageForSuperAdmin : $messageForUser;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 1,
                    'user_id' => $userId,
                    'message' => $message,
                    'is_read' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('| Cuti | - Error saat membuat notifikasi tahap 2: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiJadwalKembali($userId, $jadwalId)
    {
        try {
            $jadwal = Jadwal::find($jadwalId);
            if ($jadwal) {
                $user = User::find($userId);
                $tglMulai = Carbon::createFromFormat('Y-m-d', $jadwal->tgl_mulai)->locale('id')->isoFormat('D MMMM YYYY');
                $tglSelesai = Carbon::createFromFormat('Y-m-d', $jadwal->tgl_selesai)->locale('id')->isoFormat('D MMMM YYYY');

                // Pesan untuk karyawan terkait
                $messageForUser = "Jadwal Anda dari tanggal {$tglMulai} hingga {$tglSelesai} telah dikembalikan ke status semula.";

                // Pesan untuk Super Admin
                $messageForSuperAdmin = "Notifikasi untuk Super Admin: Jadwal '{$user->nama}' dari tanggal {$tglMulai} hingga {$tglSelesai} telah dikembalikan ke status semula.";

                $userIds = [$userId, 1];
                foreach ($userIds as $userId) {
                    $message = $userId === 1 ? $messageForSuperAdmin : $messageForUser;
                    Notifikasi::create([
                        'kategori_notifikasi_id' => 2,
                        'user_id' => $userId,
                        'message' => $message,
                        'is_read' => false,
                        'created_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat membuat notifikasi pengembalian jadwal: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
