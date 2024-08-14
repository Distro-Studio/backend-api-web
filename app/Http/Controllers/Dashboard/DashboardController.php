<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Jadwal;
use App\Models\Lembur;
use App\Models\Jabatan;
use App\Models\Presensi;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DashboardController extends Controller
{
    public function calculatedHeader()
    {
        $today = Carbon::today()->format('Y-m-d');

        // Retrieve the ID for each category
        $kategoriCutiId = DB::table('kategori_presensis')->where('label', 'Cuti')->value('id');
        $kategoriAbsenId = DB::table('kategori_presensis')->where('label', 'Absen')->value('id');

        // Calculate total number of employees excluding the super admin
        $calculatedKaryawan = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')->count();

        // Konversi tanggal tgl_mulai dan tgl_selesai menjadi format yang sesuai untuk perbandingan
        $jadwalLibur = Jadwal::where('shift_id', 0)->get();

        $countLibur = $jadwalLibur->filter(function ($jadwal) use ($today) {
            $tglMulai = Carbon::parse(RandomHelper::convertToDateString($jadwal->tgl_mulai))->format('Y-m-d');
            $tglSelesai = Carbon::parse(RandomHelper::convertToDateString($jadwal->tgl_selesai))->format('Y-m-d');
            return $tglMulai <= $today && $tglSelesai >= $today;
        })->count();

        // Calculate the number of employees on leave today
        $countCuti = Presensi::where('kategori_presensi_id', $kategoriCutiId)
            ->whereDate('jam_masuk', $today)
            ->count('user_id');

        // Calculate the number of employees absent today
        $countAbsen = Presensi::where('kategori_presensi_id', $kategoriAbsenId)
            ->whereDate('jam_masuk', $today)
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
        $totalEmployees = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')->count();

        if ($totalEmployees == 0) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Tidak ada data karyawan yang tersedia.",
                'data' => [
                    'persen_laki_laki' => 0,
                    'persen_perempuan' => 0,
                ]
            ], Response::HTTP_OK);
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

    public function calculatedKepegawaian()
    {
        // Retrieve all statuses
        $statuses = DB::table('status_karyawans')->get();

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
        $today = Carbon::today()->format('Y-m-d');

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
                'tgl_pengajuan' => $lembur->tgl_pengajuan,
                'kompensasi_lembur_id' => $lembur->kategori_kompensasis,
                'durasi' => $lembur->durasi,
                'catatan' => $lembur->catatan,
                'status_lembur_id' => $lembur->status_lemburs,
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
