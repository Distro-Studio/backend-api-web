<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\Jadwal\JadwalExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Excel_Import\ImportJadwalKaryawan;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreJadwalKaryawanRequest;
use App\Http\Requests\StoreJadwalShiftKaryawan;
use App\Http\Requests\StoreJadwalShiftKaryawanRequest;
use App\Http\Requests\UpdateJadwalKaryawanRequest;
use App\Http\Resources\Dashboard\Jadwal\JadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Imports\Jadwal\JadwalImport;

class JadwalController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllJadwalKaryawan()
    {
        if (!Gate::allows('view jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jadwalKaryawan = Jadwal::whereHas('users', function ($query) {
            $query->where('username', '!=', 'super_admin');
        })->with('users.data_karyawans.unit_kerjas', 'shifts')->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all jadwal karyawan for dropdown',
            'data' => $jadwalKaryawan
        ]);
    }

    public function getAllKaryawanUnitKerja()
    {
        if (!Gate::allows('view jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jadwalKaryawan = Jadwal::with(['users' => function ($query) {
            $query->where('username', '!=', 'super_admin')
                ->with('data_karyawans.unit_kerjas');
        }])->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all user for karyawan unit kerja for dropdown',
            'data' => $jadwalKaryawan
        ]);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jadwal = Jadwal::with(['users.data_karyawans.unit_kerjas', 'shifts']);

        // Filter by status_karyawan
        if ($request->has('status_karyawan')) {
            $namaStatus = $request->status_karyawan;
            $jadwal->whereHas('users.data_karyawans', function ($query) use ($namaStatus) {
                if (is_array($namaStatus)) {
                    $query->whereIn('status_karyawan', $namaStatus);
                } else {
                    $query->where('status_karyawan', '=', $namaStatus);
                }
            });
        }

        // Filter by jenis_karyawan
        if ($request->has('jenis_karyawan')) {
            $jenisKaryawan = $request->jenis_karyawan;
            $jadwal->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                if (is_array($jenisKaryawan)) {
                    $jenisKaryawan = array_map('boolval', $jenisKaryawan);
                    $query->whereIn('jenis_karyawan', $jenisKaryawan);
                } else {
                    $jenisKaryawan = (bool) $jenisKaryawan;
                    $query->where('jenis_karyawan', '=', $jenisKaryawan);
                }
            });
        }

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $jadwal->whereHas('users', function ($query) use ($searchTerm) {
                $query->where('nama', 'like', $searchTerm);
            })
                ->orWhereHas('users.data_karyawans.unit_kerjas', function ($query) use ($searchTerm) {
                    $query->where('jenis_karyawan', 'like', $searchTerm);
                });
        }


        $date_range = [];
        if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
            $start_date = Carbon::parse($request->input('tgl_mulai'));
            $end_date = Carbon::parse($request->input('tgl_selesai'));

            if ($end_date->diffInDays($start_date) > 28) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Rentang tanggal yang diberikan tidak boleh melebihi 28 hari dari tanggal mulai.'), Response::HTTP_BAD_REQUEST);
            }
        }
        // else {
        //     $start_date = Carbon::now()->startOfWeek();
        //     $end_date = Carbon::now()->endOfWeek();
        // }

        $date_range = $this->generateDateRange($start_date, $end_date);

        // Fetch schedules
        $schedules = $jadwal->get()->groupBy('user_id');
        $result = [];
        foreach ($schedules as $user_id => $user_schedules) {
            $user = $user_schedules->first()->users;
            $user_schedule_array = [];
            $user_schedules_by_date = [];

            foreach ($user_schedules as $schedule) {
                $current_date = Carbon::parse($schedule->tgl_mulai);
                while ($current_date->lte(Carbon::parse($schedule->tgl_selesai))) {
                    $user_schedules_by_date[$current_date->format('Y-m-d')] = $schedule;
                    $current_date->addDay();
                }
            }

            foreach ($date_range as $date) {
                if (isset($user_schedules_by_date[$date])) {
                    $schedule = $user_schedules_by_date[$date];
                    $user_schedule_array[] = [
                        'id' => $schedule->shifts->id,
                        'tanggal' => $date,
                        'nama_shift' => $schedule->shifts->nama,
                        'jam_from' => $schedule->shifts->jam_from,
                        'jam_to' => $schedule->shifts->jam_to,
                    ];
                } else {
                    $user_schedule_array[] = null;
                }
            }

            $result[] = [
                'id' => $schedule->id,
                'user' => $user,
                'unit_kerja' => optional($user->data_karyawans)->unit_kerjas,
                'list_jadwal' => $user_schedule_array,
            ];
        }

        if (empty($result)) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data jadwal karyawan berhasil ditampilkan.',
            'data' => $result
        ], Response::HTTP_OK);
    }

    public function store(StoreJadwalKaryawanRequest $request)
    {
        if (!Gate::allows('create jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $jadwals = [];

        foreach ($data['user_id'] as $userId) {
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

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jadwal karyawan berhasil ditambahkan.'), Response::HTTP_OK);
    }

    public function createShiftKaryawan(StoreJadwalShiftKaryawanRequest $request, $userId)
    {
        if (!Gate::allows('create jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $shiftId = $data['shift_id'];
        $tanggalMulai = Carbon::parse($data['tgl_mulai'])->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');

        if ($tanggalMulai == $today) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Anda tidak dapat mengupdate jadwal pada tanggal hari ini.'), Response::HTTP_BAD_REQUEST);
        }

        $shift = Shift::findOrFail($shiftId);

        $existingShift = Jadwal::where('user_id', $userId)
            ->where('tgl_mulai', $tanggalMulai)
            ->first();
        if ($existingShift) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Karyawan sudah memiliki shift pada tanggal ini.'), Response::HTTP_NOT_ACCEPTABLE);
        }

        // Membuat shift baru untuk user pada tanggal yang diberikan
        $newJadwal = new Jadwal();
        $newJadwal->user_id = $userId;
        $newJadwal->shift_id = $shiftId;
        $newJadwal->tgl_mulai = $tanggalMulai;
        $newJadwal->tgl_selesai = $tanggalMulai; // Assuming it's a single-day shift
        $newJadwal->save();

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Shift jadwal karyawan berhasil ditambahkan.'), Response::HTTP_OK);
    }

    public function show($userId, Request $request)
    {
        if (!Gate::allows('view jadwalKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melihat data ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = User::with(['data_karyawans.unit_kerjas', 'jadwals.shifts'])->findOrFail($userId);

        // Initialize the date range variable
        $date_range = [];
        // Only generate date range if both start_date and end_date are provided
        if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
            $start_date = Carbon::parse($request->input('tgl_mulai'));
            $end_date = Carbon::parse($request->input('tgl_selesai'));
            $date_range = $this->generateDateRange($start_date, $end_date);
        }
        // else {
        //     $start_date = Carbon::now()->startOfMonth();
        //     $end_date = Carbon::now()->endOfMonth();
        //     $date_range = $this->generateDateRange($start_date, $end_date);
        // }

        $user_schedules_by_date = [];
        foreach ($user->jadwals as $schedule) {
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
                    'id' => $schedule->shifts->id,
                    'tanggal' => $date,
                    'nama_shift' => $schedule->shifts->nama,
                    'jam_from' => $schedule->shifts->jam_from,
                    'jam_to' => $schedule->shifts->jam_to,
                ];
            } else {
                $user_schedule_array[] = null;
            }
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Detail jadwal karyawan berhasil ditampilkan.',
            'data' => [
                'id' => $schedule->id,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'role_id' => $user->role_id,
                    'foto_profil' => $user->foto_profil,
                    'data_completion_step' => $user->data_completion_step,
                    'data_karyawan' => $user->data_karyawans,
                ],
                'unit_kerja' => $user->data_karyawans->unit_kerjas,
                'shifts' => $user_schedule_array,
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
        $tanggalMulai = Carbon::parse($data['tgl_mulai'])->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');

        if ($tanggalMulai == $today) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Anda tidak dapat mengupdate jadwal pada tanggal hari ini.'), Response::HTTP_BAD_REQUEST);
        }

        $shift = Shift::findOrFail($shiftId);

        $existingShift = Jadwal::where('user_id', $userId)
            ->where('tgl_mulai', $tanggalMulai)
            ->first();

        if ($existingShift) {
            // Update existing schedule
            $existingShift->shift_id = $shiftId;
            $existingShift->save();

            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data shift jadwal karyawan berhasil diperbarui.'), Response::HTTP_OK);
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }
    }

    public function exportJadwalKaryawan(Request $request)
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

    /* ============================= Addon ============================= */
    private function generateDateRange($start_date, $end_date)
    {
        $dates = [];
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }
    /* ============================= Addon ============================= */
}
