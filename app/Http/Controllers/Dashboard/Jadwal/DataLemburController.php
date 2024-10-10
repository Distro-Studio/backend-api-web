<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\Lembur;
use App\Models\NonShift;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\LemburJadwalExport;
use App\Http\Requests\StoreLemburKaryawanRequest;
use App\Http\Requests\UpdateLemburKaryawanRequest;
use App\Http\Resources\Dashboard\Jadwal\LemburJadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataLemburController extends Controller
{
    public function getJadwalPengajuanLembur($userId)
    {
        try {
            if (!Gate::allows('view tukarJadwal')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $user = User::where('id', $userId)
                ->where('nama', '!=', 'Super Admin')
                ->where('status_aktif', 2)
                ->first(); // Menggunakan first() untuk mengambil satu record

            if (!$user) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Karyawan pengajuan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Memeriksa jenis_karyawan melalui relasi unit_kerja
            $jenisKaryawan = $user->data_karyawans->unit_kerjas->jenis_karyawan ?? null;
            $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

            if ($jenisKaryawan === 1) {
                // Logika untuk karyawan shift
                $jadwal = Jadwal::with('shifts')
                    ->where('user_id', $userId)
                    ->where('shift_id', '!=', 0)
                    ->where('tgl_selesai', '>=', $today)
                    ->get();

                if ($jadwal->isEmpty()) {
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal karyawan pengajuan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
                }

                $start_date = $jadwal->min('tgl_mulai');
                $end_date = $jadwal->max('tgl_selesai');
                $date_range = $this->generateDateRange($start_date, $end_date);
                $user_schedule_array = $this->formatSchedules($jadwal, $date_range);
            } else {
                // Logika untuk karyawan non-shift
                $jadwal = NonShift::first(); // Mengambil jadwal dari tabel non_shifts (gunakan first() karena hanya ada satu jadwal di tabel ini)

                if (!$jadwal) {
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal non-shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
                }

                // Format jadwal non-shift menjadi array
                $user_schedule_array = [
                    [
                        'id' => $jadwal->id,
                        'nama' => $jadwal->nama,
                        'jam_from' => $jadwal->jam_from,
                        'jam_to' => $jadwal->jam_to,
                        'created_at' => $jadwal->created_at,
                        'updated_at' => $jadwal->updated_at
                    ]
                ];
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail karyawan dan jadwalnya berhasil ditampilkan.",
                'data' => [
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
                    'list_jadwal' => $user_schedule_array
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Lembur | - Error saat get jadwal pengajuan karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view lemburKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Per page
            $limit = $request->input('limit', 10); // Default per page is 10

            $lembur = Lembur::query()->orderBy('created_at', 'desc');

            // Ambil semua filter dari request body
            $filters = $request->all();

            // Filter
            if (isset($filters['unit_kerja'])) {
                $namaUnitKerja = $filters['unit_kerja'];
                $lembur->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('id', $namaUnitKerja);
                    } else {
                        $query->where('id', '=', $namaUnitKerja);
                    }
                });
            }

            if (isset($filters['jabatan'])) {
                $namaJabatan = $filters['jabatan'];
                $lembur->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                    if (is_array($namaJabatan)) {
                        $query->whereIn('id', $namaJabatan);
                    } else {
                        $query->where('id', '=', $namaJabatan);
                    }
                });
            }

            if (isset($filters['status_karyawan'])) {
                $statusKaryawan = $filters['status_karyawan'];
                $lembur->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                    $lembur->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                        foreach ($masaKerja as $masa) {
                            $bulan = $masa * 12;
                            $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                        }
                    });
                } else {
                    $bulan = $masaKerja * 12;
                    $lembur->whereHas('users.data_karyawans', function ($query) use ($bulan) {
                        $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    });
                }
            }

            if (isset($filters['status_aktif'])) {
                $statusAktif = $filters['status_aktif'];
                $lembur->whereHas('users', function ($query) use ($statusAktif) {
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
                    $lembur->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
                        $query->whereIn('tgl_masuk', $convertedDates);
                    });
                } else {
                    $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                    $lembur->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
                        $query->where('tgl_masuk', $convertedDate);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $namaAgama = $filters['agama'];
                $lembur->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                    $lembur->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    });
                } else {
                    $lembur->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    });
                }
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $lembur->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                    $lembur->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    });
                } else {
                    $lembur->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    });
                }
            }

            // Search
            if (isset($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $lembur->where(function ($query) use ($searchTerm) {
                    $query->whereHas('users', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                        $query->where('nik', 'like', $searchTerm);
                    });
                });
            }

            // Paginate
            if ($limit == 0) {
                $dataLembur = $lembur->get();
                $paginationData = null;
            } else {
                // Pastikan limit adalah integer
                $limit = is_numeric($limit) ? (int)$limit : 10;
                $dataLembur = $lembur->paginate($limit);

                $paginationData = [
                    'links' => [
                        'first' => $dataLembur->url(1),
                        'last' => $dataLembur->url($dataLembur->lastPage()),
                        'prev' => $dataLembur->previousPageUrl(),
                        'next' => $dataLembur->nextPageUrl(),
                    ],
                    'meta' => [
                        'current_page' => $dataLembur->currentPage(),
                        'last_page' => $dataLembur->lastPage(),
                        'per_page' => $dataLembur->perPage(),
                        'total' => $dataLembur->total(),
                    ]
                ];
            }

            if ($dataLembur->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $nonShiftData = NonShift::find(1);

            $formattedData = $dataLembur->map(function ($lembur) use ($nonShiftData) {
                $unitKerja = $lembur->users->data_karyawans->unit_kerjas ?? null;
                $jenisKaryawan = $unitKerja->jenis_karyawan ?? null;

                $jadwalData = null;
                $jadwalNonShiftData = null;

                if ($jenisKaryawan === 1) { // Shift
                    $jadwalData = $lembur->jadwals ? [
                        'id' => $lembur->jadwals->id,
                        'user_id' => $lembur->jadwals->user_id,
                        'tgl_mulai' => $lembur->jadwals->tgl_mulai,
                        'tgl_selesai' => $lembur->jadwals->tgl_selesai,
                        'shift' => $lembur->jadwals->shifts,
                        'created_at' => $lembur->jadwals->created_at,
                        'updated_at' => $lembur->jadwals->updated_at
                    ] : null;
                } elseif ($jenisKaryawan === 0) { // Non-shift
                    $jadwalNonShiftData = $nonShiftData ? [
                        'id' => $nonShiftData->id,
                        'nama' => $nonShiftData->nama,
                        'jam_from' => $nonShiftData->jam_from,
                        'jam_to' => $nonShiftData->jam_to,
                        'deleted_at' => $nonShiftData->deleted_at,
                        'created_at' => $nonShiftData->created_at,
                        'updated_at' => $nonShiftData->updated_at
                    ] : null;
                }

                return [
                    'id' => $lembur->id,
                    'user' => [
                        'id' => $lembur->users->id,
                        'nama' => $lembur->users->nama,
                        'username' => $lembur->users->username,
                        'email_verified_at' => $lembur->users->email_verified_at,
                        'data_karyawan_id' => $lembur->users->data_karyawan_id,
                        'foto_profil' => $lembur->users->foto_profil,
                        'data_completion_step' => $lembur->users->data_completion_step,
                        'status_aktif' => $lembur->users->status_aktif,
                        'created_at' => $lembur->users->created_at,
                        'updated_at' => $lembur->users->updated_at
                    ],
                    'unit_kerja' => $unitKerja ? [
                        'id' => $unitKerja->id,
                        'nama_unit' => $unitKerja->nama_unit,
                        'jenis_karyawan' => $unitKerja->jenis_karyawan,
                    ] : null,
                    'jadwal_shift' => $jadwalData,
                    'jadwal_non_shift' => $jadwalNonShiftData,
                    'tgl_pengajuan' => $lembur->tgl_pengajuan,
                    'durasi' => $lembur->durasi,
                    'catatan' => $lembur->catatan,
                    'created_at' => $lembur->created_at,
                    'updated_at' => $lembur->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data lembur karyawan berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Lembur | - Error saat menampilkan lembur karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreLemburKaryawanRequest $request)
    {
        try {
            if (!Gate::allows('create lemburKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();
            $verifikatorId = Auth::id();

            $user = User::find($data['user_id']);
            if (!$user) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Karyawan tidak valid.'), Response::HTTP_NOT_FOUND);
            }

            $jenisKaryawan = $user->data_karyawans->unit_kerjas->jenis_karyawan ?? null;

            if ($jenisKaryawan === 1) { // Jika jenis karyawan shift
                $jadwal = Jadwal::find($data['jadwal_id']);
                if (!$jadwal) {
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan.'), Response::HTTP_NOT_FOUND);
                }

                $data['tgl_pengajuan'] = Carbon::parse($jadwal->tgl_mulai)->format('d-m-Y');
            } elseif ($jenisKaryawan === 0) { // Jika jenis karyawan non-shift
                if (empty($data['tgl_pengajuan'])) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal pengajuan harus diisi untuk karyawan non-shift.'), Response::HTTP_BAD_REQUEST);
                }
                // Validasi tanggal mulai tidak boleh hari ini, atau hari yang sudah terlewat
                $tgl_mulai = Carbon::createFromFormat('d-m-Y', $data['tgl_pengajuan'])->startOfDay();
                $today = Carbon::today();
                if ($tgl_mulai->lt($today)) {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal pengajuan tidak boleh hari yang sudah terlewat.'), Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Jenis karyawan tidak valid.'), Response::HTTP_BAD_REQUEST);
            }

            $timeParts = explode(':', $data['durasi']);
            $hours = (int)$timeParts[0];
            $minutes = (int)$timeParts[1];
            $seconds = (int)$timeParts[2];
            $data['durasi'] = ($hours * 3600) + ($minutes * 60) + $seconds;
            $data['status_lembur_id'] = 1;

            $dataLembur = Lembur::create($data);
            $successMessage = "Lembur karyawan '{$dataLembur->users->nama}' berhasil ditambahkan.";

            // Buat dan simpan notifikasi
            $this->createNotifikasiLembur($dataLembur);

            return response()->json(new LemburJadwalResource(Response::HTTP_OK, $successMessage, $dataLembur), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Lembur | - Error saat menimpan data lembur karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            if (!Gate::allows('view lemburKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataLembur = Lembur::find($id);
            if (!$dataLembur) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Konversi durasi
            $durasi = null;
            if ($dataLembur->durasi !== null) {
                $timeString = RandomHelper::convertToTimeString($dataLembur->durasi);
                $durasi = RandomHelper::convertTimeStringToSeconds($timeString);
            }

            // Format data untuk respon
            $formattedData = [
                'id' => $dataLembur->id,
                'user' => [
                    'id' => $dataLembur->users->id,
                    'nama' => $dataLembur->users->nama,
                    'username' => $dataLembur->users->username,
                    'email_verified_at' => $dataLembur->users->email_verified_at,
                    'data_karyawan_id' => $dataLembur->users->data_karyawan_id,
                    'foto_profil' => $dataLembur->users->foto_profil,
                    'data_completion_step' => $dataLembur->users->data_completion_step,
                    'status_aktif' => $dataLembur->users->status_aktif,
                    'created_at' => $dataLembur->users->created_at,
                    'updated_at' => $dataLembur->users->updated_at
                ],
                'jadwal' => $dataLembur->jadwals,
                'tgl_pengajuan' => $dataLembur->tgl_pengajuan,
                'durasi' => $durasi,
                'catatan' => $dataLembur->catatan,
                'created_at' => $dataLembur->created_at,
                'updated_at' => $dataLembur->updated_at
            ];

            $message = "Detail lembur karyawan '{$dataLembur->users->nama}' berhasil ditampilkan.";
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => $message,
                'data' => $formattedData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Lembur | - Error saat menampilkan detail data lembur karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // public function update(UpdateLemburKaryawanRequest $request, $id)
    // {
    //     if (!Gate::allows('edit lemburKaryawan')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $data = $request->validated();
    //     $dataLembur = Lembur::find($id);
    //     if (!$dataLembur) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    //     }

    //     $dataLembur->update($data);
    //     $message = "Lembur karyawan '{$dataLembur->users->nama}' berhasil diperbarui.";

    //     return response()->json(new LemburJadwalResource(Response::HTTP_OK, $message, $dataLembur), Response::HTTP_OK);
    // }

    public function exportJadwalLembur()
    {
        try {
            if (!Gate::allows('export lemburKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataLembur = Lembur::all();
            if ($dataLembur->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data lembur karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new LemburJadwalExport(), 'lembur-karyawan.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Lembur | - Error saat export data lembur karyawan: ' . $e->getMessage());
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
            Log::error('| Lembur | - Error saat generate date range untuk lembur karyawan: ' . $e->getMessage());
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
            Log::error('| Lembur | - Error saat membuat format schedules lembur karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiLembur($dataLembur)
    {
        try {
            $timeString = RandomHelper::convertToTimeString($dataLembur->durasi);
            $durasi = RandomHelper::convertTimeStringToSeconds($timeString);
            $durasi_jam = RandomHelper::convertToHoursMinutes($durasi);

            // Pesan untuk karyawan terkait
            $messageForUser = "{$dataLembur->users->nama}, Anda mendapatkan pengajuan lembur dengan durasi {$durasi_jam}.";

            // Pesan untuk Super Admin
            $messageForSuperAdmin = "Notifikasi untuk Super Admin: {$dataLembur->users->nama} mengajukan lembur dengan durasi {$durasi_jam}.";

            // Daftar userId yang akan menerima notifikasi (karyawan dan Super Admin)
            $userIds = [$dataLembur->user_id, 1];
            foreach ($userIds as $userId) {
                // Tentukan pesan berdasarkan user
                $message = $userId === 1 ? $messageForSuperAdmin : $messageForUser;

                // Buat notifikasi untuk user atau Super Admin
                Notifikasi::create([
                    'kategori_notifikasi_id' => 3,
                    'user_id' => $userId,
                    'message' => $message,
                    'is_read' => false,
                    'is_verifikasi' => true,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('| Lembur | - Error saat membuat notifikasi lembur karyawan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
