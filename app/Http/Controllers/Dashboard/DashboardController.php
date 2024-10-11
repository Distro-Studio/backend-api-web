<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\Jadwal;
use App\Models\Lembur;
use App\Models\Jabatan;
use App\Models\Presensi;
use App\Models\HariLibur;
use App\Models\Kompetensi;
use App\Models\DataKaryawan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DashboardController extends Controller
{
    // public function calculatedHeader()
    // {
    //     $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

    //     // Retrieve the ID for each category
    //     $kategoriCutiId = DB::table('kategori_presensis')->where('label', 'Cuti')->value('id');
    //     $kategoriAbsenId = DB::table('kategori_presensis')->where('label', 'Absen')->value('id');

    //     // Calculate total number of employees excluding the super admin
    //     $calculatedKaryawan = DataKaryawan::where('id', '!=', 1)->count();

    //     // Hitung karyawan shift yang libur berdasarkan user_id
    //     $countLiburShift = Jadwal::where('shift_id', 0)
    //         ->whereDate('tgl_mulai', '<=', $today)
    //         ->whereDate('tgl_selesai', '>=', $today)
    //         ->count('user_id');

    //     // Periksa apakah hari ini adalah hari libur
    //     $isHariLibur = HariLibur::whereDate('tanggal', $today)->exists();

    //     // Hitung karyawan non-shift yang libur berdasarkan hari libur
    //     $countLiburNonShift = DataKaryawan::whereHas('unit_kerjas', function ($query) {
    //         $query->where('jenis_karyawan', 0);
    //     })->when($isHariLibur, function ($query) {
    //         return $query->distinct('id')->count('id');  // Hitung berdasarkan user_id
    //     }, function ($query) {
    //         return 0;
    //     });

    //     // Total karyawan yang libur
    //     $countLibur = $countLiburShift + $countLiburNonShift;

    //     // Calculate the number of employees on leave today
    //     $countCuti = Presensi::where('kategori_presensi_id', $kategoriCutiId)
    //         ->whereDate('jam_masuk', $today)
    //         ->count('user_id');

    //     // Calculate the number of employees absent today
    //     $countAbsen = Presensi::where('kategori_presensi_id', $kategoriAbsenId)
    //         ->whereDate('jam_masuk', $today)
    //         ->count('user_id');

    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => "Header calculation successful.",
    //         'data' => [
    //             'total_karyawan' => $calculatedKaryawan,
    //             'jumlah_libur' => $countLibur,
    //             'jumlah_cuti' => $countCuti,
    //             'jumlah_absen' => $countAbsen,
    //         ]
    //     ], Response::HTTP_OK);
    // }

    public function calculatedHeader()
    {
        $today = Carbon::today('Asia/Jakarta')->format('d-m-Y');

        $kategoriAbsenId = DB::table('kategori_presensis')->where('label', 'Absen')->value('id');

        // Calculate total number of employees excluding the super admin
        $calculatedKaryawan = DataKaryawan::where('id', '!=', 1)->count();

        // Hitung karyawan shift yang libur berdasarkan user_id
        $countLiburShift = Jadwal::where('shift_id', 0)
            ->whereDate('tgl_mulai', '<=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->whereDate('tgl_selesai', '>=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        // Periksa apakah hari ini adalah hari libur
        $isHariLibur = HariLibur::whereDate('tanggal', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))->exists();
        $countLiburNonShift = DataKaryawan::whereHas('unit_kerjas', function ($query) {
            $query->where('jenis_karyawan', 0);
        })->when($isHariLibur, function ($query) {
            return $query->distinct('id')->count('id');  // Hitung berdasarkan user_id
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

        // Calculate the number of employees absent today (presensi kategori absen)
        $countAbsen = Presensi::where('kategori_presensi_id', $kategoriAbsenId)
            ->whereDate('jam_masuk', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Header calculation successful.",
            'data' => [
                'total_karyawan' => $calculatedKaryawan,
                'jumlah_libur' => $countLibur,
                'jumlah_cuti' => $countCuti,
                'jumlah_absen' => $countAbsen,
            ]
        ], Response::HTTP_OK);
    }

    public function calculatedKelamin()
    {
        // Retrieve all employees excluding the super admin
        $totalEmployees = DataKaryawan::where('id', '!=', 1)->count();

        if ($totalEmployees == 0) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "Tidak ada data karyawan yang tersedia.",
                'data' => [
                    'persen_laki_laki' => 0,
                    'persen_perempuan' => 0,
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Retrieve the count of male and female employees
        $countMale = DataKaryawan::where('jenis_kelamin', true)->count();
        $countFemale = DataKaryawan::where('jenis_kelamin', false)->count();

        // Calculate the percentage
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

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Kalkulasi jenis kelamin berhasil.",
            'data' => [
                'persen_laki_laki' => round($percentMale, 0),
                'persen_perempuan' => round($percentFemale, 0),
            ]
        ], Response::HTTP_OK);
    }

    public function calculatedJabatan()
    {
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
            $countKaryawan = DataKaryawan::where('jabatan_id', $jabatan->id)->count();
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
            $countKaryawan = DataKaryawan::where('kompetensi_id', $kompetensi->id)->count();
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
        // Retrieve all statuses
        $statuses = DB::table('status_karyawans')->get();

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
            $countKaryawan = DataKaryawan::where('status_karyawan_id', $status->id)->count();
            $result[] = [
                'status_karyawan' => $status->label,
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
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        // Retrieve lembur entries for today based on tgl_mulai from jadwals
        $dataLembur = Lembur::with(['users.data_karyawans.unit_kerjas', 'jadwals' => function ($query) use ($today) {
            $query->whereDate('tgl_mulai', '<=', $today)
                ->whereDate('tgl_selesai', '>=', $today);
        }, 'kategori_kompensasis', 'status_lemburs'])
            ->whereHas('jadwals', function ($query) use ($today) {
                $query->whereDate('tgl_mulai', '<=', $today)
                    ->whereDate('tgl_selesai', '>=', $today);
            })
            ->get();

        if ($dataLembur->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data lembur untuk hari ini.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataLembur->map(function ($lembur) {
            return [
                'id' => $lembur->users->data_karyawan_id,
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
                'unit_kerja' => $lembur->users->data_karyawans->unit_kerjas,
                'jadwal' => [
                    'id' => $lembur->jadwals->id,
                    'tgl_mulai' => $lembur->jadwals->tgl_mulai,
                    'tgl_selesai' => $lembur->jadwals->tgl_selesai,
                    'shift_id' => $lembur->jadwals->shift_id,
                    'created_at' => $lembur->jadwals->created_at,
                    'updated_at' => $lembur->jadwals->updated_at
                ],
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
