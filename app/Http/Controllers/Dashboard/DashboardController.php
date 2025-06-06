<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\Jadwal;
use App\Models\Lembur;
use App\Models\Jabatan;
use App\Models\HariLibur;
use App\Models\Kompetensi;
use App\Models\DataKaryawan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\StatusKaryawan;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function calculatedHeader()
    {
        if (!Gate::allows('view dashboardKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $today = Carbon::today('Asia/Jakarta')->format('d-m-Y');

        // Calculate total number of employees excluding the super admin
        $calculatedKaryawanAktif = DataKaryawan::whereHas('users', function ($query) {
            $query->where('status_aktif', 2);
        })->where('id', '!=', 1)->count();

        // Calculate the number of fulltime, parttime, and outsourcing employees
        $calculatedKaryawanFulltime = DataKaryawan::whereHas('status_karyawans', function ($query) {
            $query->where('kategori_status_id', 1)
                ->whereNotNull('kategori_status_id');
        })
            ->whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
            ->where('id', '!=', 1)->count();

        $calculatedKaryawanParttime = DataKaryawan::whereHas('status_karyawans', function ($query) {
            $query->where('kategori_status_id', 2)
                ->whereNotNull('kategori_status_id');
        })
            ->whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
            ->where('id', '!=', 1)->count();

        $calculatedKaryawanOutsourcing = DataKaryawan::whereHas('status_karyawans', function ($query) {
            $query->where('kategori_status_id', 3)
                ->whereNotNull('kategori_status_id');
        })
            ->whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
            ->where('id', '!=', 1)->count();

        // Hitung karyawan shift yang libur berdasarkan user_id
        $countLiburShift = Jadwal::where('shift_id', 0)
            ->whereDate('tgl_mulai', '<=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->whereDate('tgl_selesai', '>=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        // Periksa apakah hari ini adalah hari libur
        $isHariLibur = HariLibur::whereDate('tanggal', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))->exists();
        $countLiburNonShift = DataKaryawan::whereHas('unit_kerjas', function ($query) {
            $query->where('jenis_karyawan', 0);
        })
            ->whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
            ->when($isHariLibur, function ($query) {
                return $query->distinct('id')->count('user_id');  // Hitung berdasarkan user_id
            }, function ($query) {
                return 0;
            });

        // Total karyawan yang libur
        $countLibur = $countLiburShift + $countLiburNonShift;

        // Calculate the number of employees on leave today using the Cuti table
        $countCuti = Cuti::where('status_cuti_id', 4)
            ->whereDate(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->whereDate(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Header calculation successful.",
            'data' => [
                'total_karyawan' => $calculatedKaryawanAktif,
                'karyawan_fulltime' => $calculatedKaryawanFulltime,
                'karyawan_parttime' => $calculatedKaryawanParttime,
                'karyawan_outsourcing' => $calculatedKaryawanOutsourcing,
                'jumlah_libur' => $countLibur,
                'jumlah_cuti' => $countCuti,
            ]
        ], Response::HTTP_OK);
    }

    public function calculatedKelamin()
    {
        if (!Gate::allows('view dashboardKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Retrieve all employees excluding the super admin
        $totalEmployees = DataKaryawan::whereHas('users', function ($query) {
            $query->where('status_aktif', 2);
        })->where('id', '!=', 1)->count();
        if ($totalEmployees == 0) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "Tidak ada data karyawan yang tersedia.",
                'data' => [
                    'akumulatif_karyawan' => [
                        [
                            'persen_laki_laki' => 0,
                            'persen_perempuan' => 0,
                        ],
                        [
                            'jumlah_laki_laki' => 0,
                            'jumlah_perempuan' => 0,
                        ]
                    ],
                    'status_karyawan' => [
                        'label' => null,
                        'jumlah_laki_laki' => 0,
                        'jumlah_perempuan' => 0,
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Prepare the akumulatif karyawan data (total percentages)
        $countMale = DataKaryawan::whereHas('users', function ($query) {
            $query->where('status_aktif', 2);
        })
            ->where('jenis_kelamin', 1)
            ->where('id', '!=', 1)
            ->count();

        $countFemale = DataKaryawan::whereHas('users', function ($query) {
            $query->where('status_aktif', 2);
        })
            ->where('jenis_kelamin', 0)
            ->where('id', '!=', 1)
            ->count();

        $percentMale = ($countMale / $totalEmployees) * 100;
        $percentFemale = ($countFemale / $totalEmployees) * 100;

        // Ensure the total percentage does not exceed 100%
        if ($percentMale + $percentFemale > 100) {
            if ($percentMale > $percentFemale) {
                $percentMale = 100 - $percentFemale;
            } else {
                $percentFemale = 100 - $percentMale;
            }
        }

        // Retrieve all statuses where kategori_status_id is not null
        $statuses = StatusKaryawan::whereNotNull('kategori_status_id')->get();
        if ($statuses->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "Tidak ada data status karyawan yang tersedia.",
            ], Response::HTTP_NOT_FOUND);
        }

        $statusData = [];

        // Iterate over each status and count the number of employees with that status
        foreach ($statuses as $status) {
            $countLakiLaki = DataKaryawan::whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
                ->where('id', '!=', 1)
                ->where('status_karyawan_id', $status->id)
                ->where('jenis_kelamin', 1)
                ->count();

            $countPerempuan = DataKaryawan::whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
                ->where('id', '!=', 1)
                ->where('status_karyawan_id', $status->id)
                ->where('jenis_kelamin', 0)
                ->count();

            $statusData[] = [
                'label' => $status->label,
                'jumlah_laki_laki' => $countLakiLaki,
                'jumlah_perempuan' => $countPerempuan,
            ];
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Kalkulasi jumlah jenis kelamin berdasarkan status karyawan berhasil.",
            'data' => [
                'akumulatif_karyawan' => [
                    [
                        'persen_laki_laki' => round($percentMale, 0),
                        'persen_perempuan' => round($percentFemale, 0),
                    ],
                    [
                        'jumlah_laki_laki' => $countMale,
                        'jumlah_perempuan' => $countFemale,
                    ]
                ],
                'status_karyawan' => $statusData
            ]
        ], Response::HTTP_OK);
    }

    public function calculatedJabatan()
    {
        if (!Gate::allows('view dashboardKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Retrieve all positions
        $jabatans = Jabatan::all();

        if ($jabatans->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "Tidak ada data jabatan yang tersedia.",
            ], Response::HTTP_NOT_FOUND);
        }

        // Initialize an array to hold the results
        $result = [];

        // Iterate over each position and count the number of employees holding that position
        foreach ($jabatans as $jabatan) {
            $countKaryawan = DataKaryawan::whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })->where('jabatan_id', $jabatan->id)->count();
            $result[] = [
                'nama_jabatan' => $jabatan->nama_jabatan,
                'jumlah_karyawan' => $countKaryawan,
            ];
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Kalkulasi jabatan berhasil.",
            'data' => $result
        ], Response::HTTP_OK);
    }

    public function calculatedKompetensi()
    {
        if (!Gate::allows('view dashboardKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Retrieve all positions
        $kompetensis = Kompetensi::all();

        if ($kompetensis->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "Tidak ada data kompetensi yang tersedia.",
            ], Response::HTTP_NOT_FOUND);
        }

        // Initialize an array to hold the results
        $result = [];

        // Iterate over each position and count the number of employees holding that position
        foreach ($kompetensis as $kompetensi) {
            $countKaryawan = DataKaryawan::whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })->where('kompetensi_id', $kompetensi->id)->count();
            $result[] = [
                'nama_kompetensi' => $kompetensi->nama_kompetensi,
                'jumlah_karyawan' => $countKaryawan,
            ];
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Kalkulasi profesi berhasil.",
            'data' => $result
        ], Response::HTTP_OK);
    }

    public function calculatedKepegawaian()
    {
        if (!Gate::allows('view dashboardKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Retrieve all statuses
        $statuses = StatusKaryawan::whereNotNull('kategori_status_id')->get();
        if ($statuses->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "Tidak ada data status karyawan yang tersedia.",
            ], Response::HTTP_NOT_FOUND);
        }

        // Initialize an array to hold the results
        $result = [];

        // Iterate over each status and count the number of employees with that status
        foreach ($statuses as $status) {
            $countKaryawan = DataKaryawan::whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
                ->where('id', '!=', 1)
                ->where('status_karyawan_id', $status->id)
                ->count();
            $result[] = [
                'status_karyawan' => [
                    'id' => $status->id,
                    'label' => $status->label,
                    'kategori_status' => $status->kategori_status ?? null,
                    'created_at' => $status->created_at,
                    'updated_at' => $status->updated_at
                ],
                'jumlah_karyawan' => $countKaryawan,
            ];
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Kalkulasi status karyawan berhasil.",
            'data' => $result
        ], Response::HTTP_OK);
    }

    public function getLemburToday()
    {
        if (!Gate::allows('view dashboardKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $today = Carbon::today('Asia/Jakarta')->format('d-m-Y');
        // dd($today);

        $dataLembur = Lembur::where('tgl_pengajuan', $today)
            ->get();
        // dd($dataLembur);
        if ($dataLembur->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data lembur untuk hari ini.'), Response::HTTP_NOT_FOUND);
        }

        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = $dataLembur->map(function ($lembur) use ($baseUrl) {
            return [
                'id' => $lembur->users->data_karyawan_id,
                'user' => [
                    'id' => $lembur->users->id,
                    'nama' => $lembur->users->nama,
                    'username' => $lembur->users->username,
                    'email_verified_at' => $lembur->users->email_verified_at,
                    'data_karyawan_id' => $lembur->users->data_karyawan_id,
                    'foto_profil' => $lembur->users->foto_profiles ? [
                        'id' => $lembur->users->foto_profiles->id,
                        'user_id' => $lembur->users->foto_profiles->user_id,
                        'file_id' => $lembur->users->foto_profiles->file_id,
                        'nama' => $lembur->users->foto_profiles->nama,
                        'nama_file' => $lembur->users->foto_profiles->nama_file,
                        'path' => $baseUrl . $lembur->users->foto_profiles->path,
                        'ext' => $lembur->users->foto_profiles->ext,
                        'size' => $lembur->users->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $lembur->users->data_completion_step,
                    'status_aktif' => $lembur->users->status_aktif,
                    'created_at' => $lembur->users->created_at,
                    'updated_at' => $lembur->users->updated_at
                ],
                'unit_kerja' => $lembur->users->data_karyawans->unit_kerjas,
                'jadwal' => $lembur->jadwals ? [
                    'id' => $lembur->jadwals->id,
                    'tgl_mulai' => $lembur->jadwals->tgl_mulai,
                    'tgl_selesai' => $lembur->jadwals->tgl_selesai,
                    'shift_id' => $lembur->jadwals->shift_id,
                    'created_at' => $lembur->jadwals->created_at,
                    'updated_at' => $lembur->jadwals->updated_at
                ] : null,
                'durasi' => $lembur->durasi,
                'catatan' => $lembur->catatan,
                'created_at' => $lembur->created_at,
                'updated_at' => $lembur->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data lembur untuk hari ini berhasil ditampilkan.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }
}
