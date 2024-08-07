<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\TukarJadwal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\TukarJadwalExport;
use App\Helpers\RandomHelper;
use App\Http\Requests\StoreTukarJadwalRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataTukarJadwalController extends Controller
{
    // ambil jadwal dari user pengajuan
    public function getJadwalPengajuan($userId)
    {
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = User::where('id', $userId)->where('nama', '!=', 'Super Admin')
            ->firstOrFail();

        $jadwal = Jadwal::with('shifts')->where('user_id', $userId)->get();

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
    }

    // ambil user ditukar dari jadwal pengajuan
    public function getUserDitukar($jadwalId)
    {
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jadwal = Jadwal::findOrFail($jadwalId);
        $unitKerjaId = $jadwal->users->data_karyawans->unit_kerjas->id;
        $tglMulai = Carbon::parse($jadwal->tgl_mulai)->format('Y-m-d');
        $tglSelesai = Carbon::parse($jadwal->tgl_selesai)->format('Y-m-d');

        $users = User::whereHas('jadwals', function ($query) use ($jadwal, $tglMulai, $tglSelesai) {
            $query->where('shift_id', '!=', $jadwal->shift_id)
                ->whereBetween('tgl_mulai', [$tglMulai, $tglSelesai])
                ->whereBetween('tgl_selesai', [$tglMulai, $tglSelesai]);
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
            'data' => $users
        ]);
    }

    // ambil jadwal dari user ditukar
    public function getJadwalDitukar($userId)
    {
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = User::where('id', $userId)->where('nama', '!=', 'Super Admin')
            ->firstOrFail();

        $jadwal = Jadwal::with('shifts')->where('user_id', $userId)->get();

        // Ambil range tanggal untuk jadwal
        $start_date = $jadwal->min('tgl_mulai');
        $end_date = $jadwal->max('tgl_selesai');
        $date_range = $this->generateDateRange($start_date, $end_date);

        $user_schedule_array = $this->formatSchedules($jadwal, $date_range);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detai jadwall dan karyawan ditukar berhasil ditampilkan.",
            'data' => [
                'user' => $user,
                'list_jadwal' => $user_schedule_array
            ]
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $tukarJadwal = TukarJadwal::query();

        // Ambil semua filter dari request body
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
            if (is_array($masaKerja)) {
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
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
                $convertedDates = array_map([RandomHelper::class, 'convertToDateString'], $tglMasuk);
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $tukarJadwal->whereHas('user_pengajuans.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
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
            $tukarJadwal->whereHas('user_pengajuans.data_karyawans.pendidikan_terakhir', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
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

        $formattedData = $dataTukarJadwal->map(function ($tukar_jadwal) {
            return [
                'id' => $tukar_jadwal->id,
                'tanggal_pengajuan' => $tukar_jadwal->created_at,
                'status_penukaran' => $tukar_jadwal->status_tukar_jadwals,
                'kategori_penukaran' => $tukar_jadwal->kategori_tukar_jadwals,
                'unit_kerja' => $tukar_jadwal->user_pengajuans->data_karyawans->unit_kerjas,
                'karyawan_pengajuan' => [
                    'id' => $tukar_jadwal->user_pengajuans->id,
                    'nama' => $tukar_jadwal->user_pengajuans->nama,
                    'email_verified_at' => $tukar_jadwal->user_pengajuans->email_verified_at,
                    'data_karyawan_id' => $tukar_jadwal->user_pengajuans->data_karyawan_id,
                    'foto_profil' => $tukar_jadwal->user_pengajuans->foto_profil,
                    'data_completion_step' => $tukar_jadwal->user_pengajuans->data_completion_step,
                    'status_aktif' => $tukar_jadwal->user_pengajuans->status_aktif,
                    'created_at' => $tukar_jadwal->user_pengajuans->created_at,
                    'updated_at' => $tukar_jadwal->user_pengajuans->updated_at
                ],
                'karyawan_ditukar' => [
                    'id' => $tukar_jadwal->user_ditukars->id,
                    'nama' => $tukar_jadwal->user_ditukars->nama,
                    'email_verified_at' => $tukar_jadwal->user_ditukars->email_verified_at,
                    'data_karyawan_id' => $tukar_jadwal->user_ditukars->data_karyawan_id,
                    'foto_profil' => $tukar_jadwal->user_ditukars->foto_profil,
                    'data_completion_step' => $tukar_jadwal->user_ditukars->data_completion_step,
                    'status_aktif' => $tukar_jadwal->user_ditukars->status_aktif,
                    'created_at' => $tukar_jadwal->user_ditukars->created_at,
                    'updated_at' => $tukar_jadwal->user_ditukars->updated_at
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
    }

    // super fix super jos
    public function store(StoreTukarJadwalRequest $request)
    {
        if (!Gate::allows('create tukarJadwal')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $userPengajuan = User::findOrFail($data['user_pengajuan']);
        $userDitukar = User::findOrFail($data['user_ditukar']);
        $jadwalPengajuan = Jadwal::findOrFail($data['jadwal_pengajuan']);
        $jadwalDitukar = Jadwal::findOrFail($data['jadwal_ditukar']);
        
        // Konversi tanggal dari string untuk validasi
        $tglMulaiPengajuan = RandomHelper::convertToDateString($jadwalPengajuan->tgl_mulai);
        $tglMulaiDitukar = RandomHelper::convertToDateString($jadwalDitukar->tgl_mulai);

        // Verifikasi unit kerja
        if ($userPengajuan->data_karyawans->unit_kerjas->id !== $userDitukar->data_karyawans->unit_kerjas->id) {
            return response()->json(['message' => 'Karyawan harus berada di unit kerja yang sama untuk menukar jadwal.'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_null($jadwalPengajuan->shift_id) && !is_null($jadwalDitukar->shift_id)) {
            // Verifikasi tanggal
            if ($tglMulaiPengajuan !== $tglMulaiDitukar) {
                return response()->json(['message' => 'Jadwal harus pada tanggal yang sama untuk menukar jadwal.'], Response::HTTP_BAD_REQUEST);
            }

            // Tukar shift dengan shift
            // Tukar user_id
            $tempUserId = $jadwalPengajuan->user_id;
            $jadwalPengajuan->user_id = $jadwalDitukar->user_id;
            $jadwalDitukar->user_id = $tempUserId;

            $jadwalPengajuan->save();
            $jadwalDitukar->save();

            // Simpan permintaan tukar jadwal
            $tukarJadwal = new TukarJadwal([
                'user_pengajuan' => $userPengajuan->id,
                'jadwal_pengajuan' => $jadwalPengajuan->id,
                'user_ditukar' => $userDitukar->id,
                'jadwal_ditukar' => $jadwalDitukar->id,
                'status_penukaran_id' => 2, // Disetujui
                'kategori_penukaran_id' => 1, // Tukar Shift
            ]);
            $tukarJadwal->save();
        } else if (is_null($jadwalPengajuan->shift_id) && is_null($jadwalDitukar->shift_id)) {
            // Tukar libur dengan libur

            // Ambil jadwal user pengajuan pada tanggal libur user ditukar
            $jadwalKerjaPengajuan = Jadwal::where('user_id', $userPengajuan->id)
                ->where('tgl_mulai', $tglMulaiDitukar)
                ->whereNotNull('shift_id')
                ->first();

            if (!$jadwalKerjaPengajuan) {
                return response()->json(['message' => 'Jadwal kerja user pengajuan tidak ditemukan pada tanggal yang diminta.'], Response::HTTP_BAD_REQUEST);
            }

            // Tukar user_id pada jadwal libur
            $tempUserId = $jadwalPengajuan->user_id;
            $jadwalPengajuan->user_id = $jadwalDitukar->user_id;
            $jadwalDitukar->user_id = $tempUserId;

            // Simpan perubahan jadwal libur
            $jadwalPengajuan->save();
            $jadwalDitukar->save();

            // Tukar user_id pada jadwal kerja pengajuan
            $jadwalKerjaPengajuan->user_id = $userDitukar->id;
            $jadwalKerjaPengajuan->save();

            // Tukar user_id pada jadwal libur yang lain
            $jadwalLiburPengajuan = Jadwal::findOrFail($data['jadwal_pengajuan']);
            $jadwalLiburPengajuan->user_id = $userDitukar->id;
            $jadwalLiburPengajuan->save();

            // Simpan permintaan tukar jadwal
            $tukarJadwal = new TukarJadwal([
                'user_pengajuan' => $userPengajuan->id,
                'jadwal_pengajuan' => $jadwalPengajuan->id,
                'user_ditukar' => $userDitukar->id,
                'jadwal_ditukar' => $jadwalDitukar->id,
                'status_penukaran_id' => 2, // Disetujui
                'kategori_penukaran_id' => 2, // Tukar Libur
            ]);
            $tukarJadwal->save();
        } else {
            return response()->json(['message' => 'Tidak bisa menukar shift dengan libur atau sebaliknya.'], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data tukar jadwal karyawan berhasil ditambahkan.',
            'data' => [
                [
                    'user_pengajuan' => [
                        'id' => $tukarJadwal->id,
                        'user' => [
                            'id' => $userPengajuan->id,
                            'nama' => $userPengajuan->nama,
                            'email_verified_at' => $userPengajuan->email_verified_at,
                            'data_karyawan_id' => $userPengajuan->data_karyawan_id,
                            'foto_profil' => $userPengajuan->foto_profil,
                            'data_completion_step' => $userPengajuan->data_completion_step,
                            'status_aktif' => $userPengajuan->status_aktif,
                            'created_at' => $userPengajuan->created_at,
                            'updated_at' => $userPengajuan->updated_at
                        ],
                        'jadwal' => $jadwalPengajuan,
                        'status' => $tukarJadwal->status_tukar_jadwals,
                        'kategori' => $tukarJadwal->kategori_tukar_jadwals,
                    ]
                ],
                [
                    'user_ditukar' => [
                        'id' => $tukarJadwal->id,
                        'user' => [
                            'id' => $userDitukar->id,
                            'nama' => $userDitukar->nama,
                            'email_verified_at' => $userDitukar->email_verified_at,
                            'data_karyawan_id' => $userDitukar->data_karyawan_id,
                            'foto_profil' => $userDitukar->foto_profil,
                            'data_completion_step' => $userDitukar->data_completion_step,
                            'status_aktif' => $userDitukar->status_aktif,
                            'created_at' => $userDitukar->created_at,
                            'updated_at' => $userDitukar->updated_at
                        ],
                        'jadwal' => $jadwalDitukar,
                        'status' => $tukarJadwal->status_tukar_jadwals,
                        'kategori' => $tukarJadwal->kategori_tukar_jadwals,
                    ]
                ]
            ]
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
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

        $formattedData = [
            'id' => $tukarJadwal->id,
            'unit_kerja' => $tukarJadwal->user_pengajuans->data_karyawans->unit_kerjas,
            'user_pengajuan' => [
                'user' => [
                    'id' => $userPengajuan->id,
                    'nama' => $userPengajuan->nama,
                    'email_verified_at' => $userPengajuan->email_verified_at,
                    'data_karyawan_id' => $userPengajuan->data_karyawan_id,
                    'foto_profil' => $userPengajuan->foto_profil,
                    'data_completion_step' => $userPengajuan->data_completion_step,
                    'status_aktif' => $userPengajuan->status_aktif,
                    'created_at' => $userPengajuan->created_at,
                    'updated_at' => $userPengajuan->updated_at,
                ],
                'jadwal' => $jadwalPengajuan,
                'status' => $tukarJadwal->status_tukar_jadwals,
                'kategori' => $tukarJadwal->kategori_tukar_jadwals,
            ],
            'user_ditukar' => [
                'user' => [
                    'id' => $userDitukar->id,
                    'nama' => $userDitukar->nama,
                    'email_verified_at' => $userDitukar->email_verified_at,
                    'data_karyawan_id' => $userDitukar->data_karyawan_id,
                    'foto_profil' => $userDitukar->foto_profil,
                    'data_completion_step' => $userDitukar->data_completion_step,
                    'status_aktif' => $userDitukar->status_aktif,
                    'created_at' => $userDitukar->created_at,
                    'updated_at' => $userDitukar->updated_at,
                ],
                'jadwal' => $jadwalDitukar,
                'status' => $tukarJadwal->status_tukar_jadwals,
                'kategori' => $tukarJadwal->kategori_tukar_jadwals,
            ],
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail tukar jadwal karyawan '{$tukarJadwal->user_pengajuans->nama}' dan '{$tukarJadwal->user_ditukars->nama}' berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function exportJadwalTukar()
    {
        if (!Gate::allows('export tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new TukarJadwalExport(), 'jadwal-penukaran-jadwal.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jadwal lembur karyawan berhasil di download.'), Response::HTTP_OK);
    }

    private function generateDateRange($start_date, $end_date)
    {
        $dates = [];
        $current = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);

        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }

    private function formatSchedules($jadwal, $date_range)
    {
        $user_schedules_by_date = [];
        foreach ($jadwal as $schedule) {
            $current_date = Carbon::parse($schedule->tgl_mulai);
            while ($current_date->lte(Carbon::parse($schedule->tgl_selesai))) {
                $user_schedules_by_date[$current_date->format('Y-m-d')] = $schedule;
                $current_date->addDay();
            }
        }

        $user_schedule_array = [];
        foreach ($date_range as $date) {
            if (isset($user_schedules_by_date[$date])) {
                $schedule = $user_schedules_by_date[$date];
                $user_schedule_array[] = [
                    'id' => $schedule->id,
                    'tanggal' => $date,
                    'nama_shift' => $schedule->shifts->nama,
                    'jam_from' => $schedule->shifts->jam_from,
                    'jam_to' => $schedule->shifts->jam_to,
                ];
            } else {
                $user_schedule_array[] = null;
            }
        }

        return $user_schedule_array;
    }
}
