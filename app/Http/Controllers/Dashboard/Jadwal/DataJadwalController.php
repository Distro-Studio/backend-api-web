<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Exports\Jadwal\JadwalExport;
use App\Helpers\RandomHelper;
use App\Http\Controllers\Controller;
use App\Imports\Jadwal\JadwalImport;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreJadwalKaryawanRequest;
use App\Http\Requests\StoreJadwalShiftKaryawanRequest;
use App\Http\Requests\Excel_Import\ImportJadwalKaryawan;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataJadwalController extends Controller
{
    // Orisinil
    // public function index(Request $request)
    // {
    //     if (!Gate::allows('view jadwalKaryawan')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $jadwal = Jadwal::query();

    //     // Ambil semua filter dari request body
    //     $filters = $request->all();

    //     // Filter
    //     if (isset($filters['nama_unit'])) {
    //         $namaUnitKerja = $filters['nama_unit'];
    //         $jadwal->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
    //             if (is_array($namaUnitKerja)) {
    //                 $query->whereIn('id', $namaUnitKerja);
    //             } else {
    //                 $query->where('id', '=', $namaUnitKerja);
    //             }
    //         });
    //     }

    //     if (isset($filters['status_karyawan'])) {
    //         $statusKaryawan = $filters['status_karyawan'];
    //         $jadwal->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
    //             if (is_array($statusKaryawan)) {
    //                 $query->whereIn('id', $statusKaryawan);
    //             } else {
    //                 $query->where('id', '=', $statusKaryawan);
    //             }
    //         });
    //     }

    //     if (isset($filters['masa_kerja'])) {
    //         $masaKerja = $filters['masa_kerja'];
    //         if (is_array($masaKerja)) {
    //             $jadwal->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
    //                 foreach ($masaKerja as $masa) {
    //                     $query->orWhereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$masa]);
    //                 }
    //             });
    //         } else {
    //             $jadwal->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
    //                 $query->whereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$masaKerja]);
    //             });
    //         }
    //     }

    //     if (isset($filters['status_aktif'])) {
    //         $statusAktif = $filters['status_aktif'];
    //         $jadwal->whereHas('users', function ($query) use ($statusAktif) {
    //             if (is_array($statusAktif)) {
    //                 $query->whereIn('status_aktif', $statusAktif);
    //             } else {
    //                 $query->where('status_aktif', '=', $statusAktif);
    //             }
    //         });
    //     }

    //     if (isset($filters['tgl_masuk'])) {
    //         $tglMasuk = $filters['tgl_masuk'];
    //         if (is_array($tglMasuk)) {
    //             $jadwal->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
    //                 $query->whereIn('tgl_masuk', $tglMasuk);
    //             });
    //         } else {
    //             $tglMasuk = Carbon::parse($tglMasuk)->format('Y-m-d');
    //             $jadwal->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
    //                 $query->where('tgl_masuk', $tglMasuk);
    //             });
    //         }
    //     }

    //     // Search
    //     if (isset($filters['search'])) {
    //         $searchTerm = '%' . $filters['search'] . '%';
    //         $jadwal->where(function ($query) use ($searchTerm) {
    //             $query->whereHas('users', function ($query) use ($searchTerm) {
    //                 $query->where('nama', 'like', $searchTerm);
    //             })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
    //                 $query->where('nik', 'like', $searchTerm);
    //             });
    //         });
    //     }

    //     if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
    //         $start_date = Carbon::parse($request->input('tgl_mulai'));
    //         $end_date = Carbon::parse($request->input('tgl_selesai'));

    //         if ($end_date->diffInDays($start_date) > 28) {
    //             return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Rentang tanggal yang diberikan tidak boleh melebihi 28 hari dari tanggal mulai.'), Response::HTTP_BAD_REQUEST);
    //         }

    //         $date_range = $this->generateDateRange($start_date, $end_date);
    //     } else {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal jadwal mulai dan selesai tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
    //     }


    //     // Per page
    //     $limit = $request->input('limit', 10); // Default per page is 10
    //     $limit = is_numeric($limit) ? (int)$limit : 10; // Pastikan limit adalah integer

    //     // Fetch schedules
    //     $schedules = $jadwal->get()->groupBy('user_id');
    //     $result = [];
    //     foreach ($schedules as $user_id => $user_schedules) {
    //         $user = $user_schedules->first()->users;
    //         $user_schedule_array = [];
    //         $user_schedules_by_date = [];

    //         foreach ($user_schedules as $schedule) {
    //             $current_date = Carbon::parse($schedule->tgl_mulai);
    //             while ($current_date->lte(Carbon::parse($schedule->tgl_selesai))) {
    //                 $user_schedules_by_date[$current_date->format('Y-m-d')] = $schedule;
    //                 $current_date->addDay();
    //             }
    //         }

    //         foreach ($date_range as $date) {
    //             if (isset($user_schedules_by_date[$date])) {
    //                 $schedule = $user_schedules_by_date[$date];
    //                 $user_schedule_array[] = [
    //                     'id' => $schedule->shifts->id,
    //                     'tanggal' => $date,
    //                     'nama_shift' => $schedule->shifts->nama,
    //                     'jam_from' => $schedule->shifts->jam_from,
    //                     'jam_to' => $schedule->shifts->jam_to,
    //                 ];
    //             } else {
    //                 $user_schedule_array[] = null;
    //             }
    //         }

    //         $result[] = [
    //             'id' => $schedule->id,
    //             'user' => [
    //                 'id' => $user->id,
    //                 'nama' => $user->nama,
    //                 'email_verified_at' => $user->email_verified_at,
    //                 'data_karyawan_id' => $user->data_karyawan_id,
    //                 'foto_profil' => $user->foto_profil,
    //                 'data_completion_step' => $user->data_completion_step,
    //                 'status_aktif' => $user->status_aktif,
    //                 'created_at' => $user->created_at,
    //                 'updated_at' => $user->updated_at
    //             ],
    //             'unit_kerja' => optional($user->data_karyawans)->unit_kerjas,
    //             'list_jadwal' => $user_schedule_array,
    //         ];
    //     }

    //     if (empty($result)) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    //     }

    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => 'Data jadwal karyawan berhasil ditampilkan.',
    //         'data' => $result
    //     ], Response::HTTP_OK);
    // }

    public function index(Request $request)
    {
        if (!Gate::allows('view jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $jadwal = Jadwal::query();

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $jadwal->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $jadwal->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $jadwal->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $jadwal->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $jadwal->whereHas('users.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $jadwal->whereHas('users', function ($query) use ($statusAktif) {
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
                $jadwal->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $jadwal->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $jadwal->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $jadwal->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $jadwal->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $jadwal->whereHas('users.data_karyawans.pendidikan_terakhir', function ($query) use ($namaPendidikan) {
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
            $jadwal->where(function ($query) use ($searchTerm) {
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
            $start_dateConvert = RandomHelper::convertToDateString($request->input('tgl_mulai'));
            $end_dateConvert = RandomHelper::convertToDateString($request->input('tgl_selesai'));
            $start_date = Carbon::parse($start_dateConvert);
            $end_date = Carbon::parse($end_dateConvert);

            if ($end_date->diffInDays($start_date) > 28) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Rentang tanggal yang diberikan tidak boleh melebihi 28 hari dari tanggal mulai.'), Response::HTTP_BAD_REQUEST);
            }

            $date_range = $this->generateDateRange($start_date, $end_date);
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal jadwal mulai dan selesai tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
        }

        // Ambil semua data jadwal terlebih dahulu
        $allSchedules = Jadwal::with(['users', 'shifts'])->get()->groupBy('user_id');

        // Paginate
        if ($limit == 0) {
            $dataJadwal = $jadwal->get();
            $paginationData = null;
        } else {
            // Pastikan limit adalah integer
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataJadwal = $jadwal->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataJadwal->url(1),
                    'last' => $dataJadwal->url($dataJadwal->lastPage()),
                    'prev' => $dataJadwal->previousPageUrl(),
                    'next' => $dataJadwal->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataJadwal->currentPage(),
                    'last_page' => $dataJadwal->lastPage(),
                    'per_page' => $dataJadwal->perPage(),
                    'total' => $dataJadwal->total(),
                ]
            ];
        }

        if ($dataJadwal->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data untuk output
        if ($limit == 0) {
            $groupedSchedules = $dataJadwal->groupBy('user_id');
        } else {
            $groupedSchedules = collect($dataJadwal->items())->groupBy('user_id');
        }
        $result = [];
        foreach ($groupedSchedules as $user_id => $user_schedules) {
            $user = $user_schedules->first()->users;
            $user_schedule_array = array_fill_keys($date_range, null);

            if (isset($allSchedules[$user_id])) {
                foreach ($allSchedules[$user_id] as $schedule) {
                    $current_date = Carbon::parse($schedule->tgl_mulai);
                    while ($current_date->lte(Carbon::parse($schedule->tgl_selesai))) {
                        $date = $current_date->format('Y-m-d');
                        if (in_array($date, $date_range)) {
                            $user_schedule_array[$date] = [
                                'id' => $schedule->id,
                                // 'tanggal' => $date,
                                'tgl_mulai' => $schedule->tgl_mulai,
                                'tgl_selesai' => $schedule->tgl_selesai,
                                'shift' => $schedule->shifts,
                                'updated_at' => $schedule->updated_at
                            ];
                        }
                        $current_date->addDay();
                    }
                }
            }

            $result[] = [
                'id' => $schedule->id,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email_verified_at' => $user->email_verified_at,
                    'data_karyawan_id' => $user->data_karyawan_id,
                    'foto_profil' => $user->foto_profil,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'unit_kerja' => optional($user->data_karyawans)->unit_kerjas,
                'list_jadwal' => array_values($user_schedule_array),
            ];
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data jadwal karyawan berhasil ditampilkan.',
            'data' => $result,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreJadwalKaryawanRequest $request)
    {
        if (!Gate::allows('create jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $jadwals = [];

        DB::beginTransaction();
        try {
            foreach ($data['user_id'] as $userId) {
                // Check if the user exists
                $user = User::find($userId);
                if (!$user) {
                    DB::rollBack();
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Data karyawan dengan ID '{$userId}' tidak ditemukan."), Response::HTTP_NOT_FOUND);
                }

                // Check if the shift exists if shift_id is not null
                if (!is_null($data['shift_id'])) {
                    $shift = Shift::find($data['shift_id']);
                    if (!$shift) {
                        DB::rollBack();
                        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Data shift dengan ID '{$data['shift_id']}' tidak ditemukan."), Response::HTTP_NOT_FOUND);
                    }
                }

                // Konversi tanggal dari string
                $tglMulai = RandomHelper::convertToDateString($data['tgl_mulai']);
                $tglSelesai = RandomHelper::convertToDateString($data['tgl_selesai']);

                // Check for unique constraints or existing data validation
                $existingDataValidation = Jadwal::where('user_id', $userId)
                    ->where('tgl_mulai', $tglMulai)
                    ->where('shift_id', $data['shift_id'])
                    ->first();
                if ($existingDataValidation) {
                    DB::rollBack();
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Jadwal karyawan dengan ID '{$userId}' dan shift tersebut sudah tersedia pada tanggal '{$tglMulai}'."), Response::HTTP_BAD_REQUEST);
                }

                $jadwalArray = [
                    'user_id' => $userId,
                    'tgl_mulai' => $data['tgl_mulai'],
                    'tgl_selesai' => $data['tgl_selesai'],
                    'shift_id' => $data['shift_id'],
                ];

                $jadwal = Jadwal::create($jadwalArray);
                $jadwal->load(['users', 'shifts']);
                $jadwals[] = $jadwal;
            }

            DB::commit();
            return response()->json(new WithoutDataResource(Response::HTTP_OK, "Data jadwal karyawan berhasil ditambahkan."), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createShiftByDate(StoreJadwalShiftKaryawanRequest $request, $userId)
    {
        if (!Gate::allows('create jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $shiftId = $data['shift_id'];
        $tanggalMulai = $data['tgl_mulai'];
        $tanggalMulaiForValidation = RandomHelper::convertToDateString($data['tgl_mulai']);
        $today = Carbon::now()->format('Y-m-d');

        // Validasi tanggal mulai
        if ($tanggalMulaiForValidation == $today) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Anda tidak dapat mengupdate jadwal pada tanggal hari ini.'), Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();

        try {
            // Validasi jenis karyawan melalui unit_kerjas
            $user = User::findOrFail($userId);
            $dataKaryawan = $user->data_karyawans;

            if (!$dataKaryawan || !$dataKaryawan->unit_kerjas || $dataKaryawan->unit_kerjas->jenis_karyawan != 1) {
                DB::rollBack();
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Membuat dan mengupdate jadwal hanya diperuntukkan kepada karyawan shift saja.'), Response::HTTP_FORBIDDEN);
            }

            $shift = Shift::findOrFail($shiftId);

            // Validasi jika karyawan sudah memiliki shift pada tanggal ini
            $existingShift = Jadwal::where('user_id', $userId)
                ->where('tgl_mulai', $tanggalMulaiForValidation)
                ->first();
            if ($existingShift) {
                DB::rollBack();
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Karyawan sudah memiliki shift pada tanggal ini.'), Response::HTTP_NOT_ACCEPTABLE);
            }

            // Membuat shift baru untuk user pada tanggal yang diberikan
            $newJadwal = new Jadwal();
            $newJadwal->user_id = $userId;
            $newJadwal->shift_id = $shiftId;
            $newJadwal->tgl_mulai = $tanggalMulai;
            $newJadwal->tgl_selesai = $tanggalMulai; // Assuming it's a single-day shift
            $newJadwal->save();

            DB::commit();

            return response()->json(new WithoutDataResource(Response::HTTP_OK, "Shift jadwal karyawan '{$newJadwal->users->nama}' berhasil ditambahkan."), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menambahkan jadwal shift: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // public function show($userId, Request $request)
    // {
    //     if (!Gate::allows('view jadwalKaryawan')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melihat data ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $user = User::findOrFail($userId);

    //     // Initialize the date range variable
    //     $date_range = [];
    //     // Only generate date range if both start_date and end_date are provided
    //     if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
    //         $start_date = Carbon::parse($request->input('tgl_mulai'));
    //         $end_date = Carbon::parse($request->input('tgl_selesai'));
    //         $date_range = $this->generateDateRange($start_date, $end_date);
    //     }

    //     $user_schedules_by_date = [];
    //     foreach ($user->jadwals as $schedule) {
    //         $current_date = Carbon::parse($schedule->tgl_mulai);
    //         while ($current_date->lte(Carbon::parse($schedule->tgl_selesai))) {
    //             $user_schedules_by_date[$current_date->format('Y-m-d')] = $schedule;
    //             $current_date->addDay();
    //         }
    //     }

    //     $user_schedule_array = [];
    //     foreach ($date_range as $date) {
    //         if (isset($user_schedules_by_date[$date])) {
    //             $schedule = $user_schedules_by_date[$date];
    //             $user_schedule_array[] = [
    //                 'id' => $schedule->shifts->id,
    //                 'tanggal' => $date,
    //                 'nama_shift' => $schedule->shifts->nama,
    //                 'jam_from' => $schedule->shifts->jam_from,
    //                 'jam_to' => $schedule->shifts->jam_to,
    //             ];
    //         } else {
    //             $user_schedule_array[] = null;
    //         }
    //     }

    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => "Detail jadwal karyawan '{$user->nama}' berhasil ditampilkan.",
    //         'data' => [
    //             'id' => $schedule->id,
    //             'user' => [
    //                 'id' => $user->id,
    //                 'nama' => $user->nama,
    //                 'email_verified_at' => $user->email_verified_at,
    //                 'data_karyawan_id' => $user->data_karyawan_id,
    //                 'foto_profil' => $user->foto_profil,
    //                 'data_completion_step' => $user->data_completion_step,
    //                 'status_aktif' => $user->status_aktif,
    //                 'created_at' => $user->created_at,
    //                 'updated_at' => $user->updated_at
    //             ],
    //             'data_karyawan' => $user->data_karyawans,
    //             'unit_kerja' => $user->data_karyawans->unit_kerjas,
    //             'shift' => $user_schedule_array,
    //         ]
    //     ], Response::HTTP_OK);
    // }

    public function show($userId)
    {
        if (!Gate::allows('view jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melihat data ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = User::with(['data_karyawans.unit_kerjas', 'jadwals.shifts'])->findOrFail($userId);

        $user_schedule_array = [];
        foreach ($user->jadwals as $schedule) {
            $tglMulai = RandomHelper::convertToDateString($schedule->tgl_mulai);
            $tglSelesai = RandomHelper::convertToDateString($schedule->tgl_selesai);

            $current_date = Carbon::parse($tglMulai);
            while ($current_date->lte(Carbon::parse($tglSelesai))) {
                // $date = $current_date->format('Y-m-d');
                $user_schedule_array[] = [
                    'id' => $schedule->id,
                    // 'tanggal' => $date,
                    'tgl_mulai' => $schedule->tgl_mulai,
                    'tgl_selesai' => $schedule->tgl_selesai,
                    'shift' => $schedule->shifts,
                    'updated_at' => $schedule->updated_at
                ];
                $current_date->addDay();
            }
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail jadwal karyawan '{$user->nama}' berhasil ditampilkan.",
            'data' => [
                'id' => $schedule->id,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email_verified_at' => $user->email_verified_at,
                    'data_karyawan_id' => $user->data_karyawan_id,
                    'foto_profil' => $user->foto_profil,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'data_karyawan' => $user->data_karyawans,
                'unit_kerja' => $user->data_karyawans->unit_kerjas,
                'list_jadwal' => $user_schedule_array,
            ]
        ], Response::HTTP_OK);
    }

    public function update(StoreJadwalShiftKaryawanRequest $request, $userId)
    {
        if (!Gate::allows('edit jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $shiftId = $data['shift_id'];
        $tanggalMulai = $data['tgl_mulai'];
        $tanggalMulaiForValidation = RandomHelper::convertToDateString($data['tgl_mulai']);
        $today = Carbon::now()->format('Y-m-d');

        if ($tanggalMulaiForValidation == $today) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Anda tidak dapat mengupdate jadwal pada tanggal hari ini.'), Response::HTTP_BAD_REQUEST);
        }

        $shift = Shift::findOrFail($shiftId);

        DB::beginTransaction();
        try {
            // Validasi jenis karyawan melalui unit_kerjas
            $user = User::findOrFail($userId);
            $dataKaryawan = $user->data_karyawans;

            if (!$dataKaryawan || !$dataKaryawan->unit_kerjas || $dataKaryawan->unit_kerjas->jenis_karyawan != 1) {
                DB::rollBack();
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Membuat dan mengupdate jadwal hanya diperuntukkan kepada karyawan shift saja.'), Response::HTTP_FORBIDDEN);
            }

            $existingShift = Jadwal::where('user_id', $userId)
                ->where('tgl_mulai', $tanggalMulaiForValidation)
                ->first();

            if ($existingShift) {
                // Update existing schedule
                $existingShift->shift_id = $shiftId;
                $existingShift->save();

                DB::commit();

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Data shift jadwal karyawan '{$user->nama}' berhasil diperbarui."), Response::HTTP_OK);
            } else {
                DB::rollBack();
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat memperbarui data shift jadwal karyawan.'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportJadwalKaryawan()
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new JadwalExport(), 'jadwal-karyawans.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jadwal karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function importJadwalKaryawan(ImportJadwalKaryawan $request)
    {
        if (!Gate::allows('import jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new JadwalImport(), $file['jadwal_karyawan_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data presensi karyawan berhasil di import kedalam tabel.'), Response::HTTP_OK);
    }

    private function generateDateRange($start_date, $end_date)
    {
        $dates = [];
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }
}
