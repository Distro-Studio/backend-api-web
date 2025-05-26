<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Exports\Karyawan\RewardPembatalanExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\RiwayatPembatalanReward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PembatalanRewardController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view pembatalanReward')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Per page
            $limit = $request->input('limit', 10);

            $pembatalanReward = RiwayatPembatalanReward::with(['data_karyawans.users', 'cutis', 'presensis', 'riwayat_izins', 'verifikators'])
                ->join('data_karyawans', 'riwayat_pembatalan_rewards.data_karyawan_id', '=', 'data_karyawans.id')
                ->orderBy('data_karyawans.nik', 'asc')
                ->select('riwayat_pembatalan_rewards.*');

            $filters = $request->all();

            // Filter
            if (isset($filters['unit_kerja'])) {
                $namaUnitKerja = $filters['unit_kerja'];
                $pembatalanReward->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('id', $namaUnitKerja);
                    } else {
                        $query->where('id', '=', $namaUnitKerja);
                    }
                });
            }

            if (isset($filters['jabatan'])) {
                $namaJabatan = $filters['jabatan'];
                $pembatalanReward->whereHas('data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                    if (is_array($namaJabatan)) {
                        $query->whereIn('id', $namaJabatan);
                    } else {
                        $query->where('id', '=', $namaJabatan);
                    }
                });
            }

            if (isset($filters['status_karyawan'])) {
                $statusKaryawan = $filters['status_karyawan'];
                $pembatalanReward->whereHas('data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
                    if (is_array($statusKaryawan)) {
                        $query->whereIn('id', $statusKaryawan);
                    } else {
                        $query->where('id', '=', $statusKaryawan);
                    }
                });
            }

            if (isset($filters['masa_kerja'])) {
                $masaKerja = $filters['masa_kerja'];
                $currentDate = Carbon::now('Asia/Jakarta');
                if (is_array($masaKerja)) {
                    $pembatalanReward->whereHas('data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                        foreach ($masaKerja as $masa) {
                            $bulan = $masa * 12;
                            $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                        }
                    });
                } else {
                    $bulan = $masaKerja * 12;
                    $pembatalanReward->whereHas('data_karyawans', function ($query) use ($bulan, $currentDate) {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    });
                }
            }

            if (isset($filters['status_aktif'])) {
                $statusAktif = $filters['status_aktif'];
                $pembatalanReward->whereHas('users', function ($query) use ($statusAktif) {
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
                    $pembatalanReward->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                        $query->whereIn('tgl_masuk', $tglMasuk);
                    });
                } else {
                    $pembatalanReward->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                        $query->where('tgl_masuk', $tglMasuk);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $namaAgama = $filters['agama'];
                $pembatalanReward->whereHas('data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                    $pembatalanReward->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    });
                } else {
                    $pembatalanReward->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    });
                }
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $pembatalanReward->whereHas('data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                    $pembatalanReward->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    });
                } else {
                    $pembatalanReward->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['jenis_kompetensi'])) {
                $jenisKaryawan = $filters['jenis_kompetensi'];
                if (is_array($jenisKaryawan)) {
                    $pembatalanReward->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_kompetensi', $jk);
                            }
                        });
                    });
                } else {
                    $pembatalanReward->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_kompetensi', $jenisKaryawan);
                    });
                }
            }

            // Search
            if (isset($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $pembatalanReward->where(function ($query) use ($searchTerm) {
                    $query->whereHas('data_karyawans.users', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhereHas('data_karyawans', function ($query) use ($searchTerm) {
                        $query->where('nik', 'like', $searchTerm);
                    });
                });
            }

            // Paginate
            if ($limit == 0) {
                $dataPembatalanReward = $pembatalanReward->get();
                $paginationData = null;
            } else {
                // Pastikan limit adalah integer
                $limit = is_numeric($limit) ? (int)$limit : 10;
                $dataPembatalanReward = $pembatalanReward->paginate($limit);

                $paginationData = [
                    'links' => [
                        'first' => $dataPembatalanReward->url(1),
                        'last' => $dataPembatalanReward->url($dataPembatalanReward->lastPage()),
                        'prev' => $dataPembatalanReward->previousPageUrl(),
                        'next' => $dataPembatalanReward->nextPageUrl(),
                    ],
                    'meta' => [
                        'current_page' => $dataPembatalanReward->currentPage(),
                        'last_page' => $dataPembatalanReward->lastPage(),
                        'per_page' => $dataPembatalanReward->perPage(),
                        'total' => $dataPembatalanReward->total(),
                    ]
                ];
            }

            if ($dataPembatalanReward->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = $dataPembatalanReward->map(function ($pembatalanReward) use ($baseUrl) {
                $unitKerja = $pembatalanReward->data_karyawans->unit_kerjas;
                $nik = $pembatalanReward->data_karyawans;
                $dataUsers = $pembatalanReward->data_karyawans->users;
                $dataVerifikator = $pembatalanReward->verifikators;
                $dataCutis = $pembatalanReward->cutis;
                $dataPresensi = $pembatalanReward->presensis;
                $dataRiwayatIzin = $pembatalanReward->riwayat_izins;

                return [
                    'id' => $pembatalanReward->id,
                    'user' => $dataUsers ? [
                        'id' => $dataUsers->id,
                        'nama' => $dataUsers->nama,
                        'username' => $dataUsers->username,
                        'email_verified_at' => $dataUsers->email_verified_at,
                        'data_karyawan_id' => $dataUsers->data_karyawan_id,
                        'foto_profil' => $dataUsers->foto_profiles ? [
                            'id' => $dataUsers->foto_profiles->id,
                            'user_id' => $dataUsers->foto_profiles->user_id,
                            'file_id' => $dataUsers->foto_profiles->file_id,
                            'nama' => $dataUsers->foto_profiles->nama,
                            'nama_file' => $dataUsers->foto_profiles->nama_file,
                            'path' => $baseUrl . $dataUsers->foto_profiles->path,
                            'ext' => $dataUsers->foto_profiles->ext,
                            'size' => $dataUsers->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $dataUsers->data_completion_step,
                        'status_aktif' => $dataUsers->status_aktif,
                        'created_at' => $dataUsers->created_at,
                        'updated_at' => $dataUsers->updated_at
                    ] : null,
                    'nik' => $nik->nik ?? null,
                    'unit_kerja' => $unitKerja ? [
                        'id' => $unitKerja->id,
                        'nama_unit' => $unitKerja->nama_unit,
                        'jenis_karyawan' => $unitKerja->jenis_karyawan,
                    ] : null,
                    'tipe_pembatalan' => $pembatalanReward->tipe_pembatalan,
                    'tgl_pembatalan' => $pembatalanReward->tgl_pembatalan,
                    'keterangan' => $pembatalanReward->keterangan,
                    'data_cuti' => $dataCutis ? [
                        'id' => $dataCutis->id,
                        'tipe_cuti' => $dataCutis->tipe_cutis,
                        'hak_cuti' => $dataCutis->hak_cutis,
                        'keterangan' => $dataCutis->keterangan ?? null,
                        'tgl_from' => $dataCutis->tgl_from,
                        'tgl_to' => $dataCutis->tgl_to,
                        'catatan' => $dataCutis->catatan,
                        'durasi' => $dataCutis->durasi,
                        'status_cuti' => $dataCutis->status_cutis,
                        'alasan' => $dataCutis->alasan ?? null,
                        'created_at' => $dataCutis->created_at,
                        'updated_at' => $dataCutis->updated_at
                    ] : null,
                    'data_presensi' => $dataPresensi ? [
                        'id' => $dataPresensi->id,
                        'jadwal' => $dataPresensi->jadwals ? [
                            'id' => $dataPresensi->jadwals->id,
                            'tgl_mulai' => $dataPresensi->jadwals->tgl_mulai,
                            'tgl_selesai' => $dataPresensi->jadwals->tgl_selesai,
                            'shift' => $dataPresensi->jadwals->shifts,
                        ] : null,
                        'jam_masuk' => $dataPresensi->jam_masuk,
                        'jam_keluar' => $dataPresensi->jam_keluar,
                        'durasi' => $dataPresensi->durasi,
                        'kategori_presensi' => $dataPresensi->kategori_presensis,
                        'created_at' => $dataPresensi->created_at,
                        'updated_at' => $dataPresensi->updated_at,
                    ] : null,
                    'data_izin' => $dataRiwayatIzin ? [
                        'id' => $dataRiwayatIzin->id,
                        'tgl_izin' => $dataRiwayatIzin->tgl_izin,
                        'waktu_izin' => $dataRiwayatIzin->waktu_izin,
                        'durasi' => $dataRiwayatIzin->durasi,
                        'keterangan' => $dataRiwayatIzin->keterangan,
                        'status_izin' => $dataRiwayatIzin->status_izins,
                        'created_at' => $dataRiwayatIzin->created_at,
                        'updated_at' => $dataRiwayatIzin->updated_at
                    ] : null,
                    'verifikator' => $dataVerifikator ? [
                        'id' => $dataVerifikator->id,
                        'nama' => $dataVerifikator->nama,
                        'username' => $dataVerifikator->username,
                        'email_verified_at' => $dataVerifikator->email_verified_at,
                        'data_karyawan_id' => $dataVerifikator->data_karyawan_id,
                        'foto_profil' => $dataVerifikator->foto_profiles ? [
                            'id' => $dataVerifikator->foto_profiles->id,
                            'user_id' => $dataVerifikator->foto_profiles->user_id,
                            'file_id' => $dataVerifikator->foto_profiles->file_id,
                            'nama' => $dataVerifikator->foto_profiles->nama,
                            'nama_file' => $dataVerifikator->foto_profiles->nama_file,
                            'path' => $baseUrl . $dataVerifikator->foto_profiles->path,
                            'ext' => $dataVerifikator->foto_profiles->ext,
                            'size' => $dataVerifikator->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $dataVerifikator->data_completion_step,
                        'status_aktif' => $dataVerifikator->status_aktif,
                        'created_at' => $dataVerifikator->created_at,
                        'updated_at' => $dataVerifikator->updated_at
                    ] : null,
                    'created_at' => $pembatalanReward->created_at,
                    'updated_at' => $pembatalanReward->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data pembatalan reward presensi berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Pembatalan Reward | - Error function index: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportPembatalanReward(Request $request)
    {
        try {
            if (!Gate::allows('export pembatalanReward')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataPembatalan = RiwayatPembatalanReward::all();
            if ($dataPembatalan->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data pembatalan reward yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new RewardPembatalanExport($request->all()), 'pembatalan-reward.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Pembatalan Reward | - Error function export: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
