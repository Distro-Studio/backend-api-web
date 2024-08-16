<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Models\Berkas;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\RiwayatPerubahan;
use App\Models\PerubahanKeluarga;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataRiwayatPerubahanController extends Controller
{
    // public function index(Request $request)
    // {
    //     if (!Gate::allows('view dataKaryawan')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $limit = $request->input('limit', 10);

    //     $data_perubahan = RiwayatPerubahan::query()->orderBy('created_at', 'desc');

    //     $filters = $request->all();

    //     // Filter
    //     if (isset($filters['unit_kerja'])) {
    //         $namaUnitKerja = $filters['unit_kerja'];
    //         $data_perubahan->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
    //             if (is_array($namaUnitKerja)) {
    //                 $query->whereIn('id', $namaUnitKerja);
    //             } else {
    //                 $query->where('id', '=', $namaUnitKerja);
    //             }
    //         });
    //     }

    //     if (isset($filters['jabatan'])) {
    //         $namaJabatan = $filters['jabatan'];
    //         $data_perubahan->whereHas('data_karyawans.jabatans', function ($query) use ($namaJabatan) {
    //             if (is_array($namaJabatan)) {
    //                 $query->whereIn('id', $namaJabatan);
    //             } else {
    //                 $query->where('id', '=', $namaJabatan);
    //             }
    //         });
    //     }

    //     if (isset($filters['status_karyawan'])) {
    //         $statusKaryawan = $filters['status_karyawan'];
    //         $data_perubahan->whereHas('data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
    //             if (is_array($statusKaryawan)) {
    //                 $query->whereIn('id', $statusKaryawan);
    //             } else {
    //                 $query->where('id', '=', $statusKaryawan);
    //             }
    //         });
    //     }

    //     if (isset($filters['masa_kerja'])) {
    //         $masaKerja = $filters['masa_kerja'];
    //         if (is_array($masaKerja)) {
    //             $data_perubahan->whereHas('data_karyawans', function ($query) use ($masaKerja) {
    //                 foreach ($masaKerja as $masa) {
    //                     $bulan = $masa * 12;
    //                     $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
    //                 }
    //             });
    //         } else {
    //             $bulan = $masaKerja * 12;
    //             $data_perubahan->whereHas('data_karyawans', function ($query) use ($bulan) {
    //                 $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
    //             });
    //         }
    //     }

    //     if (isset($filters['status_aktif'])) {
    //         $statusAktif = $filters['status_aktif'];
    //         $data_perubahan->whereHas('data_karyawans.users', function ($query) use ($statusAktif) {
    //             if (is_array($statusAktif)) {
    //                 $query->whereIn('status_aktif', $statusAktif);
    //             } else {
    //                 $query->where('status_aktif', '=', $statusAktif);
    //             }
    //         });
    //     }

    //     if (isset($filters['tgl_masuk'])) {
    //         $tglMasuk = $filters['tgl_masuk'];
    //         if (is_array($tglMasuk)) {
    //             $convertedDates = array_map([RandomHelper::class, 'convertToDateString'], $tglMasuk);
    //             $data_perubahan->whereHas('data_karyawans', function ($query) use ($convertedDates) {
    //                 $query->whereIn('tgl_masuk', $convertedDates);
    //             });
    //         } else {
    //             $convertedDate = RandomHelper::convertToDateString($tglMasuk);
    //             $data_perubahan->whereHas('data_karyawans', function ($query) use ($convertedDate) {
    //                 $query->where('tgl_masuk', $convertedDate);
    //             });
    //         }
    //     }

    //     if (isset($filters['agama'])) {
    //         $namaAgama = $filters['agama'];
    //         $data_perubahan->whereHas('data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
    //             if (is_array($namaAgama)) {
    //                 $query->whereIn('id', $namaAgama);
    //             } else {
    //                 $query->where('id', '=', $namaAgama);
    //             }
    //         });
    //     }

    //     if (isset($filters['jenis_kelamin'])) {
    //         $jenisKelamin = $filters['jenis_kelamin'];
    //         if (is_array($jenisKelamin)) {
    //             $data_perubahan->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
    //                 $query->where(function ($query) use ($jenisKelamin) {
    //                     foreach ($jenisKelamin as $jk) {
    //                         $query->orWhere('jenis_kelamin', $jk);
    //                     }
    //                 });
    //             });
    //         } else {
    //             $data_perubahan->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
    //                 $query->where('jenis_kelamin', $jenisKelamin);
    //             });
    //         }
    //     }

    //     if (isset($filters['pendidikan_terakhir'])) {
    //         $namaPendidikan = $filters['pendidikan_terakhir'];
    //         $data_perubahan->whereHas('data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
    //             if (is_array($namaPendidikan)) {
    //                 $query->whereIn('id', $namaPendidikan);
    //             } else {
    //                 $query->where('id', '=', $namaPendidikan);
    //             }
    //         });
    //     }

    //     if (isset($filters['jenis_karyawan'])) {
    //         $jenisKaryawan = $filters['jenis_karyawan'];
    //         if (is_array($jenisKaryawan)) {
    //             $data_perubahan->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
    //                 $query->where(function ($query) use ($jenisKaryawan) {
    //                     foreach ($jenisKaryawan as $jk) {
    //                         $query->orWhere('jenis_karyawan', $jk);
    //                     }
    //                 });
    //             });
    //         } else {
    //             $data_perubahan->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
    //                 $query->where('jenis_karyawan', $jenisKaryawan);
    //             });
    //         }
    //     }

    //     // Search
    //     if (isset($filters['search'])) {
    //         $searchTerm = '%' . $filters['search'] . '%';
    //         $data_perubahan->where(function ($query) use ($searchTerm) {
    //             $query->whereHas('data_karyawans.users', function ($query) use ($searchTerm) {
    //                 $query->where('nama', 'like', $searchTerm);
    //             })->orWhereHas('data_karyawans', function ($query) use ($searchTerm) {
    //                 $query->where('nik', 'like', $searchTerm);
    //             });
    //         });
    //     }

    //     if ($limit == 0) {
    //         $dataPerubahan = $data_perubahan->get();
    //         $paginationData = null;
    //     } else {
    //         $limit = is_numeric($limit) ? (int)$limit : 10;
    //         $dataPerubahan = $data_perubahan->paginate($limit);

    //         $paginationData = [
    //             'links' => [
    //                 'first' => $dataPerubahan->url(1),
    //                 'last' => $dataPerubahan->url($dataPerubahan->lastPage()),
    //                 'prev' => $dataPerubahan->previousPageUrl(),
    //                 'next' => $dataPerubahan->nextPageUrl(),
    //             ],
    //             'meta' => [
    //                 'current_page' => $dataPerubahan->currentPage(),
    //                 'last_page' => $dataPerubahan->lastPage(),
    //                 'per_page' => $dataPerubahan->perPage(),
    //                 'total' => $dataPerubahan->total(),
    //             ]
    //         ];
    //     }

    //     if ($dataPerubahan->isEmpty()) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data perubahan karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    //     }

    //     $formattedData = $dataPerubahan->map(function ($data_perubahan) {
    //         $relasiUser = $data_perubahan->data_karyawans->users ?? null;
    //         $relasiVerifikator = $data_perubahan->verifikator_1_users ?? null;
    //         return [
    //             'id' => $data_perubahan->id,
    //             'user' => $relasiUser ? [
    //                 'id' => $relasiUser->id,
    //                 'nama' => $relasiUser->nama,
    //                 'email_verified_at' => $relasiUser->email_verified_at,
    //                 'data_karyawan_id' => $relasiUser->data_karyawan_id,
    //                 'foto_profil' => $relasiUser->foto_profil,
    //                 'data_completion_step' => $relasiUser->data_completion_step,
    //                 'status_aktif' => $relasiUser->status_aktif,
    //                 'created_at' => $relasiUser->created_at,
    //                 'updated_at' => $relasiUser->updated_at
    //             ] : null,
    //             'kolom' => $data_perubahan->kolom,
    //             'original_data' => $data_perubahan->original_data,
    //             'updated_data' => $data_perubahan->updated_data,
    //             'status_perubahan' => $data_perubahan->status_perubahans,
    //             'verifikator_1' => $relasiVerifikator ? [
    //                 'id' => $relasiVerifikator->id,
    //                 'nama' => $relasiVerifikator->nama,
    //                 'email_verified_at' => $relasiVerifikator->email_verified_at,
    //                 'data_karyawan_id' => $relasiVerifikator->data_karyawan_id,
    //                 'foto_profil' => $relasiVerifikator->foto_profil,
    //                 'data_completion_step' => $relasiVerifikator->data_completion_step,
    //                 'status_aktif' => $relasiVerifikator->status_aktif,
    //                 'created_at' => $relasiVerifikator->created_at,
    //                 'updated_at' => $relasiVerifikator->updated_at
    //             ] : null,
    //             'alasan' => $data_perubahan->alasan ?? null,
    //             'created_at' => $data_perubahan->created_at,
    //             'updated_at' => $data_perubahan->updated_at
    //         ];
    //     });

    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => 'Data perubahan karyawan ditemukan.',
    //         'data' => $formattedData,
    //         'pagination' => $paginationData
    //     ], Response::HTTP_OK);
    // }

    // public function verifikasi_perubahan(Request $request, $id)
    // {
    //     if (!Gate::allows('verifikasi verifikator1')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $riwayat = RiwayatPerubahan::find($id);

    //     if (!$riwayat) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Riwayat perubahan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    //     }

    //     $status = $request->input('status');
    //     $alasan = $request->input('alasan', null);

    //     // Validasi input status
    //     if (!in_array($status, [1, 2, 3])) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Status tidak valid. Harus berupa 1 (menunggu verifikasi), 2 (terverifikasi), atau 3 (ditolak).'), Response::HTTP_BAD_REQUEST);
    //     }

    //     if ($riwayat->status_perubahan_id == 3) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Status sudah ditolak dan tidak bisa diupdate lagi.'), Response::HTTP_BAD_REQUEST);
    //     }

    //     if ($riwayat->status_perubahan_id == 2 && $status != 3) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Status yang telah diverifikasi hanya bisa diupdate ke status ditolak.'), Response::HTTP_BAD_REQUEST);
    //     }

    //     if ($riwayat->status_perubahan_id == 1 || ($riwayat->status_perubahan_id == 2 && $status == 3)) {
    //         $riwayat->status_perubahan_id = $status;
    //         $riwayat->verifikator_1 = auth()->user()->id; // Set verifikator_1 dengan user yang sedang login

    //         if ($status == 3 && $alasan) {
    //             $riwayat->alasan = $alasan; // Simpan alasan penolakan jika ada
    //         }

    //         $riwayat->save();

    //         return response()->json([
    //             'status' => Response::HTTP_OK,
    //             'message' => "Status riwayat perubahan berhasil diperbarui menjadi '{$riwayat->status_perubahans->label}'.",
    //             'data' => $riwayat
    //         ], Response::HTTP_OK);
    //     }

    //     return response()->json([
    //         'status' => Response::HTTP_BAD_REQUEST,
    //         'message' => 'Perubahan status tidak valid. Status saat ini: ' . $riwayat->status_perubahans->label
    //     ], Response::HTTP_BAD_REQUEST);
    // }

    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $limit = $request->input('limit', 10);

        $data_perubahan = RiwayatPerubahan::with(['data_karyawans.users', 'status_perubahans', 'verifikator_1_users'])
            ->orderBy('created_at', 'desc');

        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $data_perubahan->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $data_perubahan->whereHas('data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $data_perubahan->whereHas('data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $data_perubahan->whereHas('data_karyawans.users', function ($query) use ($statusAktif) {
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
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $data_perubahan->whereHas('data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $data_perubahan->whereHas('data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $data_perubahan->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $data_perubahan->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $data_perubahan->where(function ($query) use ($searchTerm) {
                $query->whereHas('data_karyawans.users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($limit == 0) {
            $dataPerubahan = $data_perubahan->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataPerubahan = $data_perubahan->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataPerubahan->url(1),
                    'last' => $dataPerubahan->url($dataPerubahan->lastPage()),
                    'prev' => $dataPerubahan->previousPageUrl(),
                    'next' => $dataPerubahan->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataPerubahan->currentPage(),
                    'last_page' => $dataPerubahan->lastPage(),
                    'per_page' => $dataPerubahan->perPage(),
                    'total' => $dataPerubahan->total(),
                ]
            ];
        }

        if ($dataPerubahan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data perubahan karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataPerubahan->map(function ($data_perubahan) {
            $relasiUser = $data_perubahan->data_karyawans->users ?? null;
            $relasiVerifikator = $data_perubahan->verifikator_1_users ?? null;

            return [
                'id' => $data_perubahan->id,
                'user' => $relasiUser ? [
                    'id' => $relasiUser->id,
                    'nama' => $relasiUser->nama,
                    'email_verified_at' => $relasiUser->email_verified_at,
                    'data_karyawan_id' => $relasiUser->data_karyawan_id,
                    'foto_profil' => $relasiUser->foto_profil,
                    'data_completion_step' => $relasiUser->data_completion_step,
                    'status_aktif' => $relasiUser->status_aktif,
                    'created_at' => $relasiUser->created_at,
                    'updated_at' => $relasiUser->updated_at
                ] : null,
                'kolom' => $data_perubahan->kolom,
                'original_data' => $data_perubahan->original_data,
                'updated_data' => $data_perubahan->updated_data,
                'status_perubahan' => $data_perubahan->status_perubahans,
                'verifikator_1' => $relasiVerifikator ? [
                    'id' => $relasiVerifikator->id,
                    'nama' => $relasiVerifikator->nama,
                    'email_verified_at' => $relasiVerifikator->email_verified_at,
                    'data_karyawan_id' => $relasiVerifikator->data_karyawan_id,
                    'foto_profil' => $relasiVerifikator->foto_profil,
                    'data_completion_step' => $relasiVerifikator->data_completion_step,
                    'status_aktif' => $relasiVerifikator->status_aktif,
                    'created_at' => $relasiVerifikator->created_at,
                    'updated_at' => $relasiVerifikator->updated_at
                ] : null,
                'alasan' => $data_perubahan->alasan ?? null,
                'created_at' => $data_perubahan->created_at,
                'updated_at' => $data_perubahan->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data perubahan karyawan ditemukan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function verifikasiPerubahan(Request $request, $id)
    {
        if (!Gate::allows('verifikasi verifikator1')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari riwayat perubahan berdasarkan ID
        $riwayat = RiwayatPerubahan::find($id);

        if (!$riwayat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Riwayat perubahan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $status_perubahan_id = $riwayat->status_perubahan_id;

        // Logika verifikasi disetujui
        if ($request->has('verifikasi_disetujui') && $request->verifikasi_disetujui == 1) {
            // Jika status_perubahan_id = 1 (menunggu) atau 3 (ditolak sebelumnya)
            if ($status_perubahan_id == 1 || $status_perubahan_id == 3) {
                $riwayat->status_perubahan_id = 2; // Update status ke diverifikasi
                $riwayat->verifikator_1 = Auth::id(); // Set verifikator tahap 1
                $riwayat->alasan = null; // Reset alasan penolakan
                $riwayat->save();

                // Lakukan pembaruan data pada tabel asli
                $this->updateOriginalData($riwayat);

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi untuk riwayat perubahan '{$riwayat->kolom}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Riwayat perubahan '{$riwayat->kolom}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
            }
        }
        // Logika verifikasi ditolak
        elseif ($request->has('verifikasi_ditolak') && $request->verifikasi_ditolak == 1) {
            // Jika status_perubahan_id = 1 (menunggu)
            if ($status_perubahan_id == 1) {
                $riwayat->status_perubahan_id = 3; // Update status ke ditolak
                $riwayat->verifikator_1 = Auth::id(); // Set verifikator tahap 1
                $riwayat->alasan = 'Verifikasi ditolak karena: ' . $request->input('alasan', null);
                $riwayat->save();

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi untuk riwayat perubahan '{$riwayat->kolom}' telah ditolak."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Riwayat perubahan '{$riwayat->kolom}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function updateOriginalData($riwayat)
    {
        // Identifikasi jenis perubahan
        switch ($riwayat->jenis_perubahan) {
            case 'Personal':
                $dataKaryawan = DataKaryawan::find($riwayat->data_karyawan_id);
                if ($dataKaryawan) {
                    $dataKaryawan->{$riwayat->kolom} = $riwayat->updated_data;
                    $dataKaryawan->save();
                }
                break;

            case 'Berkas':
                $berkas = Berkas::where('id', function ($query) use ($riwayat) {
                    $query->select('berkas_id')
                        ->from('perubahan_berkas')
                        ->where('riwayat_perubahan_id', $riwayat->id);
                })->whereHas('users', function ($query) use ($riwayat) {
                    $query->where('data_karyawan_id', $riwayat->data_karyawan_id);
                })->first();

                if ($berkas) {
                    // Update kolom yang sesuai di tabel berkas
                    $berkas->{$riwayat->kolom} = $riwayat->updated_data;
                    $berkas->save();
                }
                break;

            case 'Keluarga':
                $perubahanKeluarga = PerubahanKeluarga::where('riwayat_perubahan_id', $riwayat->id)->first();

                if ($perubahanKeluarga) {
                    if ($perubahanKeluarga->jenis_perubahan == 1) {
                        DataKeluarga::create([
                            'data_karyawan_id' => $riwayat->data_karyawan_id,
                            'nama_keluarga' => $perubahanKeluarga->nama_keluarga,
                            'hubungan' => $perubahanKeluarga->hubungan,
                            'pendidikan_terakhir' => $perubahanKeluarga->pendidikan_terakhir,
                            'status_hidup' => $perubahanKeluarga->status_hidup,
                            'pekerjaan' => $perubahanKeluarga->pekerjaan,
                            'no_hp' => $perubahanKeluarga->no_hp,
                            'email' => $perubahanKeluarga->email,
                        ]);
                    } else {
                        $dataKeluarga = DataKeluarga::where('id', $perubahanKeluarga->data_keluarga_id)
                            ->where('data_karyawan_id', $riwayat->data_karyawan_id)
                            ->first();

                        if ($dataKeluarga) {
                            $dataKeluarga->{$riwayat->kolom} = $riwayat->updated_data;
                            $dataKeluarga->save();
                        }
                    }
                }
                break;

            default:
                Log::warning('No action taken for jenis_perubahan: ' . $riwayat->jenis_perubahan);
                break;
        }
    }
}
