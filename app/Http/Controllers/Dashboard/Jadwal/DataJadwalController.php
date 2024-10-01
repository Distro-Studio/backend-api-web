<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\NonShift;
use App\Models\HariLibur;
use App\Models\TukarJadwal;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Imports\Jadwal\JadwalImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateJadwalExport;
use App\Exports\Jadwal\JadwalShiftExport;
use App\Exports\Jadwal\JadwalNonShiftExport;
use App\Http\Requests\StoreJadwalKaryawanRequest;
use App\Http\Requests\StoreJadwalShiftKaryawanRequest;
use App\Http\Requests\Excel_Import\ImportJadwalKaryawan;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataJadwalController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view jadwalKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $limit = $request->input('limit', 10);

            // Mengambil rentang tanggal
            if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
                $start_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_mulai'));
                $end_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_selesai'));

                if ($end_date->diffInDays($start_date) > 28) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Rentang tanggal yang diberikan tidak boleh melebihi 28 hari dari tanggal mulai.'), Response::HTTP_BAD_REQUEST);
                }

                $date_range = $this->generateDateRange($start_date, $end_date);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal jadwal mulai dan selesai tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
            }

            $filters = $request->all();

            $usersQuery = User::where('nama', '!=', 'Super Admin')->where('status_aktif', 2);

            // Filter berdasarkan filter yang diberikan
            if (isset($filters['unit_kerja'])) {
                $usersQuery->whereHas('data_karyawans.unit_kerjas', function ($query) use ($filters) {
                    $query->whereIn('id', (array) $filters['unit_kerja']);
                });
            }

            if (isset($filters['jabatan'])) {
                $usersQuery->whereHas('data_karyawans.jabatans', function ($query) use ($filters) {
                    $query->whereIn('id', (array) $filters['jabatan']);
                });
            }

            if (isset($filters['status_karyawan'])) {
                $usersQuery->whereHas('data_karyawans.status_karyawans', function ($query) use ($filters) {
                    $query->whereIn('id', (array) $filters['status_karyawan']);
                });
            }

            if (isset($filters['status_aktif'])) {
                $usersQuery->whereIn('status_aktif', (array) $filters['status_aktif']);
            }

            if (isset($filters['tgl_masuk'])) {
                $tglMasuk = $filters['tgl_masuk'];
                if (is_array($tglMasuk)) {
                    $convertedDates = array_map(function ($date) {
                        return RandomHelper::convertToDateString($date);
                    }, $tglMasuk);
                    $usersQuery->whereHas('data_karyawans', function ($query) use ($convertedDates) {
                        $query->whereIn(DB::raw('DATE(tgl_masuk)'), $convertedDates);
                    });
                } else {
                    $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                    $usersQuery->whereHas('data_karyawans', function ($query) use ($convertedDate) {
                        $query->where(DB::raw('DATE(tgl_masuk)'), $convertedDate);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $usersQuery->whereHas('data_karyawans.kategori_agamas', function ($query) use ($filters) {
                    $query->whereIn('id', (array) $filters['agama']);
                });
            }

            if (isset($filters['jenis_kelamin'])) {
                $jenisKelamin = $filters['jenis_kelamin'];
                $usersQuery->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                    if (is_array($jenisKelamin)) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    } else {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    }
                });
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $usersQuery->whereHas('data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
                    $query->whereIn('id', (array) $namaPendidikan);
                });
            }

            if (isset($filters['jenis_karyawan'])) {
                $jenisKaryawan = $filters['jenis_karyawan'];
                $usersQuery->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    if (is_array($jenisKaryawan)) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    } else {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    }
                });
            }

            if (isset($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $usersQuery->where(function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm)
                        ->orWhereHas('data_karyawans', function ($query) use ($searchTerm) {
                            $query->where('nik', 'like', $searchTerm);
                        });
                });
            }

            $paginationData = null;
            if ($limit != 0) {
                $users = $usersQuery->paginate($limit);
                $paginationData = [
                    'links' => [
                        'first' => $users->url(1),
                        'last' => $users->url($users->lastPage()),
                        'prev' => $users->previousPageUrl(),
                        'next' => $users->nextPageUrl(),
                    ],
                    'meta' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                    ]
                ];
            } else {
                $users = $usersQuery->get();
            }

            $jadwal = Jadwal::whereIn('user_id', $users->pluck('id'))->get();
            $hariLibur = HariLibur::whereIn('tanggal', $date_range)->get()->keyBy('tanggal');
            $nonShift = NonShift::first(); // Asumsikan hanya ada satu jadwal non-shift

            $groupedSchedules = $jadwal->groupBy('user_id');
            $result = $users->map(function ($user) use ($groupedSchedules, $date_range, $nonShift, $hariLibur, $jadwal, $request) {
                $user_schedule_array = array_fill_keys($date_range, null);

                // Mengisi user_schedule_array dengan jadwal yang sebenarnya
                if ($groupedSchedules->has($user->id)) {
                    foreach ($groupedSchedules[$user->id] as $schedule) {
                        $current_date = Carbon::createFromFormat('Y-m-d', $schedule->tgl_mulai);
                        $end_date_for_schedule = Carbon::createFromFormat('Y-m-d', $schedule->tgl_selesai);

                        while ($current_date->lte($end_date_for_schedule)) {
                            $date = $current_date->format('Y-m-d');
                            if (array_key_exists($date, $user_schedule_array)) {
                                $user_schedule_array[$date] = [
                                    'id' => $schedule->id,
                                    'tgl_mulai' => $schedule->tgl_mulai,
                                    'tgl_selesai' => $schedule->tgl_selesai,
                                    'shift' => $schedule->shifts,
                                    'updated_at' => $schedule->updated_at,
                                    'status' => 1 // 1 = ada jadwal
                                ];
                            }
                            $current_date->addDay();
                        }
                    }
                } else if ($user->data_karyawans->unit_kerjas->jenis_karyawan == 0) {
                    // Jika user non-shift, gunakan data dari tabel non_shifts atau hari libur
                    foreach ($date_range as $date) {
                        $day_of_week = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;

                        if ($day_of_week == Carbon::SUNDAY) {
                            // Libur pada hari Minggu
                            $user_schedule_array[$date] = [
                                'id' => null,
                                'nama' => 'Libur Minggu',
                                'jam_from' => null,
                                'jam_to' => null,
                                'status' => 4 // libur minggu
                            ];
                        } elseif (isset($hariLibur[$date])) {
                            $user_schedule_array[$date] = [
                                'id' => $hariLibur[$date]->id,
                                'nama' => $hariLibur[$date]->nama,
                                'jam_from' => null,
                                'jam_to' => null,
                                'status' => 3 // libur besar
                            ];
                        } else if ($nonShift) {
                            $user_schedule_array[$date] = [
                                'id' => $nonShift->id,
                                'nama' => $nonShift->nama,
                                'jam_from' => $nonShift->jam_from,
                                'jam_to' => $nonShift->jam_to,
                                'status' => 2 // non-shift
                            ];
                        }
                    }
                }

                if ($user->data_karyawans->unit_kerjas->jenis_karyawan == 0) {
                    if ($nonShift && $nonShift->jam_from && $nonShift->jam_to) {
                        $jamFrom = Carbon::createFromFormat('H:i:s', $nonShift->jam_from);
                        $jamTo = Carbon::createFromFormat('H:i:s', $nonShift->jam_to);

                        if ($jamTo->lt($jamFrom)) {
                            $jamTo->addDay();
                        }

                        $shiftSeconds = $jamTo->diffInSeconds($jamFrom);

                        // Filter hari kerja (Senin - Sabtu) yang tidak libur
                        $workingDaysCount = collect($date_range)->filter(function ($date) use ($hariLibur) {
                            $dayOfWeek = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;
                            // Hitung Senin (1) hingga Sabtu (6), dan tanggal yang bukan hari libur
                            return $dayOfWeek >= 1 && $dayOfWeek <= 6 && !isset($hariLibur[$date]);
                        })->count();
                        $totalJamFilter = $shiftSeconds * $workingDaysCount;
                    } else {
                        $totalJamFilter = null;
                    }
                } else {
                    // Shift user
                    $totalJamFilter = $groupedSchedules->get($user->id, collect())->filter(function ($schedule) use ($request) {
                        // Jadwal yang ada pada rentang start_date dan end_date
                        $start_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_mulai'));
                        $end_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_selesai'));

                        // Ambil jadwal mulai dan selesai
                        $scheduleStart = Carbon::parse($schedule->tgl_mulai);
                        $scheduleEnd = $schedule->tgl_selesai ? Carbon::parse($schedule->tgl_selesai) : $scheduleStart;

                        // Bandingkan apakah jadwal ada dalam rentang tanggal
                        return $scheduleStart <= $end_date && $scheduleEnd >= $start_date;
                    })->sum(function ($schedule) {
                        $shift = $schedule->shifts;

                        // Jika shift ada dan memiliki jam_from dan jam_to
                        if ($shift && $shift->jam_from && $shift->jam_to) {
                            $jamFrom = Carbon::createFromFormat('H:i:s', $shift->jam_from);
                            $jamTo = Carbon::createFromFormat('H:i:s', $shift->jam_to);

                            if ($jamTo->lt($jamFrom)) {
                                $jamTo->addDay();
                            }

                            // Hitung selisih waktu dalam detik
                            return $jamTo->diffInSeconds($jamFrom);
                        }

                        return 0;
                    });
                }

                // Total detik saja, tanpa konversi ke jam atau menit
                $totalJam = $totalJamFilter > 0 ? $totalJamFilter : null;

                return [
                    'user' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'username' => $user->username,
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
                    'total_jam' => $totalJam
                ];
            });

            if ($result->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data jadwal karyawan tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data jadwal karyawan berhasil ditampilkan.',
                'data' => $result,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat menampilkan detail data jadwal karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreJadwalKaryawanRequest $request)
    {
        try {
            if (!Gate::allows('create jadwalKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();

            $jadwals = [];
            $tanggalMulai = Carbon::createFromFormat('d-m-Y', $data['tgl_mulai']);
            $tanggalSelesai = $data['tgl_selesai'] ? Carbon::createFromFormat('d-m-Y', $data['tgl_selesai']) : $tanggalMulai;

            $today = Carbon::today();

            // Validasi tanggal mulai
            if ($tanggalMulai->lessThanOrEqualTo($today)) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Tidak diperbolehkan menerapkan jadwal untuk hari ini atau hari terlewat. Hanya diperbolehkan H+1 hari ini.'), Response::HTTP_NOT_ACCEPTABLE);
            }

            // Get unit kerja yang sedang login
            $admin = Auth::user();
            $adminUnitKerja = $admin->data_karyawans->unit_kerjas->id ?? null;

            DB::beginTransaction();
            try {
                foreach ($data['user_id'] as $userId) {
                    // Check if the user exists
                    $user = User::find($userId);
                    if (!$user) {
                        DB::rollBack();
                        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Data karyawan '{$user->nama}' tidak ditemukan."), Response::HTTP_NOT_FOUND);
                    }

                    // Validasi kesamaan unit kerja antara admin dan karyawan
                    if ($admin->nama !== 'Super Admin') {
                        if (!Gate::allows('bypass jadwalKaryawan')) {
                            $karyawanUnitKerja = $user->data_karyawans->unit_kerjas->id ?? null;
                            if ($adminUnitKerja !== $karyawanUnitKerja) {
                                DB::rollBack();
                                return response()->json(new WithoutDataResource(
                                    Response::HTTP_FORBIDDEN,
                                    "Terdapat beberapa karyawan yang tidak sesuai dengan unit kerja Anda. Anda hanya dapat mengatur jadwal karyawan dalam unit kerja yang sama dengan Anda."
                                ), Response::HTTP_FORBIDDEN);
                            }
                        }
                    }

                    // Cek apakah ada cuti pada rentang tanggal ini
                    $cutiConflict = Cuti::where('user_id', $userId)
                        ->where(function ($query) use ($tanggalMulai, $tanggalSelesai) {
                            // Konversi tgl_from dan tgl_to ke format Y-m-d sebelum melakukan perbandingan
                            $query->whereBetween(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), [$tanggalMulai->format('Y-m-d'), $tanggalSelesai->format('Y-m-d')])
                                ->orWhereBetween(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), [$tanggalMulai->format('Y-m-d'), $tanggalSelesai->format('Y-m-d')])
                                ->orWhere(function ($query) use ($tanggalMulai, $tanggalSelesai) {
                                    $query->where(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', $tanggalMulai->format('Y-m-d'))
                                        ->where(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', $tanggalSelesai->format('Y-m-d'));
                                });
                        })
                        ->first();

                    if ($cutiConflict && $cutiConflict->status_cuti_id > 1) {
                        DB::rollBack();
                        $cutiFrom = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_from)->format('d-m-Y');
                        $cutiTo = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_to)->format('d-m-Y');
                        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tidak dapat membuat jadwal untuk karyawan '{$user->nama}', karena memiliki cuti pada rentang tanggal {$cutiFrom} hingga {$cutiTo}."), Response::HTTP_BAD_REQUEST);
                    }

                    if ($data['shift_id'] == 3) {
                        $tanggalAkhirShiftMalam = $tanggalMulai->copy()->addDays(2);

                        $cutiConflict = Cuti::where('user_id', $userId)
                            ->where(function ($query) use ($tanggalMulai, $tanggalAkhirShiftMalam) {
                                $query->whereBetween(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), [$tanggalMulai->format('Y-m-d'), $tanggalAkhirShiftMalam->format('Y-m-d')])
                                    ->orWhereBetween(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), [$tanggalMulai->format('Y-m-d'), $tanggalAkhirShiftMalam->format('Y-m-d')])
                                    ->orWhere(function ($query) use ($tanggalMulai, $tanggalAkhirShiftMalam) {
                                        $query->where(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', $tanggalMulai->format('Y-m-d'))
                                            ->where(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', $tanggalAkhirShiftMalam->format('Y-m-d'));
                                    });
                            })
                            ->first();

                        // Jika ada konflik cuti, return error
                        if ($cutiConflict && $cutiConflict->status_cuti_id > 1) {
                            $cutiFrom = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_from)->format('d-m-Y');
                            $cutiTo = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_to)->format('d-m-Y');
                            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tidak dapat membuat jadwal untuk karyawan '{$user->nama}', karena memiliki cuti pada rentang tanggal {$cutiFrom} hingga {$cutiTo}."), Response::HTTP_BAD_REQUEST);
                        }
                    }

                    // Reset tanggalMulai untuk setiap user
                    $currentTanggalMulai = $tanggalMulai->copy();

                    // Loop through each day in the range from tgl_mulai to tgl_selesai
                    while ($currentTanggalMulai->lessThanOrEqualTo($tanggalSelesai)) {
                        // Check if the shift exists if shift_id is not null
                        $shift = null;
                        if ($data['shift_id'] != 0) { // Skip validation if shift_id is 0
                            $shift = Shift::find($data['shift_id']);
                            if (!$shift) {
                                DB::rollBack();
                                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Data shift dengan ID '{$data['shift_id']}' tidak ditemukan."), Response::HTTP_NOT_FOUND);
                            }
                        }

                        // Determine tgl_selesai based on shift times
                        $tglSelesai = $currentTanggalMulai->copy(); // Start with the same date
                        if ($shift) {
                            $jamFrom = Carbon::parse(RandomHelper::convertToTimeString($shift->jam_from));
                            $jamTo = Carbon::parse(RandomHelper::convertToTimeString($shift->jam_to));
                            if ($jamTo->lessThan($jamFrom)) {
                                $tglSelesai->addDay();
                            }
                        } else {
                            $tglSelesai = $currentTanggalMulai; // When shift_id is null, tgl_selesai is same as tgl_mulai
                        }

                        if ($data['shift_id'] == 3) {
                            $nextDay = $currentTanggalMulai->copy()->addDay();
                            $nextDayShift = Jadwal::where('user_id', $userId)
                                ->whereDate('tgl_mulai', $nextDay)
                                ->first();

                            if ($nextDayShift) {
                                // Hapus jadwal hari berikutnya jika ada
                                $nextDayShift->delete();
                            }
                        }

                        // Check for existing schedule for the same user and date
                        $existingSchedule = Jadwal::where('user_id', $userId)
                            ->whereDate('tgl_mulai', $currentTanggalMulai)
                            ->first();
                        if ($existingSchedule) {
                            DB::rollBack();
                            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Jadwal karyawan '{$user->nama}' sudah tersedia pada tanggal '{$currentTanggalMulai->toDateString()}'."), Response::HTTP_BAD_REQUEST);
                        }

                        // Create the new schedule
                        $jadwalArray = [
                            'user_id' => $userId,
                            'tgl_mulai' => $currentTanggalMulai->format('Y-m-d'),
                            'tgl_selesai' => $tglSelesai->format('Y-m-d'),
                            'shift_id' => $data['shift_id'],
                        ];

                        $jadwal = Jadwal::create($jadwalArray);
                        $jadwal->load(['users', 'shifts']);
                        $jadwals[] = $jadwal;

                        // Increment the currentTanggalMulai for the next iteration (next day)
                        $currentTanggalMulai->addDay();
                    }
                }

                DB::commit();
                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Data jadwal karyawan berhasil ditambahkan."), Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat menyimpan jadwal karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createShiftByDate(StoreJadwalShiftKaryawanRequest $request, $userId)
    {
        try {
            if (!Gate::allows('create jadwalKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();
            $shiftId = $data['shift_id'] ?? null;
            $tanggalMulai = Carbon::createFromFormat('d-m-Y', $data['tgl_mulai']);
            $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

            // Validasi tanggal mulai
            if ($tanggalMulai->format('Y-m-d') == $today) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Anda tidak dapat mengupdate jadwal pada tanggal hari ini.'), Response::HTTP_BAD_REQUEST);
            }

            // Get admin yang sedang login
            $admin = Auth::user();
            $adminUnitKerja = $admin->data_karyawans->unit_kerjas->id ?? null;

            // Get user data
            $user = User::findOrFail($userId);
            $dataKaryawan = $user->data_karyawans;
            $karyawanUnitKerja = $dataKaryawan->unit_kerjas->id ?? null;

            if ($admin->nama !== 'Super Admin') {
                if (!Gate::allows('bypass jadwalKaryawan') && $adminUnitKerja !== $karyawanUnitKerja) {
                    return response()->json(new WithoutDataResource(
                        Response::HTTP_FORBIDDEN,
                        'Anda hanya dapat mengatur jadwal untuk karyawan dalam unit kerja yang sama dengan anda.'
                    ), Response::HTTP_FORBIDDEN);
                }
            }

            $cutiConflict = Cuti::where('user_id', $userId)
                ->where(function ($query) use ($tanggalMulai) {
                    $query->where(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', $tanggalMulai->format('Y-m-d'))
                        ->where(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', $tanggalMulai->format('Y-m-d'));
                })
                ->first();

            if ($cutiConflict && $cutiConflict->status_cuti_id > 1) {
                $cutiFrom = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_from)->format('d-m-Y');
                $cutiTo = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_to)->format('d-m-Y');
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tidak dapat membuat jadwal untuk karyawan '{$user->nama}', karena memiliki cuti pada rentang tanggal {$cutiFrom} hingga {$cutiTo}."), Response::HTTP_BAD_REQUEST);
            }

            if ($shiftId == 3) {
                $tanggalAkhirShiftMalam = $tanggalMulai->copy()->addDays(2);

                // Cek apakah ada cuti dalam rentang tgl_mulai sampai H+2
                $cutiConflict = Cuti::where('user_id', $userId)
                    ->where(function ($query) use ($tanggalMulai, $tanggalAkhirShiftMalam) {
                        $query->whereBetween(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), [$tanggalMulai->format('Y-m-d'), $tanggalAkhirShiftMalam->format('Y-m-d')])
                            ->orWhereBetween(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), [$tanggalMulai->format('Y-m-d'), $tanggalAkhirShiftMalam->format('Y-m-d')])
                            ->orWhere(function ($query) use ($tanggalMulai, $tanggalAkhirShiftMalam) {
                                $query->where(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', $tanggalMulai->format('Y-m-d'))
                                    ->where(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', $tanggalAkhirShiftMalam->format('Y-m-d'));
                            });
                    })
                    ->first();

                // Jika ada konflik cuti, return error
                if ($cutiConflict && $cutiConflict->status_cuti_id > 1) {
                    $cutiFrom = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_from)->format('d-m-Y');
                    $cutiTo = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_to)->format('d-m-Y');
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tidak dapat membuat jadwal untuk karyawan '{$user->nama}', karena memiliki cuti pada rentang tanggal {$cutiFrom} hingga {$cutiTo}."), Response::HTTP_BAD_REQUEST);
                }
            }

            DB::beginTransaction();

            try {
                // Validasi jenis karyawan melalui unit_kerjas
                $user = User::findOrFail($userId);
                $dataKaryawan = $user->data_karyawans;

                if (!$dataKaryawan || !$dataKaryawan->unit_kerjas || $dataKaryawan->unit_kerjas->jenis_karyawan != 1) {
                    DB::rollBack();
                    return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Membuat dan mengupdate jadwal hanya diperuntukkan kepada karyawan shift.'), Response::HTTP_FORBIDDEN);
                }

                // Validasi jika karyawan sudah memiliki shift pada tanggal ini
                $existingShift = Jadwal::where('user_id', $userId)
                    ->whereDate('tgl_mulai', $tanggalMulai)
                    ->first();
                if ($existingShift) {
                    DB::rollBack();
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Karyawan sudah memiliki shift pada tanggal ini.'), Response::HTTP_BAD_REQUEST);
                }

                if ($shiftId == 3) {
                    $nextDay = $tanggalMulai->copy()->addDay();
                    $nextDayShift = Jadwal::where('user_id', $userId)
                        ->whereDate('tgl_mulai', $nextDay)
                        ->first();
                    if ($nextDayShift) {
                        // Hapus jadwal hari berikutnya jika ada
                        $nextDayShift->delete();
                    }
                }

                // Calculate tgl_selesai based on shift times if shift_id is provided and not 0
                $tglSelesai = $tanggalMulai->copy(); // Start with the same date
                if ($shiftId != 0) {
                    $shift = Shift::findOrFail($shiftId);
                    $jamFrom = Carbon::parse($shift->jam_from);
                    $jamTo = Carbon::parse($shift->jam_to);
                    if ($jamTo->lessThan($jamFrom)) {
                        $tglSelesai->addDay();
                    }
                }

                // Membuat shift baru untuk user pada tanggal yang diberikan
                $newJadwal = new Jadwal();
                $newJadwal->user_id = $userId;
                $newJadwal->shift_id = $shiftId;
                $newJadwal->tgl_mulai = $tanggalMulai->format('Y-m-d');
                $newJadwal->tgl_selesai = $tglSelesai->format('Y-m-d');
                $newJadwal->save();

                DB::commit();

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Shift jadwal karyawan '{$newJadwal->users->nama}' berhasil ditambahkan."), Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menambahkan jadwal shift: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat membuat jadwal karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($userId)
    {
        try {
            if (!Gate::allows('view jadwalKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melihat data ini.'), Response::HTTP_FORBIDDEN);
            }

            $user = User::with(['data_karyawans.unit_kerjas', 'jadwals.shifts'])->findOrFail($userId);

            $user_schedule_array = [];
            foreach ($user->jadwals as $schedule) {
                $tglMulai = Carbon::createFromFormat('Y-m-d', $schedule->tgl_mulai)->format('d-m-Y');
                $tglSelesai = Carbon::createFromFormat('Y-m-d', $schedule->tgl_selesai)->format('d-m-Y');

                $current_date = Carbon::createFromFormat('d-m-Y', $tglMulai);
                $end_date = Carbon::createFromFormat('d-m-Y', $tglSelesai);
                while ($current_date->lte(Carbon::parse($tglSelesai))) {
                    // $date = $current_date->format('Y-m-d');
                    $user_schedule_array[] = [
                        'id' => $schedule->id,
                        // 'tanggal' => $date,
                        'tgl_mulai' => $tglMulai,
                        'tgl_selesai' => $tglSelesai,
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
                        'username' => $user->username,
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
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat menampilkan detail jadwal karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(StoreJadwalShiftKaryawanRequest $request, $userId)
    {
        try {
            if (!Gate::allows('edit jadwalKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();
            $shiftId = $data['shift_id'] ?? null;
            $tanggalMulai = Carbon::createFromFormat('d-m-Y', $data['tgl_mulai'])->format('Y-m-d');
            $today = Carbon::today()->format('Y-m-d');

            if ($tanggalMulai == $today) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Anda tidak dapat memperbarui jadwal pada tanggal hari ini.'), Response::HTTP_BAD_REQUEST);
            }

            // Get admin yang sedang login
            $admin = Auth::user();
            $adminUnitKerja = $admin->data_karyawans->unit_kerjas->id ?? null;

            // Get user data
            $user = User::findOrFail($userId);
            $dataKaryawan = $user->data_karyawans;
            $karyawanUnitKerja = $dataKaryawan->unit_kerjas->id ?? null;

            // Validasi kesamaan unit kerja antara admin dan karyawan kecuali jika admin adalah Super Admin
            if ($admin->nama !== 'Super Admin') {
                if (!Gate::allows('bypass jadwalKaryawan') && $adminUnitKerja !== $karyawanUnitKerja) {
                    return response()->json(new WithoutDataResource(
                        Response::HTTP_FORBIDDEN,
                        'Anda hanya dapat mengatur jadwal untuk karyawan dalam unit kerja yang sama dengan anda.'
                    ), Response::HTTP_FORBIDDEN);
                }
            }

            $tukarJadwal = TukarJadwal::where(function ($query) use ($userId) {
                $query->whereHas('jadwal_pengajuans', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                    ->orWhereHas('jadwal_ditukars', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })->whereNotIn('status_penukaran_id', [3, 4, 5])
                ->first();

            if ($tukarJadwal) {
                $jadwalPengajuanId = $tukarJadwal->jadwal_pengajuan;
                $jadwalDitukarId = $tukarJadwal->jadwal_ditukar;

                // Cek apakah jadwal yang sedang diupdate terlibat dalam penukaran
                $existingJadwal = Jadwal::where('user_id', $userId)
                    ->whereIn('id', [$jadwalPengajuanId, $jadwalDitukarId])
                    ->first();

                if ($existingJadwal) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Jadwal ini sedang dalam proses penukaran dan tidak dapat diubah.'), Response::HTTP_BAD_REQUEST);
                }
            }

            $cutiConflict = Cuti::where('user_id', $userId)
                ->where(function ($query) use ($tanggalMulai) {
                    $query->where(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', $tanggalMulai)
                        ->where(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', $tanggalMulai);
                })
                ->first();

            if ($cutiConflict && $cutiConflict->status_cuti_id > 1) {
                $cutiFrom = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_from)->format('d-m-Y');
                $cutiTo = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_to)->format('d-m-Y');
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tidak dapat memperbarui jadwal untuk karyawan '{$user->nama}', karena memiliki cuti pada rentang tanggal {$cutiFrom} hingga {$cutiTo}."), Response::HTTP_BAD_REQUEST);
            }

            if ($shiftId == 3) {
                $tanggalAkhirShiftMalam = Carbon::createFromFormat('Y-m-d', $tanggalMulai)->addDays(2);

                $cutiConflict = Cuti::where('user_id', $userId)
                    ->where(function ($query) use ($tanggalMulai, $tanggalAkhirShiftMalam) {
                        $query->whereBetween(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), [$tanggalMulai, $tanggalAkhirShiftMalam])
                            ->orWhereBetween(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), [$tanggalMulai, $tanggalAkhirShiftMalam])
                            ->orWhere(function ($query) use ($tanggalMulai, $tanggalAkhirShiftMalam) {
                                $query->where(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', $tanggalMulai)
                                    ->where(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', $tanggalAkhirShiftMalam);
                            });
                    })
                    ->first();

                if ($cutiConflict && $cutiConflict->status_cuti_id > 1) {
                    $cutiFrom = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_from)->format('d-m-Y');
                    $cutiTo = Carbon::createFromFormat('d-m-Y', $cutiConflict->tgl_to)->format('d-m-Y');
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tidak dapat memperbarui jadwal untuk karyawan '{$user->nama}', karena memiliki cuti pada rentang tanggal {$cutiFrom} hingga {$cutiTo}."), Response::HTTP_BAD_REQUEST);
                }

                $nextDay = Carbon::parse($tanggalMulai)->addDay();
                $nextDayShift = Jadwal::where('user_id', $userId)
                    ->whereDate('tgl_mulai', $nextDay)
                    ->first();
                if ($nextDayShift) {
                    $nextDayShift->delete();
                }
            }

            DB::beginTransaction();
            try {
                // Validasi jenis karyawan melalui unit_kerjas
                $user = User::findOrFail($userId);
                $dataKaryawan = $user->data_karyawans;

                if (!$dataKaryawan || !$dataKaryawan->unit_kerjas || $dataKaryawan->unit_kerjas->jenis_karyawan != 1) {
                    DB::rollBack();
                    return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Membuat dan mengupdate jadwal hanya diperuntukkan kepada karyawan shift.'), Response::HTTP_FORBIDDEN);
                }

                $existingShift = Jadwal::where('user_id', $userId)
                    ->whereDate('tgl_mulai', $tanggalMulai)
                    ->first();

                if ($existingShift) {
                    // If shift_id is null, set tgl_selesai equal to tgl_mulai
                    // if (is_null($shiftId)) {
                    //     $tglSelesai = $tanggalMulai;
                    // } else {
                    //     // Retrieve the shift and calculate tgl_selesai
                    //     $shift = Shift::findOrFail($shiftId);
                    //     $jamFrom = Carbon::parse(RandomHelper::convertToTimeString($shift->jam_from));
                    //     $jamTo = Carbon::parse(RandomHelper::convertToTimeString($shift->jam_to));

                    //     if ($jamTo->lessThanOrEqualTo($jamFrom)) {
                    //         $tglSelesai = $tanggalMulai->copy()->addDay();
                    //     } else {
                    //         // Shift ends on the same day
                    //         $tglSelesai = $tanggalMulai;
                    //     }
                    // }


                    $tglSelesai = Carbon::parse($tanggalMulai); // Default to the same day as Carbon object

                    if ($shiftId != 0) { // Skip validation if shift_id is 0
                        $shift = Shift::findOrFail($shiftId);
                        $jamFrom = Carbon::parse(RandomHelper::convertToTimeString($shift->jam_from));
                        $jamTo = Carbon::parse(RandomHelper::convertToTimeString($shift->jam_to));

                        if ($jamTo->lessThanOrEqualTo($jamFrom)) {
                            $tglSelesai->addDay();
                        }
                    }

                    // Format tglSelesai to 'Y-m-d' before saving it to the database
                    $existingShift->shift_id = $shiftId;
                    $existingShift->tgl_selesai = $tglSelesai->format('Y-m-d');
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
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat edit jadwal karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportJadwalKaryawanShift()
    {
        try {
            if (!Gate::allows('export jadwalKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataJadwal = Jadwal::all();
            if ($dataJadwal->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data jadwal karyawan shift yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new JadwalShiftExport(), 'jadwal-shift-karyawan.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat export data jadwal karyawan shift: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportJadwalKaryawanNonShift()
    {
        try {
            if (!Gate::allows('export jadwalKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $jadwalNonShift = NonShift::all();
            if ($jadwalNonShift->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data jadwal non shift karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new JadwalNonShiftExport(), 'jadwal-non-shift-karyawan.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat export data jadwal karyawan non shift: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importJadwalKaryawan(ImportJadwalKaryawan $request)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error saat import data jadwal karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function generateDateRange($start_date, $end_date)
    {
        $dates = [];
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }

    public function downloadJadwalTemplate()
    {
        try {
            return Excel::download(new TemplateJadwalExport(), 'template_jadwal_import.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
