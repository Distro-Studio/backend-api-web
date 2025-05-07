<?php

namespace App\Http\Controllers\Dashboard\Karyawan\DetailKaryawan;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\Lembur;
use App\Models\NonShift;
use App\Models\Presensi;
use App\Models\Penilaian;
use App\Models\TrackRecord;
use App\Models\TukarJadwal;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Models\PesertaDiklat;
use Illuminate\Http\Response;
use App\Models\RiwayatPerubahan;
use App\Models\TransferKaryawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Presensi\DetailKaryawanPresensiExport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class Karyawan_DetailController extends Controller
{
    public function getDataPresensi(Request $request, $data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
                $start_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_mulai'))->format('Y-m-d');
                $end_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_selesai'))->format('Y-m-d');
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal presensi mulai dan selesai tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
            }

            $dataPresensi = Presensi::with([
                'users',
                'jadwals.shifts',
                'data_karyawans.unit_kerjas',
                'kategori_presensis',
            ])
                ->where('data_karyawan_id', $data_karyawan_id)
                ->first();
            if (!$dataPresensi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data presensi karyawan tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $listPresensi = Presensi::with([
                'users',
                'jadwals.shifts',
                'data_karyawans.unit_kerjas',
                'kategori_presensis'
            ])
                ->where('data_karyawan_id', $data_karyawan_id)
                ->whereBetween(DB::raw("DATE(jam_masuk)"), [$start_date, $end_date])
                ->orderBy('created_at', 'desc')
                ->get();
            $formattedData = $listPresensi->map(function ($presensi) {
                return [
                    'id' => $presensi->id,
                    'user' => $presensi->users,
                    'unit_kerja' => $presensi->data_karyawans->unit_kerjas,
                    'jadwal' => $presensi->jadwals ? [
                        'id' => $presensi->jadwals->id,
                        'tgl_mulai' => $presensi->jadwals->tgl_mulai,
                        'tgl_selesai' => $presensi->jadwals->tgl_selesai,
                        'shift' => $presensi->jadwals->shifts,
                    ] : null,
                    'jam_masuk' => $presensi->jam_masuk,
                    'jam_keluar' => $presensi->jam_keluar,
                    'durasi' => $presensi->durasi,
                    'kategori_presensi' => $presensi->kategori_presensis,
                    'created_at' => $presensi->created_at,
                    'updated_at' => $presensi->updated_at,
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail data presensi karyawan '{$dataPresensi->users->nama}' berhasil ditampilkan.",
                'data' => [
                    'user' => $this->mapUser($dataPresensi->users),
                    'unit_kerja' => $dataPresensi->data_karyawans->unit_kerjas,
                    'list_presensi' => $formattedData
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

    public function exportDataPresensi(Request $request, $data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
                $start_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_mulai'))->format('Y-m-d');
                $end_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_selesai'))->format('Y-m-d');
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal presensi mulai dan selesai tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
            }

            $listPresensi = Presensi::with([
                'users',
                'jadwals.shifts',
                'data_karyawans.unit_kerjas',
                'kategori_presensis'
            ])
                ->where('data_karyawan_id', $data_karyawan_id)
                ->whereBetween(DB::raw("DATE(jam_masuk)"), [$start_date, $end_date])
                ->orderBy('created_at', 'desc')
                ->get();
            if ($listPresensi->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data presensi karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new DetailKaryawanPresensiExport($start_date, $end_date, $data_karyawan_id), 'presensi-detail-karyawan.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage() . $e->getLine()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function exportDataPresensi: ' . $e->getMessage());
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

            $karyawan = DataKaryawan::with(['users.jadwals.shifts', 'unit_kerjas'])->find($data_karyawan_id);
            if (!$karyawan) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $user = $karyawan->users;
            if (!$user) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data akun karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $unitKerja = $karyawan->unit_kerjas;
            $jenisKaryawan = $unitKerja->jenis_karyawan;

            // Tentukan rentang tanggal untuk minggu ini
            $startOfWeek = Carbon::now('Asia/Jakarta')->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            $endOfWeek = Carbon::now('Asia/Jakarta')->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
            $dateRange = $this->generateDateRange(Carbon::parse($startOfWeek), Carbon::parse($endOfWeek));

            // Ambil data jadwal
            $jadwals = Jadwal::where('user_id', $user->id)
                ->where(function ($query) use ($startOfWeek, $endOfWeek) {
                    $query->whereBetween('tgl_mulai', [$startOfWeek, $endOfWeek])
                        ->orWhereBetween('tgl_selesai', [$startOfWeek, $endOfWeek]);
                })
                ->with('shifts')
                ->get();

            // Ambil data cuti
            $cutis = Cuti::where('user_id', $user->id)
                ->where('status_cuti_id', 4)
                ->get()
                ->map(function ($cuti) {
                    $cuti->tgl_from = Carbon::createFromFormat('d-m-Y', $cuti->tgl_from)->format('Y-m-d');
                    $cuti->tgl_to = Carbon::createFromFormat('d-m-Y', $cuti->tgl_to)->format('Y-m-d');
                    return $cuti;
                });

            // Ambil jadwal non-shift jika jenis karyawan adalah non-shift
            $nonShifts = $jenisKaryawan == 0
                ? NonShift::whereIn('nama', collect($dateRange)->map(fn($date) => $this->getDayName(Carbon::parse($date)->dayOfWeek))->toArray())
                ->get()
                ->keyBy('nama')
                : collect();

            // Format jadwal
            $formattedSchedules = [];
            foreach ($dateRange as $date) {
                $currentDate = Carbon::parse($date);

                // Cek untuk cuti
                $cutiForDate = $cutis->first(function ($cuti) use ($currentDate) {
                    return $currentDate->between(Carbon::parse($cuti->tgl_from), Carbon::parse($cuti->tgl_to));
                });

                if ($cutiForDate) {
                    $formattedSchedules[] = [
                        'id' => $cutiForDate->id,
                        'user' => $this->mapUser($user),
                        'unit_kerja' => $unitKerja,
                        'tipe_cuti' => $cutiForDate->tipe_cutis,
                        'keterangan' => $cutiForDate->keterangan,
                        'tgl_from' => $cutiForDate->tgl_from,
                        'tgl_to' => $cutiForDate->tgl_to,
                        'catatan' => $cutiForDate->catatan,
                        'durasi' => $cutiForDate->durasi,
                        'status_cuti' => $cutiForDate->status_cutis,
                        'status' => 5,
                        'created_at' => $cutiForDate->created_at,
                        'updated_at' => $cutiForDate->updated_at,
                    ];
                    continue;
                }

                // Cek untuk jadwal shift
                $scheduleForDate = $jadwals->first(function ($jadwal) use ($currentDate) {
                    return $currentDate->between(Carbon::parse($jadwal->tgl_mulai), Carbon::parse($jadwal->tgl_selesai));
                });

                if ($scheduleForDate) {
                    $formattedSchedules[] = [
                        'id' => $scheduleForDate->id,
                        'tgl_mulai' => $scheduleForDate->tgl_mulai,
                        'tgl_selesai' => $scheduleForDate->tgl_selesai,
                        'shift' => $scheduleForDate->shifts,
                        'updated_at' => $scheduleForDate->updated_at,
                        'status' => 1,
                    ];
                    continue;
                }

                // Cek untuk jadwal non-shift
                if ($jenisKaryawan == 0) {
                    $dayName = $this->getDayName($currentDate->dayOfWeek);
                    $nonShiftForDay = $nonShifts->get($dayName);

                    if ($nonShiftForDay) {
                        $formattedSchedules[] = [
                            'id' => $nonShiftForDay->id,
                            'nama' => $nonShiftForDay->nama,
                            'jam_from' => $nonShiftForDay->jam_from,
                            'jam_to' => $nonShiftForDay->jam_to,
                            'status' => 2,
                        ];
                    } else {
                        $formattedSchedules[] = null;
                    }
                } else {
                    $formattedSchedules[] = null;
                }
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail jadwal karyawan '{$user->nama}' minggu ini berhasil ditampilkan.",
                'data' => [
                    'id' => $karyawan->id,
                    'user' => $this->mapUser($user),
                    'unit_kerja' => $unitKerja,
                    'list_jadwal' => $formattedSchedules,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Jadwal | - Error function getDataJadwal: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
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
                    'user' => $this->mapUser($user),
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
                        'dokumen' => "https://192.168.0.20/RskiSistem24/file-storage/public" . $transfer->dokumen,
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
                        'user' => $this->mapUser($relasiUser),
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
                    'user' => $this->mapUser($user),
                    'tgl_masuk_karyawan' => $karyawan->tgl_masuk,
                    'tgl_keluar_karyawan' => $karyawan->tgl_keluar,
                    // 'masa_kerja_karyawan' => $masaKerja,
                    'list_rekam_jejak' => $allFormattedData
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataRekamJejak: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
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

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail data cuti karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
                'data' => [
                    'id' => $karyawan->id,
                    'user' => $this->mapUser($karyawan->users),
                    'unit_kerja' => $karyawan->unit_kerjas,
                    'list_cuti' => $listCuti
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataCuti: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
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

            $karyawan = DataKaryawan::find($data_karyawan_id);
            if (!$karyawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil user id dari karyawan
            $userId = $karyawan->users->id;

            // Cari tukar jadwal di mana user menjadi user_pengajuan atau user_ditukar
            $tukarJadwal = TukarJadwal::where('user_pengajuan', $userId)
                ->orWhere('user_ditukar', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            if ($tukarJadwal->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data tukar jadwal karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Format data tukar jadwal
            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = $tukarJadwal->map(function ($item) use ($baseUrl) {
                return [
                    'id' => $item->id,
                    'tanggal_pengajuan' => $item->created_at,
                    'status_penukaran' => $item->status_tukar_jadwals ? [
                        'id' => $item->status_tukar_jadwals->id,
                        'label' => $item->status_tukar_jadwals->label,
                        'created_at' => $item->status_tukar_jadwals->created_at,
                        'updated_at' => $item->status_tukar_jadwals->updated_at,
                    ] : null,
                    'kategori_penukaran' => $item->kategori_tukar_jadwals ? [
                        'id' => $item->kategori_tukar_jadwals->id,
                        'label' => $item->kategori_tukar_jadwals->label,
                        'created_at' => $item->kategori_tukar_jadwals->created_at,
                        'updated_at' => $item->kategori_tukar_jadwals->updated_at,
                    ] : null,
                    'unit_kerja' => $item->user_pengajuans->data_karyawans->unit_kerjas ?? null,
                    'karyawan_pengajuan' => $item->user_pengajuans ? [
                        'id' => $item->user_pengajuans->id,
                        'nama' => $item->user_pengajuans->nama,
                        'email_verified_at' => $item->user_pengajuans->email_verified_at,
                        'data_karyawan_id' => $item->user_pengajuans->data_karyawans->id,
                        'foto_profil' => $item->user_pengajuans->foto_profiles ? [
                            'id' => $item->user_pengajuans->foto_profiles->id,
                            'user_id' => $item->user_pengajuans->foto_profiles->user_id,
                            'file_id' => $item->user_pengajuans->foto_profiles->file_id,
                            'nama' => $item->user_pengajuans->foto_profiles->nama,
                            'nama_file' => $item->user_pengajuans->foto_profiles->nama_file,
                            'path' => $baseUrl . $item->user_pengajuans->foto_profiles->path,
                            'ext' => $item->user_pengajuans->foto_profiles->ext,
                            'size' => $item->user_pengajuans->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $item->user_pengajuans->data_completion_step,
                        'status_aktif' => $item->user_pengajuans->status_aktif,
                        'created_at' => $item->user_pengajuans->created_at,
                        'updated_at' => $item->user_pengajuans->updated_at,
                    ] : null,
                    'karyawan_ditukar' => $item->user_ditukars ? [
                        'id' => $item->user_ditukars->id,
                        'nama' => $item->user_ditukars->nama,
                        'email_verified_at' => $item->user_ditukars->email_verified_at,
                        'data_karyawan_id' => $item->user_ditukars->data_karyawans->id,
                        'foto_profil' => $item->user_ditukars->foto_profiles ? [
                            'id' => $item->user_ditukars->foto_profiles->id,
                            'user_id' => $item->user_ditukars->foto_profiles->user_id,
                            'file_id' => $item->user_ditukars->foto_profiles->file_id,
                            'nama' => $item->user_ditukars->foto_profiles->nama,
                            'nama_file' => $item->user_ditukars->foto_profiles->nama_file,
                            'path' => $baseUrl . $item->user_ditukars->foto_profiles->path,
                            'ext' => $item->user_ditukars->foto_profiles->ext,
                            'size' => $item->user_ditukars->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $item->user_ditukars->data_completion_step,
                        'status_aktif' => $item->user_ditukars->status_aktif,
                        'created_at' => $item->user_ditukars->created_at,
                        'updated_at' => $item->user_ditukars->updated_at,
                    ] : null,
                    'list_jadwal' => [
                        [
                            'jadwal_karyawan_pengajuan' => $item->jadwal_pengajuans ? [
                                'id' => $item->jadwal_pengajuans->id,
                                'tgl_mulai' => $item->jadwal_pengajuans->tgl_mulai,
                                'tgl_selesai' => $item->jadwal_pengajuans->tgl_selesai,
                                'shift' => $item->jadwal_pengajuans->shifts,
                                'created_at' => $item->jadwal_pengajuans->created_at,
                                'updated_at' => $item->jadwal_pengajuans->updated_at,
                            ] : null,
                            'jadwal_karyawan_ditukar' => $item->jadwal_ditukars ? [
                                'id' => $item->jadwal_ditukars->id,
                                'tgl_mulai' => $item->jadwal_ditukars->tgl_mulai,
                                'tgl_selesai' => $item->jadwal_ditukars->tgl_selesai,
                                'shift' => $item->jadwal_ditukars->shifts,
                                'created_at' => $item->jadwal_ditukars->created_at,
                                'updated_at' => $item->jadwal_ditukars->updated_at,
                            ] : null,
                        ]
                    ],
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ];
            });

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
                    'user' => $this->mapUser($karyawan->users),
                    'unit_kerja' => $karyawan->unit_kerjas,
                    'pertukaran_jadwal' => $formattedData
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataTukarJadwal: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
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

            $jenisKaryawan = $karyawan->unit_kerjas->jenis_karyawan;

            $dataLembur = Lembur::where('user_id', $karyawan->user_id)
                ->orderBy('created_at', 'desc')
                ->get();
            if ($dataLembur->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data lembur karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Format data lembur
            $formattedData = $dataLembur->map(function ($lembur) use ($jenisKaryawan) {
                $jadwalNonShift = [];
                if ($jenisKaryawan == 0) {
                    $tanggalPengajuan = Carbon::createFromFormat('d-m-Y', $lembur->tgl_pengajuan);
                    $hari = $tanggalPengajuan->locale('id')->dayName;
                    $jadwalNonShift = NonShift::where('nama', $hari)->first();
                }

                return [
                    'id' => $lembur->id,
                    'jadwal_shift' => $jenisKaryawan == 1 ? [
                        'id' => $lembur->jadwals->id ?? null,
                        'tgl_mulai' => $lembur->jadwals->tgl_mulai ?? null,
                        'tgl_selesai' => $lembur->jadwals->tgl_selesai ?? null,
                        'shift' => $lembur->jadwals->shifts ?? null,
                    ] : null,
                    'jadwal_non_shift' => $jadwalNonShift ? [
                        'id' => $jadwalNonShift->id,
                        'nama' => $jadwalNonShift->nama,
                        'jam_from' => $jadwalNonShift->jam_from,
                        'jam_to' => $jadwalNonShift->jam_to,
                        'created_at' => $jadwalNonShift->created_at,
                        'updated_at' => $jadwalNonShift->updated_at
                    ] : null,
                    'tgl_pengajuan' => $lembur->tgl_pengajuan,
                    'durasi' => $lembur->durasi,
                    'catatan' => $lembur->catatan,
                    'created_at' => $lembur->created_at,
                    'updated_at' => $lembur->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail data lembur karyawan '{$karyawan->users->nama}' berhasil ditampilkan.",
                'data' => [
                    'id' => $karyawan->id,
                    'user' => $this->mapUser($karyawan->users),
                    'unit_kerja' => $karyawan->unit_kerjas,
                    'list_lembur' => $formattedData
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataLembur: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.' . $e->getMessage(),
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

            // Response dengan data detail penilaian
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Detail penilaian berhasil ditampilkan.',
                'data' => [
                    'id' => $userDinilai->data_karyawan_id,
                    'user' => $this->mapUser($userDinilai),
                    'jabatan' => $userDinilai->data_karyawans->jabatans,
                    'list_penilaian' => $listPenilaian,
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataFeedbackPenilaian: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
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
                ->whereHas('diklats', function ($query) {
                    $query->where('kategori_diklat_id', 1) // Internal
                        ->where('status_diklat_id', 4)
                        ->where('certificate_published', 1);
                })
                ->get();
            if ($pesertaDiklats->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data diklat yang ditemukan untuk karyawan ini.'), Response::HTTP_NOT_FOUND);
            }

            $userName = $pesertaDiklats->first()->users;
            $formattedData = $pesertaDiklats->map(function ($pesertaDiklat) {
                $diklat = $pesertaDiklat->diklats;
                $server_url = "https://192.168.0.20/RskiSistem24/file-storage/public";

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
                    'id' => $user->data_karyawan_id,
                    'user' => $this->mapUser($userName),
                    'unit_kerja' => $userName->data_karyawans->unit_kerjas,
                    'jadwal_diklat' => $formattedData
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataDiklat: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function mapUser($user)
    {
        return [
            'id' => $user->id,
            'nama' => $user->nama,
            'username' => $user->username,
            'email_verified_at' => $user->email_verified_at,
            'data_karyawan_id' => $user->data_karyawan_id,
            'foto_profil' => $user->foto_profiles ? [
                'id' => $user->foto_profiles->id,
                'user_id' => $user->foto_profiles->user_id,
                'file_id' => $user->foto_profiles->file_id,
                'nama' => $user->foto_profiles->nama,
                'nama_file' => $user->foto_profiles->nama_file,
                'path' => env('STORAGE_SERVER_DOMAIN') . $user->foto_profiles->path,
                'ext' => $user->foto_profiles->ext,
                'size' => $user->foto_profiles->size,
            ] : null,
            'data_completion_step' => $user->data_completion_step,
            'status_aktif' => $user->status_aktif,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    private function generateDateRange($start_date, $end_date)
    {
        $dates = [];
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }

    private function getDayName($dayOfWeek)
    {
        switch ($dayOfWeek) {
            case Carbon::MONDAY:
                return 'Senin';
            case Carbon::TUESDAY:
                return 'Selasa';
            case Carbon::WEDNESDAY:
                return 'Rabu';
            case Carbon::THURSDAY:
                return 'Kamis';
            case Carbon::FRIDAY:
                return 'Jumat';
            case Carbon::SATURDAY:
                return 'Sabtu';
            case Carbon::SUNDAY:
                return 'Minggu';
            default:
                return '';
        }
    }
}
