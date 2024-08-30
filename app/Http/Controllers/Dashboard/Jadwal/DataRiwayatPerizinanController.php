<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\Notifikasi;
use App\Models\RiwayatIzin;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataRiwayatPerizinanController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view riwayatPerizinan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $riwayat_izin = RiwayatIzin::query()->orderBy('created_at', 'desc');

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $riwayat_izin->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $riwayat_izin->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $riwayat_izin->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $riwayat_izin->whereHas('users', function ($query) use ($statusAktif) {
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
                $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $riwayat_izin->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $riwayat_izin->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $riwayat_izin->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $riwayat_izin->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $riwayat_izin->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        if (isset($filters['status_izin'])) {
            $namaStatusCuti = $filters['status_izin'];
            $riwayat_izin->whereHas('status_izins', function ($query) use ($namaStatusCuti) {
                if (is_array($namaStatusCuti)) {
                    $query->whereIn('id', $namaStatusCuti);
                } else {
                    $query->where('id', '=', $namaStatusCuti);
                }
            });
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $riwayat_izin->where(function ($query) use ($searchTerm) {
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        // Paginate
        if ($limit == 0) {
            $dataPerizinan = $riwayat_izin->get();
            $paginationData = null;
        } else {
            // Pastikan limit adalah integer
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataPerizinan = $riwayat_izin->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataPerizinan->url(1),
                    'last' => $dataPerizinan->url($dataPerizinan->lastPage()),
                    'prev' => $dataPerizinan->previousPageUrl(),
                    'next' => $dataPerizinan->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataPerizinan->currentPage(),
                    'last_page' => $dataPerizinan->lastPage(),
                    'per_page' => $dataPerizinan->perPage(),
                    'total' => $dataPerizinan->total(),
                ]
            ];
        }

        if ($dataPerizinan->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data izin karyawan tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataPerizinan->map(function ($dataPerizinan) {
            return [
                'id' => $dataPerizinan->id,
                'user' => [
                    'id' => $dataPerizinan->users->id,
                    'nama' => $dataPerizinan->users->nama,
                    'email_verified_at' => $dataPerizinan->users->email_verified_at,
                    'data_karyawan_id' => $dataPerizinan->users->data_karyawan_id,
                    'foto_profil' => $dataPerizinan->users->foto_profil,
                    'data_completion_step' => $dataPerizinan->users->data_completion_step,
                    'status_aktif' => $dataPerizinan->users->status_aktif,
                    'created_at' => $dataPerizinan->users->created_at,
                    'updated_at' => $dataPerizinan->users->updated_at
                ],
                'durasi' => $dataPerizinan->durasi,
                'keterangan' => $dataPerizinan->keterangan,
                'status_izin' => $dataPerizinan->status_izins,
                'created_at' => $dataPerizinan->created_at,
                'updated_at' => $dataPerizinan->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data izin karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view riwayatPerizinan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataIzin = RiwayatIzin::with(['users', 'status_izins', 'verifikator_izins'])->find($id);
        if (!$dataIzin) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data izin karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = [
            'id' => $dataIzin->id,
            'user' => [
                'id' => $dataIzin->users->id,
                'nama' => $dataIzin->users->nama,
                'email_verified_at' => $dataIzin->users->email_verified_at,
                'data_karyawan_id' => $dataIzin->users->data_karyawan_id,
                'foto_profil' => $dataIzin->users->foto_profil,
                'data_completion_step' => $dataIzin->users->data_completion_step,
                'status_aktif' => $dataIzin->users->status_aktif,
                'created_at' => $dataIzin->users->created_at,
                'updated_at' => $dataIzin->users->updated_at
            ],
            'durasi' => $dataIzin->durasi,
            'keterangan' => $dataIzin->keterangan,
            'status_izin' => $dataIzin->status_izins,
            'verifikator' => $dataIzin->verifikator_izins ?? null,
            'alasan' => $dataIzin->alasan ?? null,
            'created_at' => $dataIzin->created_at,
            'updated_at' => $dataIzin->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data izin karyawan '{$dataIzin->users->nama}' berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function verifikasiRiwayatIzin(Request $request, $izinId)
    {
        if (!Gate::allows('verifikasi1 riwayatPerizinan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari riwayat izin berdasarkan ID
        $riwayat_izin = RiwayatIzin::find($izinId);

        if (!$riwayat_izin) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Riwayat perizinan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $status_izin_id = $riwayat_izin->status_izin_id;

        if ($request->has('verifikasi_pertama_disetujui') && $request->verifikasi_pertama_disetujui == 1) {
            if ($status_izin_id == 1) {
                $riwayat_izin->status_izin_id = 2;
                $riwayat_izin->verifikator_1 = Auth::id();
                $riwayat_izin->alasan = null;
                $riwayat_izin->save();

                $data_karyawan_id = DB::table('data_karyawans')
                    ->where('user_id', $riwayat_izin->user_id)
                    ->value('id');

                DB::table('data_karyawans')
                    ->where('id', $data_karyawan_id)
                    ->update(['status_reward_presensi' => false]);

                // Buat dan simpan notifikasi
                $this->createNotifikasiIzin($riwayat_izin, 'Disetujui');

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi perizinan dari '{$riwayat_izin->users->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Riwayat izin dari '{$riwayat_izin->users->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
            if ($status_izin_id == 1) {
                $riwayat_izin->status_izin_id = 3;
                $riwayat_izin->verifikator_1 = Auth::id();
                $riwayat_izin->alasan = $request->input('alasan', null);
                $riwayat_izin->save();

                // Buat dan simpan notifikasi
                $this->createNotifikasiIzin($riwayat_izin, 'Ditolak');

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi perizinan dari '{$riwayat_izin->users->nama}' telah ditolak."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Riwayat Izin '{$riwayat_izin->id}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
        }
    }


    private function createNotifikasiIzin($riwayat_izin, $status)
    {
        $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
        $verifikator = Auth::user()->nama;
        $konversiTgl = Carbon::parse(RandomHelper::convertToDateString($riwayat_izin->created_at))->locale('id')->isoFormat('D MMMM YYYY');
        $message = "Pengajuan perizinan Anda pada tanggal '{$konversiTgl}' telah '{$statusText}' oleh '{$verifikator}'.";

        // Buat notifikasi untuk user yang mengajukan cuti
        Notifikasi::create([
            'kategori_notifikasi_id' => 10,
            'user_id' => $riwayat_izin->user_id, // Penerima notifikasi
            'message' => $message,
            'is_read' => false,
        ]);
    }
}
