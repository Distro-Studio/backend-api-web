<?php

namespace App\Http\Controllers\Dashboard\Karyawan\DetailKaryawan;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\User;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\Penilaian;
use App\Models\TrackRecord;
use App\Models\TukarJadwal;
use App\Models\DataKaryawan;
use App\Models\PesertaDiklat;
use Illuminate\Http\Response;
use App\Models\RiwayatPerubahan;
use App\Models\TransferKaryawan;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class Karyawan_DetailController extends Controller
{
    public function getDataPresensi($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Mendapatkan data presensi karyawan berdasarkan data_karyawan_id dan filter hari ini
            $presensi = Presensi::with([
                'users',
                'jadwals.shifts',
                'data_karyawans.unit_kerjas',
                'kategori_presensis'
            ])
                ->where('data_karyawan_id', $data_karyawan_id)
                // ->whereDate('jam_masuk', Carbon::today())
                ->first();

            if (!$presensi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data presensi karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Ambil semua presensi bulan ini dari karyawan yang sama
            $presensiBulanIni = Presensi::where('data_karyawan_id', $data_karyawan_id)
                ->whereYear('jam_masuk', Carbon::now()->year)
                ->whereMonth('jam_masuk', Carbon::now()->month)
                ->orderBy('jam_masuk')
                ->get();

            // Memformat aktivitas presensi
            $aktivitasPresensi = [];
            foreach ($presensiBulanIni as $presensi) {
                if ($presensi->jam_masuk) {
                    $aktivitasPresensi[] = [
                        'presensi' => 'Masuk',
                        'tanggal' => Carbon::parse($presensi->jam_masuk)->format('d-m-Y'),
                        'jam' => Carbon::parse($presensi->jam_masuk)->format('H:i:s'),
                    ];
                }
                if ($presensi->jam_keluar) {
                    $aktivitasPresensi[] = [
                        'presensi' => 'Keluar',
                        'tanggal' => Carbon::parse($presensi->jam_keluar)->format('d-m-Y'),
                        'jam' => Carbon::parse($presensi->jam_keluar)->format('H:i:s'),
                    ];
                }
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail data presensi karyawan '{$presensi->users->nama}' berhasil ditampilkan.",
                'data' => [
                    'id' => $presensi->id,
                    'user' => $presensi->users,
                    'unit_kerja' => $presensi->data_karyawans->unit_kerjas,
                    'list_presensi' => $aktivitasPresensi
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataPresensi: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataJadwal($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Cari data karyawan berdasarkan data_karyawan_id
            $karyawan = DataKaryawan::with(['users.jadwals.shifts', 'unit_kerjas'])
                ->where('id', '!=', 1)
                ->find($data_karyawan_id);

            if (!$karyawan) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Ambil data user yang terkait dengan karyawan
            $user = $karyawan->users;

            if (!$user) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'User tidak ditemukan untuk data karyawan ini.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Format data jadwal
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

            if (empty($user_schedule_array)) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Jadwal karyawan tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Format respons
            $formattedData = [
                'id' => $karyawan->id,
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
                'unit_kerja' => $karyawan->unit_kerjas,
                'list_jadwal' => $user_schedule_array,
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail jadwal karyawan '{$user->nama}' berhasil ditampilkan.",
                'data' => $formattedData,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataJadwal: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataRekamJejak($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Cari karyawan berdasarkan data_karyawan_id
            $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);
            if (!$karyawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil user dari karyawan
            $user = $karyawan->users;

            // Ambil semua rekam jejak dari user_id dengan kategori_record_id 2 dan 3
            $rekamJejakList = TrackRecord::where('user_id', $user->id)
                ->whereIn('kategori_record_id', [2, 3])
                ->get();

            // Ambil semua data perubahan dengan kategori_record_id 1
            $dataPerubahanList = TrackRecord::with(['users', 'kategori_track_records'])
                ->where('user_id', $user->id)
                ->where('kategori_record_id', 1)
                ->get();

            // Format data rekam jejak kategori 2 dan 3
            $formattedRekamJejak = $rekamJejakList->map(function ($item) {
                $user = $item->users;
                $transfer = TransferKaryawan::where('user_id', $user->id)->first();

                return [
                    'id' => $item->id,
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
                    'kategori_rekam_jejak' => $item->kategori_track_records,
                    'content' => [
                        'kategori_transfer' => $transfer->kategori_transfer_karyawans,
                        'tgl_masuk' => $item->tgl_masuk,
                        'tgl_keluar' => $item->tgl_keluar,
                        'tgl_mulai' => $transfer->tgl_mulai,
                        'unit_kerja_asal' => $transfer->unit_kerja_asals,
                        'unit_kerja_tujuan' => $transfer->unit_kerja_tujuans,
                        'jabatan_asal' => $transfer->jabatan_asals,
                        'jabatan_tujuan' => $transfer->jabatan_tujuans,
                        'kelompok_gaji_asal' => $transfer->kelompok_gaji_asals,
                        'kelompok_gaji_tujuan' => $transfer->kelompok_gaji_tujuans,
                        'role_asal' => $transfer->role_asals,
                        'role_tujuan' => $transfer->role_tujuans,
                        'alasan' => $transfer->alasan,
                        'dokumen' => env('STORAGE_SERVER_DOMAIN') . $transfer->dokumen,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at
                    ],
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ];
            });

            // Format data perubahan kategori 1
            $formattedDataPerubahan = [];
            foreach ($dataPerubahanList as $data_perubahan) {
                $relasiUser = $data_perubahan->users;
                $relasiKategori = $data_perubahan->kategori_track_records;
                $perubahanDataList = RiwayatPerubahan::where('data_karyawan_id', $user->id)->get();

                foreach ($perubahanDataList as $perubahanData) {
                    $formattedDataPerubahan[] = [
                        'id' => $data_perubahan->id,
                        'user' => [
                            'id' => $relasiUser->id,
                            'nama' => $relasiUser->nama,
                            'username' => $relasiUser->username,
                            'email_verified_at' => $relasiUser->email_verified_at,
                            'data_karyawan_id' => $relasiUser->data_karyawan_id,
                            'foto_profil' => $relasiUser->foto_profil,
                            'data_completion_step' => $relasiUser->data_completion_step,
                            'status_aktif' => $relasiUser->status_aktif,
                            'created_at' => $relasiUser->created_at,
                            'updated_at' => $relasiUser->updated_at
                        ],
                        'kategori_rekam_jejak' => $relasiKategori,
                        'content' => [
                            'kolom' => $perubahanData->kolom,
                            'original_data' => $perubahanData->original_data,
                            'updated_data' => $perubahanData->updated_data,
                            'status_perubahan' => $perubahanData->status_perubahans,
                            'verifikator_1' => $perubahanData->verifikator_1_users,
                            'alasan' => $perubahanData->alasan,
                            'created_at' => $perubahanData->created_at,
                            'updated_at' => $perubahanData->updated_at
                        ]
                    ];
                }
            }

            // Menggabungkan semua data yang diformat
            $allFormattedData = $formattedRekamJejak->merge($formattedDataPerubahan);

            if ($allFormattedData->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data rekam jejak karyawan tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data rekam jejak karyawan '{$user->nama}' berhasil ditampilkan.",
                'data' => [
                    'id' => $karyawan->id,
                    'user' => $user,
                    'tgl_masuk_karyawan' => $karyawan->tgl_masuk,
                    'tgl_keluar_karyawan' => $karyawan->tgl_keluar,
                    // 'masa_kerja_karyawan' => $masaKerja,
                    'list_rekam_jejak' => $allFormattedData
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataRekamJejak: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataCuti($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Ambil data karyawan berdasarkan data_karyawan_id
            $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);
            if (!$karyawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil semua data cuti yang dimiliki karyawan tersebut
            $dataCuti = Cuti::where('user_id', $karyawan->users->id)->get();

            if ($dataCuti->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data cuti karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Format list cuti
            $listCuti = $dataCuti->map(function ($cuti) {
                return [
                    'id' => $cuti->id,
                    'tipe_cuti' => $cuti->tipe_cutis,
                    'tgl_from' => $cuti->tgl_from,
                    'tgl_to' => $cuti->tgl_to,
                    'catatan' => $cuti->catatan,
                    'durasi' => $cuti->durasi,
                    'status_cuti' => $cuti->status_cutis,
                    'created_at' => $cuti->created_at,
                    'updated_at' => $cuti->updated_at
                ];
            });

            // Format data user
            $formattedData = [
                'id' => $karyawan->id,
                'user' => [
                    'id' => $karyawan->users->id,
                    'nama' => $karyawan->users->nama,
                    'username' => $karyawan->users->username,
                    'email_verified_at' => $karyawan->users->email_verified_at,
                    'data_karyawan_id' => $karyawan->users->data_karyawan_id,
                    'foto_profil' => $karyawan->users->foto_profil,
                    'data_completion_step' => $karyawan->users->data_completion_step,
                    'status_aktif' => $karyawan->users->status_aktif,
                    'created_at' => $karyawan->users->created_at,
                    'updated_at' => $karyawan->users->updated_at
                ],
                'unit_kerja' => $karyawan->unit_kerjas,
                'list_cuti' => $listCuti
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail data cuti karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
                'data' => $formattedData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataCuti: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataTukarJadwal($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Cari user berdasarkan data_karyawan_id
            $karyawan = DataKaryawan::find($data_karyawan_id);
            if (!$karyawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil user id dari karyawan
            $userId = $karyawan->users->id;

            // Cari tukar jadwal di mana user menjadi user_pengajuan atau user_ditukar
            $tukarJadwal = TukarJadwal::where('user_pengajuan', $userId)
                ->orWhere('user_ditukar', $userId)
                ->get();

            if ($tukarJadwal->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data tukar jadwal karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Format data tukar jadwal
            $formattedData = $tukarJadwal->map(function ($item) use ($userId) {
                $isUserPengajuan = $item->user_pengajuan == $userId;
                return [
                    'id' => $item->id,
                    'user_pengajuan' => [
                        'user' => [
                            'id' => $item->user_pengajuans->id,
                            'nama' => $item->user_pengajuans->nama,
                            'username' => $item->user_pengajuans->username,
                            'email_verified_at' => $item->user_pengajuans->email_verified_at,
                            'data_karyawan_id' => $item->user_pengajuans->data_karyawan_id,
                            'foto_profil' => $item->user_pengajuans->foto_profil,
                            'data_completion_step' => $item->user_pengajuans->data_completion_step,
                            'status_aktif' => $item->user_pengajuans->status_aktif,
                            'created_at' => $item->user_pengajuans->created_at,
                            'updated_at' => $item->user_pengajuans->updated_at,
                        ],
                        'jadwal' => $item->jadwal_pengajuans,
                        'status' => $item->status_tukar_jadwals,
                        'kategori' => $item->kategori_tukar_jadwals,
                    ],
                    'user_ditukar' => [
                        'user' => [
                            'id' => $item->user_ditukars->id,
                            'nama' => $item->user_ditukars->nama,
                            'username' => $item->user_ditukars->username,
                            'email_verified_at' => $item->user_ditukars->email_verified_at,
                            'data_karyawan_id' => $item->user_ditukars->data_karyawan_id,
                            'foto_profil' => $item->user_ditukars->foto_profil,
                            'data_completion_step' => $item->user_ditukars->data_completion_step,
                            'status_aktif' => $item->user_ditukars->status_aktif,
                            'created_at' => $item->user_ditukars->created_at,
                            'updated_at' => $item->user_ditukars->updated_at,
                        ],
                        'jadwal' => $item->jadwal_ditukars,
                        'status' => $item->status_tukar_jadwals,
                        'kategori' => $item->kategori_tukar_jadwals,
                    ],
                ];
            });

            // Menentukan data user dan unit_kerja
            $dataUser = $karyawan->users;
            $unitKerja = $karyawan->unit_kerjas;

            // Menentukan nama yang ditampilkan dalam pesan
            $message = '';
            if ($tukarJadwal->first()->user_pengajuan == $userId) {
                $message = "Detail tukar jadwal karyawan '{$tukarJadwal->first()->user_pengajuans->nama}' berhasil ditampilkan.";
            } else {
                $message = "Detail tukar jadwal karyawan '{$tukarJadwal->first()->user_ditukars->nama}' berhasil ditampilkan.";
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => $message,
                'data' => [
                    'id' => $karyawan->id,
                    'user' => $dataUser,
                    'unit_kerja' => $unitKerja,
                    'list_tukar_jadwal' => $formattedData
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataTukarJadwal: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataLembur($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Cari data karyawan berdasarkan data_karyawan_id
            $karyawan = DataKaryawan::find($data_karyawan_id);

            if (!$karyawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil semua data lembur berdasarkan user_id yang terkait dengan data_karyawan_id
            $dataLembur = Lembur::whereHas('users', function ($query) use ($data_karyawan_id) {
                $query->where('data_karyawan_id', $data_karyawan_id);
            })->get();

            if ($dataLembur->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data lembur karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Format data lembur
            $formattedData = $dataLembur->map(function ($lembur) {
                return [
                    'id' => $lembur->id,
                    'jadwal' => [
                        'id' => $lembur->jadwals->id,
                        'tgl_mulai' => $lembur->jadwals->tgl_mulai,
                        'tgl_selesai' => $lembur->jadwals->tgl_selesai,
                        'shift' => $lembur->jadwals->shifts
                    ],
                    'tgl_pengajuan' => $lembur->tgl_pengajuan,
                    'kompensasi_lembur' => $lembur->kategori_kompensasis,
                    'durasi' => $lembur->durasi,
                    'catatan' => $lembur->catatan,
                    'status_lembur' => $lembur->status_lemburs,
                    'created_at' => $lembur->created_at,
                    'updated_at' => $lembur->updated_at
                ];
            });

            // Menentukan data user dan unit_kerja
            $dataUser = [
                'id' => $karyawan->users->id,
                'nama' => $karyawan->users->nama,
                'email_verified_at' => $karyawan->users->email_verified_at,
                'data_karyawan_id' => $karyawan->users->data_karyawan_id,
                'foto_profil' => $karyawan->users->foto_profil,
                'data_completion_step' => $karyawan->users->data_completion_step,
                'status_aktif' => $karyawan->users->status_aktif,
                'created_at' => $karyawan->users->created_at,
                'updated_at' => $karyawan->users->updated_at
            ];
            $unitKerja = $karyawan->unit_kerjas;

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail data lembur karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
                'data' => [
                    'id' => $karyawan->id,
                    'user' => $dataUser,
                    'unit_kerja' => $unitKerja,
                    'list_lembur' => $formattedData
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataLembur: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataFeedbackPenilaian($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Ambil data user yang dinilai berdasarkan data_karyawan_id
            $userDinilai = User::whereHas('data_karyawans', function ($query) use ($data_karyawan_id) {
                $query->where('id', $data_karyawan_id);
            })->with('data_karyawans.jabatans')->first();

            if (!$userDinilai) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Ambil penilaian terkait berdasarkan user_dinilai
            $penilaian = Penilaian::with(['user_penilais', 'jenis_penilaians'])
                ->where('user_dinilai', $userDinilai->id)
                ->get();

            if ($penilaian->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data penilaian untuk karyawan ini tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Format list penilaian
            $listPenilaian = $penilaian->map(function ($penilaian) {
                return [
                    'id' => $penilaian->id,
                    'jenis_penilaian' => $penilaian->jenis_penilaians,
                    'user_penilai' => $penilaian->user_penilais,
                    'pertanyaan_jawaban' => $penilaian->pertanyaan_jawaban,
                    'total_pertanyaan' => $penilaian->total_pertanyaan,
                    'rata_rata' => $penilaian->rata_rata,
                    'created_at' => $penilaian->created_at,
                    'updated_at' => $penilaian->updated_at,
                ];
            });

            // Format Data Utama
            $formattedData = [
                'id' => $userDinilai->data_karyawan_id,
                'user' => [
                    'id' => $userDinilai->id,
                    'nama' => $userDinilai->nama,
                    'username' => $userDinilai->username,
                    'email_verified_at' => $userDinilai->email_verified_at,
                    'data_karyawan_id' => $userDinilai->data_karyawan_id,
                    'foto_profil' => $userDinilai->foto_profil,
                    'data_completion_step' => $userDinilai->data_completion_step,
                    'status_aktif' => $userDinilai->status_aktif,
                    'created_at' => $userDinilai->created_at,
                    'updated_at' => $userDinilai->updated_at
                ],
                'jabatan' => $userDinilai->data_karyawans->jabatans,
                'list_penilaian' => $listPenilaian,
            ];

            // Response dengan data detail penilaian
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Detail penilaian berhasil ditampilkan.',
                'data' => $formattedData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataFeedbackPenilaian: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataDiklat($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Ambil user yang memiliki data_karyawan_id
            $user = User::where('data_karyawan_id', $data_karyawan_id)->first();
            if (!$user || $user->id == 1 || $user->nama == 'Super Admin') {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna tidak ditemukan atau tidak valid.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil peserta_diklat yang berelasi dengan user tersebut
            $pesertaDiklats = PesertaDiklat::where('peserta', $user->id)
                ->with('diklats', 'diklats.berkas_dokumen_eksternals', 'users')
                ->get();
            if ($pesertaDiklats->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data diklat yang ditemukan untuk karyawan ini.'), Response::HTTP_NOT_FOUND);
            }

            $userName = $pesertaDiklats->first()->users;
            $formattedData = $pesertaDiklats->map(function ($pesertaDiklat) {
                $diklat = $pesertaDiklat->diklats;
                $server_url = env('STORAGE_SERVER_DOMAIN');

                return [
                    'id' => $diklat->id,
                    'nama' => $diklat->nama,
                    'kategori_diklat_id' => $diklat->kategori_diklats,
                    'status_diklat_id' => $diklat->status_diklats,
                    'deskripsi' => $diklat->deskripsi,
                    'kuota' => $diklat->kuota,
                    'total_peserta' => $diklat->total_peserta,
                    'tgl_mulai' => $diklat->tgl_mulai,
                    'tgl_selesai' => $diklat->tgl_selesai,
                    'jam_mulai' => $diklat->jam_mulai,
                    'jam_selesai' => $diklat->jam_selesai,
                    'durasi' => $diklat->durasi,
                    'lokasi' => $diklat->lokasi,
                    'skp' => $diklat->skp ?? null,
                    'dokumen_eksternal' => $diklat->berkas_dokumen_eksternals ? [
                        'id' => $diklat->berkas_dokumen_eksternals->id,
                        'nama_file' => $diklat->berkas_dokumen_eksternals->nama_file,
                        'path' => $server_url . $diklat->berkas_dokumen_eksternals->path,
                        'ext' => $diklat->berkas_dokumen_eksternals->ext,
                        'size' => $diklat->berkas_dokumen_eksternals->size,
                        'tgl_upload' => $diklat->berkas_dokumen_eksternals->tgl_upload,
                        'created_at' => $diklat->berkas_dokumen_eksternals->created_at,
                        'updated_at' => $diklat->berkas_dokumen_eksternals->updated_at
                    ] : null,
                    'verifikator_1' => $diklat->verifikator_1_diklats,
                    'verifikator_2' => $diklat->verifikator_2_diklats,
                    'certificate_published' => $diklat->certificate_published,
                    'certificate_verified_by' => $diklat->certificate_diklats,
                    'alasan' => $diklat->alasan,
                    'created_at' => $diklat->created_at,
                    'updated_at' => $diklat->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data diklat dari karyawan '{$userName->nama}' berhasil ditampilkan.",
                'data' => [
                    'user' => [
                        'id' => $userName->id,
                        'nama' => $userName->nama,
                        'username' => $userName->username,
                        'email_verified_at' => $userName->email_verified_at,
                        'data_karyawan_id' => $userName->data_karyawan_id,
                        'foto_profil' => $userName->foto_profil,
                        'data_completion_step' => $userName->data_completion_step,
                        'status_aktif' => $userName->status_aktif,
                        'created_at' => $userName->created_at,
                        'updated_at' => $userName->updated_at
                    ],
                    'unit_kerja' => $userName->data_karyawans->unit_kerjas,
                    'jadwal_diklat' => $formattedData
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataDiklat: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
