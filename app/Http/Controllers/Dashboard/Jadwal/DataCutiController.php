<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\Jadwal;
use App\Models\Lembur;
use App\Models\TipeCuti;
use App\Models\Notifikasi;
use App\Models\TukarJadwal;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\CutiJadwalExport;
use App\Http\Requests\StoreCutiJadwalRequest;
use App\Http\Requests\UpdateCutiJadwalRequest;
use App\Http\Resources\Dashboard\Jadwal\CutiJadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataCutiController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

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
            if (is_array($masaKerja)) {
                $cuti->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $cuti->whereHas('users.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
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
                $convertedDates = array_map([RandomHelper::class, 'convertToDateString'], $tglMasuk);
                $cuti->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $cuti->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
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
            return [
                'id' => $dataCuti->id,
                'user' => [
                    'id' => $dataCuti->users->id,
                    'nama' => $dataCuti->users->nama,
                    'username' => $dataCuti->users->username,
                    'email_verified_at' => $dataCuti->users->email_verified_at,
                    'data_karyawan_id' => $dataCuti->users->data_karyawan_id,
                    'foto_profil' => $dataCuti->users->foto_profil,
                    'data_completion_step' => $dataCuti->users->data_completion_step,
                    'status_aktif' => $dataCuti->users->status_aktif,
                    'created_at' => $dataCuti->users->created_at,
                    'updated_at' => $dataCuti->users->updated_at
                ],
                'unit_kerja' => $dataCuti->users->data_karyawans->unit_kerjas,
                'tipe_cuti' => $dataCuti->tipe_cutis,
                'tgl_from' => $dataCuti->tgl_from,
                'tgl_to' => $dataCuti->tgl_to,
                'catatan' => $dataCuti->catatan,
                'durasi' => $dataCuti->durasi,
                'status_cuti' => $dataCuti->status_cutis,
                'alasan' => $cuti->alasan ?? null,
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
    }

    public function store(StoreCutiJadwalRequest $request)
    {
        if (!Gate::allows('create cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Cek cuti sama
        $cutiExist = Cuti::where('user_id', $data['user_id'])
            ->where('tgl_from', Carbon::createFromFormat('d-m-Y', $data['tgl_from'])->format('d-m-Y'))
            ->where('tgl_to', Carbon::createFromFormat('d-m-Y', $data['tgl_to'])->format('d-m-Y'))
            ->exists();
        // dd($cutiExist);

        if ($cutiExist) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Karyawan tersebut sudah memiliki cuti yang diajukan pada tanggal '{$data['tgl_from']}'."), Response::HTTP_BAD_REQUEST);
        }

        // Mengambil informasi tipe cuti berdasarkan tipe_cuti_id
        $tipeCuti = TipeCuti::find($data['tipe_cuti_id']);
        if (!$tipeCuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tipe cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

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
        $currentYear = Carbon::now()->year;
        $cutiRecords = Cuti::where('user_id', $data['user_id'])
            ->where('tipe_cuti_id', $data['tipe_cuti_id'])
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

        // cek bentrok dengan jadwal karyawan
        // $jadwalConflict = Jadwal::where('user_id', $data['user_id'])
        //     ->where(function ($query) use ($tglFrom, $tglTo) {
        //         $query->whereBetween(DB::raw("DATE_FORMAT(tgl_mulai, '%Y-%m-%d')"), [$tglFrom, $tglTo])
        //             ->orWhereBetween(DB::raw("DATE_FORMAT(tgl_selesai, '%Y-%m-%d')"), [$tglFrom, $tglTo])
        //             ->orWhere(function ($query) use ($tglFrom, $tglTo) {
        //                 $query->where(DB::raw("DATE_FORMAT(tgl_mulai, '%Y-%m-%d')"), '<=', $tglFrom)
        //                     ->where(DB::raw("DATE_FORMAT(tgl_selesai, '%Y-%m-%d')"), '>=', $tglTo);
        //             });
        //     })
        //     ->first();

        // if ($jadwalConflict) {
        //     $jadwalFrom = Carbon::parse($jadwalConflict->tgl_mulai)->format('d-m-Y');
        //     $jadwalTo = Carbon::parse($jadwalConflict->tgl_selesai)->format('d-m-Y');
        //     return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tidak dapat mengajukan cuti, karena karyawan '{$jadwalConflict->users->nama}' memiliki jadwal kerja pada rentang tanggal {$jadwalFrom} hingga {$jadwalTo}."), Response::HTTP_BAD_REQUEST);
        // }

        if (Gate::allows('verifikasi2 cutiKaryawan')) {
            $statusCutiId = 4;

            // Step 1: Cek jadwal kerja shift, jika ada yang cuti mendadak dan jika masih ada jadwal
            $jadwalConflicts = Jadwal::where('user_id', $data['user_id'])
                ->where(function ($query) use ($tglFrom, $tglTo) {
                    $query->whereBetween('tgl_mulai', [$tglFrom, $tglTo])
                        ->orWhereBetween('tgl_selesai', [$tglFrom, $tglTo])
                        ->orWhere(function ($query) use ($tglFrom, $tglTo) {
                            $query->where('tgl_mulai', '<=', $tglFrom)
                                ->where('tgl_selesai', '>=', $tglTo);
                        });
                })->get();

            if ($jadwalConflicts->isNotEmpty()) {
                foreach ($jadwalConflicts as $jadwal) {
                    $tukarJadwal = TukarJadwal::where('jadwal_pengajuan', $jadwal->id)
                        ->orWhere('jadwal_ditukar', $jadwal->id)
                        ->first();

                    if ($tukarJadwal) {
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

                        // Hapus tukar jadwal setelah dikembalikan
                        $tukarJadwal->delete();
                    }

                    // Hapus lembur
                    Lembur::where('jadwal_id', $jadwal->id)->delete();

                    // Hapus jadwal
                    Jadwal::where('user_id', $data['user_id'])->delete();
                }
            }

            $data['verifikator_1'] = Auth::id();
            $data['verifikator_2'] = Auth::id();
        } elseif (Gate::allows('verifikasi1 cutiKaryawan')) {
            $statusCutiId = 2;
            $data['verifikator_1'] = Auth::id();
        } else {
            $statusCutiId = 1;
        }

        // Menambahkan durasi ke data sebelum menyimpan
        $data['durasi'] = $durasi;
        $data['status_cuti_id'] = $statusCutiId;
        $dataCuti = Cuti::create($data);

        // Setelah pembuatan cuti, cari cuti yang baru dibuat dan periksa cuti_administratif
        $cutiTerbaru = Cuti::where('user_id', $data['user_id'])->latest()->first();

        if ($cutiTerbaru && !$cutiTerbaru->tipe_cutis->cuti_administratif) {
            DB::table('data_karyawans')
                ->where('user_id', $data['user_id'])
                ->update(['status_reward_presensi' => false]);
        }

        $message = "Data cuti karyawan '{$dataCuti->users->nama}' berhasil dibuat untuk tipe cuti '{$dataCuti->tipe_cutis->nama}' dengan durasi {$dataCuti->durasi} hari.";

        $konversiNotif_tgl_from = Carbon::parse($dataCuti->tgl_from)->locale('id')->isoFormat('D MMMM YYYY');
        $konversiNotif_tgl_to = Carbon::parse($dataCuti->tgl_to)->locale('id')->isoFormat('D MMMM YYYY');

        // Menyimpan notifikasi ke tabel notifikasis
        Notifikasi::create([
            'kategori_notifikasi_id' => 1,
            'user_id' => $data['user_id'],
            'message' => "{$dataCuti->users->nama}, anda mendapatkan cuti {$dataCuti->tipe_cutis->nama} dengan durasi {$dataCuti->durasi} hari yang dimulai pada {$konversiNotif_tgl_from} s/d {$konversiNotif_tgl_to}.",
            'is_read' => false,
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return response()->json(new CutiJadwalResource(Response::HTTP_OK, $message, $dataCuti), Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataCuti = Cuti::with(['users.data_karyawans.unit_kerjas', 'users.cutis', 'tipe_cutis', 'status_cutis'])->find($id);
        if (!$dataCuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $listCuti = $dataCuti->users->cutis->map(function ($cuti) {
            return [
                'id' => $cuti->id,
                'tipe_cuti' => $cuti->tipe_cutis,
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
                    'foto_profil' => $cuti->verifikator_1_cutis->foto_profil,
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
                    'foto_profil' => $cuti->verifikator_2_cutis->foto_profil,
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
                'foto_profil' => $dataCuti->users->foto_profil,
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
    }

    public function update(UpdateCutiJadwalRequest $request, $id)
    {
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
    }

    public function exportJadwalCuti()
    {
        if (!Gate::allows('export cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataCuti = Cuti::all();
        if ($dataCuti->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data cuti karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new CutiJadwalExport(), 'cuti-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifikasiTahap1(Request $request, $cutiId)
    {
        if (!Gate::allows('verifikasi1 cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari cuti berdasarkan ID
        $cuti = Cuti::find($cutiId);

        if (!$cuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $status_cuti_id = $cuti->status_cuti_id;

        if ($request->has('verifikasi_pertama_disetujui') && $request->verifikasi_pertama_disetujui == 1) {
            if ($status_cuti_id == 1) {
                $cuti->status_cuti_id = 2; // Update status ke tahap 1 disetujui
                $cuti->verifikator_1 = Auth::id();
                $cuti->alasan = null;
                $cuti->save();

                // Buat dan simpan notifikasi
                $this->createNotifikasiCutiTahap1($cuti, 'Disetujui');

                // Cek apakah cuti_administratif pada tipe_cutis adalah true atau false
                if (!$cuti->tipe_cutis->cuti_administratif) {
                    // Jika cuti_administratif = false, lakukan update status_reward_presensi menjadi false
                    $user_id = $cuti->user_id;
                    $data_karyawan_id = DB::table('data_karyawans')
                        ->where('user_id', $user_id)
                        ->value('id');

                    DB::table('data_karyawans')
                        ->where('id', $data_karyawan_id)
                        ->update(['status_reward_presensi' => false]);
                }

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 untuk Cuti '{$cuti->tipe_cutis->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Cuti '{$cuti->tipe_cutis->nama}' tidak dalam status untuk disetujui pada tahap 1."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
            if ($status_cuti_id == 1) {
                $cuti->status_cuti_id = 3; // Update status ke tahap 1 ditolak
                $cuti->verifikator_1 = Auth::id();
                $cuti->alasan = $request->input('alasan', null);
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
    }

    public function verifikasiTahap2(Request $request, $cutiId)
    {
        if (!Gate::allows('verifikasi2 cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari cuti berdasarkan ID
        $cuti = Cuti::find($cutiId);

        if (!$cuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $status_cuti_id = $cuti->status_cuti_id;

        if ($request->has('verifikasi_kedua_disetujui') && $request->verifikasi_kedua_disetujui == 1) {
            if ($status_cuti_id == 2) {
                $cuti->status_cuti_id = 4;
                $cuti->verifikator_2 = Auth::id();
                $cuti->alasan = null;
                $cuti->save();

                // Step 1: Cek Hapus jadwal kerja shift jika ada yang cuti mendadak dan jika masih ada jadwal
                $tglFrom = Carbon::createFromFormat('d-m-Y', $cuti->tgl_from)->format('Y-m-d');
                $tglTo = Carbon::createFromFormat('d-m-Y', $cuti->tgl_to)->format('Y-m-d');

                $jadwalConflicts = Jadwal::where('user_id', $cuti->user_id)
                    ->where(function ($query) use ($tglFrom, $tglTo) {
                        $query->whereBetween('tgl_mulai', [$tglFrom, $tglTo])
                            ->orWhereBetween('tgl_selesai', [$tglFrom, $tglTo])
                            ->orWhere(function ($query) use ($tglFrom, $tglTo) {
                                $query->where('tgl_mulai', '<=', $tglFrom)
                                    ->where('tgl_selesai', '>=', $tglTo);
                            });
                    })->get();

                if ($jadwalConflicts->isNotEmpty()) {
                    foreach ($jadwalConflicts as $jadwal) {
                        $tukarJadwal = TukarJadwal::where('jadwal_pengajuan', $jadwal->id)
                            ->orWhere('jadwal_ditukar', $jadwal->id)
                            ->first();

                        if ($tukarJadwal) {
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

                            // Hapus tukar jadwal setelah dikembalikan
                            $tukarJadwal->delete();
                        }

                        // Hapus lembur
                        Lembur::where('jadwal_id', $jadwal->id)->delete();

                        // Hapus jadwal
                        Jadwal::where('user_id', $cuti->user_id)->delete();
                    }
                }

                $this->createNotifikasiCutiTahap2($cuti, 'Disetujui');

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 untuk Cuti '{$cuti->tipe_cutis->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Cuti '{$cuti->tipe_cutis->nama}' tidak dalam status untuk disetujui pada tahap 1."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_kedua_ditolak') && $request->verifikasi_kedua_ditolak == 1) {
            if ($status_cuti_id == 2) {
                $cuti->status_cuti_id = 5;
                $cuti->verifikator_2 = Auth::id();
                $cuti->alasan = $request->input('alasan', null);
                $cuti->save();

                $this->createNotifikasiCutiTahap2($cuti, 'Ditolak');

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 untuk Cuti '{$cuti->tipe_cutis->nama}' telah ditolak."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Cuti '{$cuti->tipe_cutis->nama}' tidak dalam status untuk ditolak pada tahap 1."), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
        }
    }

    private function createNotifikasiCutiTahap1($cuti, $status)
    {
        $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
        $verifikator = Auth::user()->nama;
        $konversiTgl = Carbon::parse(RandomHelper::convertToDateString($cuti->tgl_mulai))->locale('id')->isoFormat('D MMMM YYYY');
        $message = "Pengajuan cuti '{$cuti->tipe_cutis->nama}' Anda pada tanggal '{$konversiTgl}' telah '{$statusText}' tahap 1 oleh '{$verifikator}'.";

        // Buat notifikasi untuk user yang mengajukan cuti
        Notifikasi::create([
            'kategori_notifikasi_id' => 1,
            'user_id' => $cuti->user_id, // Penerima notifikasi
            'message' => $message,
            'is_read' => false,
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);
    }

    private function createNotifikasiCutiTahap2($cuti, $status)
    {
        $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
        $verifikator = Auth::user()->nama;
        $konversiTgl = Carbon::parse(RandomHelper::convertToDateString($cuti->tgl_mulai))->locale('id')->isoFormat('D MMMM YYYY');
        $message = "Pengajuan cuti '{$cuti->tipe_cutis->nama}' Anda pada tanggal '{$konversiTgl}' telah '{$statusText}' tahap 2 oleh '{$verifikator}'.";

        // Buat notifikasi untuk user yang mengajukan cuti
        Notifikasi::create([
            'kategori_notifikasi_id' => 1,
            'user_id' => $cuti->user_id, // Penerima notifikasi
            'message' => $message,
            'is_read' => false,
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);
    }

    private function createNotifikasiJadwalKembali($userId, $jadwalId)
    {
        $jadwal = Jadwal::find($jadwalId);

        if ($jadwal) {
            $tglMulai = Carbon::createFromFormat('Y-m-d', $jadwal->tgl_mulai)->locale('id')->isoFormat('D MMMM YYYY');
            $tglSelesai = Carbon::createFromFormat('Y-m-d', $jadwal->tgl_selesai)->locale('id')->isoFormat('D MMMM YYYY');
            $message = "Jadwal anda dari tanggal {$tglMulai} hingga {$tglSelesai} telah dikembalikan ke status semula.";

            Notifikasi::create([
                'kategori_notifikasi_id' => 2,
                'user_id' => $userId,
                'message' => $message,
                'is_read' => false,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }
}
