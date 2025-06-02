<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Carbon\Carbon;
use App\Models\Notifikasi;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use Illuminate\Http\Response;
use App\Models\ModulVerifikasi;
use App\Models\RelasiVerifikasi;
use App\Models\RiwayatPerubahan;
use App\Models\PerubahanKeluarga;
use App\Models\KategoriPendidikan;
use App\Helpers\CalculateBMIHelper;
use App\Helpers\StorageServerHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\Berkas;
use Illuminate\Support\Facades\DB;

class DataRiwayatPerubahanController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view riwayatPerubahan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = auth()->user();
        $userId = $user->id;

        // Periksa apakah user adalah Super Admin
        $isSuperAdmin = $user->hasRole('Super Admin');

        // Jika bukan super admin, cek apakah dia verifikator untuk modul riwayat perubahan
        $userDivVerifiedIds = [];
        if (!$isSuperAdmin) {
            $relasi = RelasiVerifikasi::where('verifikator', $userId)
                ->where('modul_verifikasi', 1)
                ->first();

            if ($relasi) {
                // Ambil array user_diverifikasi dari relasi_verifikasis
                $userDivVerifiedIds = $relasi->user_diverifikasi ?? [];
            }
        }

        $limit = $request->input('limit', 10);

        $data_perubahan = RiwayatPerubahan::with(['data_karyawans.users', 'status_perubahans', 'verifikator_1_users'])
            ->orderBy('created_at', 'desc');

        // Terapkan filter data berdasarkan role/verifikator
        if (!$isSuperAdmin) {
            if (empty($userDivVerifiedIds)) {
                // Bukan super admin dan bukan verifikator => kosongkan hasil
                // Jadi, query dibuat WHERE 0=1 agar kosong
                $data_perubahan->whereRaw('0=1');
            } else {
                // Filter hanya user_id yang termasuk dalam user_diverifikasi
                $data_perubahan->whereHas('data_karyawans.users', function ($query) use ($userDivVerifiedIds) {
                    $query->whereIn('id', $userDivVerifiedIds);
                });
            }
        }

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
            $currentDate = Carbon::now('Asia/Jakarta');
            if (is_array($masaKerja)) {
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($bulan, $currentDate) {
                    $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
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
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                    $query->whereIn('tgl_masuk', $tglMasuk);
                });
            } else {
                $data_perubahan->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                    $query->where('tgl_masuk', $tglMasuk);
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

        if (isset($filters['jenis_kompetensi'])) {
            $jenisKaryawan = $filters['jenis_kompetensi'];
            if (is_array($jenisKaryawan)) {
                $data_perubahan->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_kompetensi', $jk);
                        }
                    });
                });
            } else {
                $data_perubahan->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_kompetensi', $jenisKaryawan);
                });
            }
        }

        if (isset($filters['status_verfikasi'])) {
            $statusverfikasi = $filters['status_verfikasi'];
            $data_perubahan->whereHas('status_perubahans', function ($query) use ($statusverfikasi) {
                if (is_array($statusverfikasi)) {
                    $query->whereIn('id', $statusverfikasi);
                } else {
                    $query->where('id', '=', $statusverfikasi);
                }
            });
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

        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = $dataPerubahan->map(function ($data_perubahan) use ($baseUrl) {
            $relasiUser = $data_perubahan->data_karyawans->users ?? null;
            $relasiVerifikator = $data_perubahan->verifikator_1_users ?? null;

            $originalData = $data_perubahan->original_data ?? null;
            $updatedData = $data_perubahan->updated_data;

            if ($data_perubahan->jenis_perubahan === 'Keluarga') {
                if (is_array($originalData)) {
                    foreach ($originalData as &$item) {
                        if (isset($item['pendidikan_terakhir'])) {
                            $item['pendidikan_terakhir'] = KategoriPendidikan::find($item['pendidikan_terakhir']) ?? null;
                        }
                    }
                }

                if (is_array($updatedData)) {
                    foreach ($updatedData as &$item) {
                        if (isset($item['pendidikan_terakhir'])) {
                            $item['pendidikan_terakhir'] = KategoriPendidikan::find($item['pendidikan_terakhir']) ?? null;
                        }
                    }
                }


                if (is_array($originalData)) {
                    foreach ($originalData as &$item) {
                        if (isset($item['kategori_agama_id'])) {
                            $item['kategori_agama_id'] = KategoriAgama::find($item['kategori_agama_id']) ?? null;
                        }
                    }
                }

                if (is_array($updatedData)) {
                    foreach ($updatedData as &$item) {
                        if (isset($item['kategori_agama_id'])) {
                            $item['kategori_agama_id'] = KategoriAgama::find($item['kategori_agama_id']) ?? null;
                        }
                    }
                }

                if (is_array($originalData)) {
                    foreach ($originalData as &$item) {
                        if (isset($item['kategori_darah_id'])) {
                            $item['kategori_darah_id'] = KategoriDarah::find($item['kategori_darah_id']) ?? null;
                        }
                    }
                }

                if (is_array($updatedData)) {
                    foreach ($updatedData as &$item) {
                        if (isset($item['kategori_darah_id'])) {
                            $item['kategori_darah_id'] = KategoriDarah::find($item['kategori_darah_id']) ?? null;
                        }
                    }
                }
            }

            if ($data_perubahan->jenis_perubahan === 'Personal') {
                if (in_array($data_perubahan->kolom, ['agama', 'golongan_darah', 'pendidikan_terakhir', 'foto_profil'])) {
                    if ($data_perubahan->kolom === 'agama') {
                        $originalData = KategoriAgama::find($originalData) ?? $originalData;
                        $updatedData = KategoriAgama::find($updatedData) ?? $updatedData;
                    } elseif ($data_perubahan->kolom === 'golongan_darah') {
                        $originalData = KategoriDarah::find($originalData) ?? $originalData;
                        $updatedData = KategoriDarah::find($updatedData) ?? $updatedData;
                    } elseif ($data_perubahan->kolom === 'pendidikan_terakhir') {
                        $originalData = KategoriPendidikan::find($originalData) ?? $originalData;
                        $updatedData = KategoriPendidikan::find($updatedData) ?? $updatedData;
                    } elseif ($data_perubahan->kolom === 'foto_profil') {
                        $originalBerkas = Berkas::find($originalData);
                        $updatedBerkas = Berkas::find($updatedData);

                        $originalData = $this->formatFotoProfil($originalBerkas ?? null, $baseUrl);
                        $updatedData = $this->formatFotoProfil($updatedBerkas ?? null, $baseUrl);

                        // $originalData = $originalBerkas ? [
                        //     'id' => $originalBerkas->id,
                        //     'user_id' => $originalBerkas->user_id,
                        //     'file_id' => $originalBerkas->file_id,
                        //     'nama' => $originalBerkas->nama,
                        //     'nama_file' => $originalBerkas->nama_file,
                        //     'path' => $baseUrl . $originalBerkas->path,
                        //     'ext' => $originalBerkas->ext,
                        //     'size' => $originalBerkas->size,
                        // ] : $originalData;

                        // $updatedData = $updatedBerkas ? [
                        //     'id' => $updatedBerkas->id,
                        //     'user_id' => $updatedBerkas->user_id,
                        //     'file_id' => $updatedBerkas->file_id,
                        //     'nama' => $updatedBerkas->nama,
                        //     'nama_file' => $updatedBerkas->nama_file,
                        //     'path' => $baseUrl . $updatedBerkas->path,
                        //     'ext' => $updatedBerkas->ext,
                        //     'size' => $updatedBerkas->size,
                        // ] : $updatedData;
                    }
                }
            }

            $userId = $data_perubahan->data_karyawans->users->id ?? null;
            $relasiVerifikasi = $userId ? RelasiVerifikasi::whereJsonContains('user_diverifikasi', (int) $userId)
                ->where('modul_verifikasi', 1)
                ->get() : collect();

            // Mendapatkan max order dari modul_verifikasis untuk jenis perubahan (modul_verifikasi = 1)
            $modulVerifikasi = ModulVerifikasi::where('id', 1)->first();
            $maxOrder = $modulVerifikasi ? $modulVerifikasi->max_order : 0;

            // Lakukan loop sebanyak max order
            $formattedRelasiVerifikasi = [];
            for ($i = 1; $i <= $maxOrder; $i++) {
                $verifikasiForOrder = $relasiVerifikasi->firstWhere('order', $i);
                $formattedRelasiVerifikasi[] = $verifikasiForOrder ? [
                    'id' => $verifikasiForOrder->id,
                    'nama' => $verifikasiForOrder->nama,
                    'verifikator' => [
                        'id' => $verifikasiForOrder->users->id,
                        'nama' => $verifikasiForOrder->users->nama,
                        'username' => $verifikasiForOrder->users->username,
                        'email_verified_at' => $verifikasiForOrder->users->email_verified_at,
                        'data_karyawan_id' => $verifikasiForOrder->users->data_karyawan_id,
                        'foto_profil' => $verifikasiForOrder->users->foto_profiles ? [
                            'id' => $verifikasiForOrder->users->foto_profiles->id,
                            'user_id' => $verifikasiForOrder->users->foto_profiles->user_id,
                            'file_id' => $verifikasiForOrder->users->foto_profiles->file_id,
                            'nama' => $verifikasiForOrder->users->foto_profiles->nama,
                            'nama_file' => $verifikasiForOrder->users->foto_profiles->nama_file,
                            'path' => $baseUrl . $verifikasiForOrder->users->foto_profiles->path,
                            'ext' => $verifikasiForOrder->users->foto_profiles->ext,
                            'size' => $verifikasiForOrder->users->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $verifikasiForOrder->users->data_completion_step,
                        'status_aktif' => $verifikasiForOrder->users->status_aktif,
                        'created_at' => $verifikasiForOrder->users->created_at,
                        'updated_at' => $verifikasiForOrder->users->updated_at
                    ],
                    'order' => $verifikasiForOrder->order,
                    'user_diverifikasi' => $verifikasiForOrder->user_diverifikasi,
                    'modul_verifikasi' => $verifikasiForOrder->modul_verifikasi,
                    'created_at' => $verifikasiForOrder->created_at,
                    'updated_at' => $verifikasiForOrder->updated_at
                ] : [
                    'id' => null,
                    'nama' => null,
                    'verifikator' => null,
                    'order' => $i,
                    'user_diverifikasi' => null,
                    'modul_verifikasi' => null,
                    'created_at' => null,
                    'updated_at' => null
                ];
            }

            return [
                'id' => $data_perubahan->id,
                'user' => $relasiUser ? [
                    'id' => $relasiUser->id,
                    'nama' => $relasiUser->nama,
                    'username' => $relasiUser->username,
                    'email_verified_at' => $relasiUser->email_verified_at,
                    'data_karyawan_id' => $relasiUser->data_karyawan_id,
                    // 'foto_profil' => $this->formatFotoProfil($relasiUser->foto_profiles ?? null, $baseUrl),
                    'foto_profil' => $relasiUser->foto_profiles ? [
                        'id' => $relasiUser->foto_profiles->id,
                        'user_id' => $relasiUser->foto_profiles->user_id,
                        'file_id' => $relasiUser->foto_profiles->file_id,
                        'nama' => $relasiUser->foto_profiles->nama,
                        'nama_file' => $relasiUser->foto_profiles->nama_file,
                        'path' => $baseUrl . $relasiUser->foto_profiles->path,
                        'ext' => $relasiUser->foto_profiles->ext,
                        'size' => $relasiUser->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $relasiUser->data_completion_step,
                    'status_aktif' => $relasiUser->status_aktif,
                    'created_at' => $relasiUser->created_at,
                    'updated_at' => $relasiUser->updated_at
                ] : null,
                'jenis_perubahan' => $data_perubahan->jenis_perubahan,
                'kolom' => $data_perubahan->kolom,
                'original_data' => $originalData,
                'updated_data' => $updatedData,
                'status_perubahan' => $data_perubahan->status_perubahans,
                'verifikator_1' => $relasiVerifikator ? [
                    'id' => $relasiVerifikator->id,
                    'nama' => $relasiVerifikator->nama,
                    'email_verified_at' => $relasiVerifikator->email_verified_at,
                    'data_karyawan_id' => $relasiVerifikator->data_karyawan_id,
                    'foto_profil' => $relasiVerifikator->foto_profiles ? [
                        'id' => $relasiVerifikator->foto_profiles->id,
                        'user_id' => $relasiVerifikator->foto_profiles->user_id,
                        'file_id' => $relasiVerifikator->foto_profiles->file_id,
                        'nama' => $relasiVerifikator->foto_profiles->nama,
                        'nama_file' => $relasiVerifikator->foto_profiles->nama_file,
                        'path' => $baseUrl . $relasiVerifikator->foto_profiles->path,
                        'ext' => $relasiVerifikator->foto_profiles->ext,
                        'size' => $relasiVerifikator->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $relasiVerifikator->data_completion_step,
                    'status_aktif' => $relasiVerifikator->status_aktif,
                    'created_at' => $relasiVerifikator->created_at,
                    'updated_at' => $relasiVerifikator->updated_at
                ] : null,
                'alasan' => $data_perubahan->alasan ?? null,
                'relasi_verifikasi' => $formattedRelasiVerifikasi,
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
        // 1. Dapatkan ID user yang login
        $verifikatorId = Auth::id();

        // 2. Dapatkan riwayat perubahan berdasarkan ID
        $riwayat = RiwayatPerubahan::find($id);
        if (!$riwayat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Riwayat perubahan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // 3. Jika pengguna bukan Super Admin, lakukan pengecekan relasi verifikasi
        if (!Auth::user()->hasRole('Super Admin')) {
            // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
            $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                ->where('modul_verifikasi', 1)
                ->where('order', 1)
                ->first();

            if (!$relasiVerifikasi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => "Anda tidak memiliki hak akses untuk verifikasi riwayat perubahan data dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                    'relasi_verifikasi' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            // 4. Dapatkan karyawan yang mengajukan perubahan dengan user_id di tabel data_karyawans
            $pengajuPerubahanUserId = $riwayat->data_karyawans->user_id;

            // 5. Samakan user_id pengajuan perubahan dengan string array user_diverifikasi di tabel relasi_verifikasis
            $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
            if (!is_array($userDiverifikasi)) {
                Log::warning('Kesalahan format data user diverifikasi pada verif 1 perubahan data');
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if (!in_array($pengajuPerubahanUserId, $userDiverifikasi)) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi perubahan ini karena karyawan tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
            }

            // 6. Validasi nilai kolom order dan status_perubahan_id
            if ($relasiVerifikasi->order != 1) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Perubahan ini tidak dalam status untuk disetujui pada tahap 1.'), Response::HTTP_BAD_REQUEST);
            }
        }

        $status_perubahan_id = $riwayat->status_perubahan_id;

        DB::beginTransaction();
        // Logika verifikasi disetujui
        if ($request->has('verifikasi_disetujui') && $request->verifikasi_disetujui == 1) {
            if ($status_perubahan_id == 1) {
                $riwayat->status_perubahan_id = 2;
                $riwayat->verifikator_1 = Auth::id();
                $riwayat->alasan = null;
                $riwayat->save();

                Log::info('Data riwayat berhasil disetujui.', $riwayat->toArray());

                // Lakukan pembaruan data pada tabel asli
                $this->updateOriginalData($riwayat);

                DB::commit();

                $this->createNotifikasiPerubahan($riwayat, 'Disetujui');

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi untuk riwayat perubahan '{$riwayat->kolom}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Riwayat perubahan '{$riwayat->kolom}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
            }
        }
        // Logika verifikasi ditolak
        elseif ($request->has('verifikasi_ditolak') && $request->verifikasi_ditolak == 1) {
            if ($status_perubahan_id == 1) {
                $riwayat->status_perubahan_id = 3;
                $riwayat->verifikator_1 = Auth::id();
                $riwayat->alasan = $request->input('alasan');
                $riwayat->save();

                if ($riwayat->jenis_perubahan === 'Personal' && $riwayat->kolom === 'foto_profil') {
                    $berkasId = $riwayat->updated_data;
                    if ($berkasId) {
                        $berkasLama = Berkas::find($berkasId);
                        if ($berkasLama) {
                            try {
                                StorageServerHelper::deleteFromServer($berkasLama->file_id);
                            } catch (\Exception $e) {
                                Log::warning("Gagal hapus foto_profil 'Perubahan Data' dari server (file_id: {$berkasLama->file_id}): " . $e->getMessage());
                            }

                            $berkasLama->delete();
                        }
                    }
                }

                Log::info('Riwayat perubahan berhasil ditolak', $riwayat->toArray());

                DB::commit();

                $this->createNotifikasiPerubahan($riwayat, 'Ditolak');

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi untuk riwayat perubahan '{$riwayat->kolom}' telah ditolak."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Riwayat perubahan '{$riwayat->kolom}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
            }
            // DB::commit();
        } else {
            DB::rollBack();
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
                    // Jika kolom yang diubah adalah tinggi_badan, ambil berat_badan dari data asli
                    $bmiResult = null;
                    if ($riwayat->kolom === 'tinggi_badan') {
                        $updatedTinggiBadan = $riwayat->updated_data;
                        $beratBadan = $dataKaryawan->berat_badan; // Ambil berat_badan dari data asli
                        if ($beratBadan) {
                            $bmiResult = CalculateBMIHelper::calculateBMI($beratBadan, $updatedTinggiBadan);
                        }
                    }
                    // Jika kolom yang diubah adalah berat_badan, ambil tinggi_badan dari data asli
                    elseif ($riwayat->kolom === 'berat_badan') {
                        $updatedBeratBadan = $riwayat->updated_data;
                        $tinggiBadan = $dataKaryawan->tinggi_badan; // Ambil tinggi_badan dari data asli
                        if ($tinggiBadan) {
                            $bmiResult = CalculateBMIHelper::calculateBMI($updatedBeratBadan, $tinggiBadan);
                        }
                    }

                    if ($riwayat->kolom === 'agama') {
                        $riwayat->kolom = 'kategori_agama_id';
                    } elseif ($riwayat->kolom === 'golongan_darah') {
                        $riwayat->kolom = 'kategori_darah_id';
                    }
                    // Update data sesuai dengan kolom yang diubah
                    $dataKaryawan->{$riwayat->kolom} = $riwayat->updated_data;
                    if ($bmiResult) {
                        $dataKaryawan->bmi_value = $bmiResult['bmi_value'];
                        $dataKaryawan->bmi_ket = $bmiResult['bmi_ket'];
                    }
                    $dataKaryawan->save();

                    // Update foto_profil di tabel users
                    if ($riwayat->kolom === 'foto_profil') {
                        $user = $dataKaryawan->users;
                        if ($user) {
                            if ($riwayat->updated_data === null) {
                                if ($user->foto_profil) {
                                    // Hapus foto profil lama jika ada
                                    $berkasLama = Berkas::find($user->foto_profil);
                                    if ($berkasLama) {
                                        try {
                                            StorageServerHelper::deleteFromServer($berkasLama->file_id);
                                        } catch (\Exception $e) {
                                            Log::warning("Gagal hapus foto_profil lama dari server (file_id: {$berkasLama->file_id}): " . $e->getMessage());
                                        }

                                        // Set foto_profil ke NULL sebelum hapus berkas
                                        $user->foto_profil = null;
                                        $user->save();

                                        $berkasLama->delete();
                                    }
                                }
                            } else {
                                // Update foto_profil ke id berkas baru
                                $user->foto_profil = $riwayat->updated_data;
                                $user->save();
                            }
                        }
                    }
                }
                break;

            case 'Keluarga':
                // Decode first
                $updatedFamilyData = is_string($riwayat->updated_data) ? json_decode($riwayat->updated_data, true) : $riwayat->updated_data;

                if (is_array($updatedFamilyData)) {
                    PerubahanKeluarga::where('riwayat_perubahan_id', $riwayat->id)->delete();
                    $dkeluarga = DataKeluarga::where('data_karyawan_id', $riwayat->data_karyawan_id)->get();
                    if ($dkeluarga->isNotEmpty()) {
                        foreach ($dkeluarga as $d) {
                            PerubahanKeluarga::where('data_keluarga_id', $d->id)->delete();
                            DataKeluarga::where('id', $d->id)->delete();
                        }
                    }

                    foreach ($updatedFamilyData as $update) {
                        DataKeluarga::create([
                            'data_karyawan_id' => $riwayat->data_karyawan_id,
                            'nama_keluarga' => $update['nama_keluarga'],
                            'hubungan' => $update['hubungan'],
                            'tgl_lahir' => $update['tgl_lahir'],
                            'tempat_lahir' => $update['tempat_lahir'],
                            'jenis_kelamin' => $update['jenis_kelamin'],
                            'kategori_agama_id' => $update['kategori_agama_id'],
                            'kategori_darah_id' => $update['kategori_darah_id'],
                            'no_rm' => $update['no_rm'],
                            'pendidikan_terakhir' => $update['pendidikan_terakhir'],
                            'status_hidup' => $update['status_hidup'],
                            'pekerjaan' => $update['pekerjaan'],
                            'no_hp' => $update['no_hp'],
                            'email' => $update['email'],
                            'status_keluarga_id' => 2,
                            'is_bpjs' => $update['is_bpjs'],
                            'verifikator_1' => Auth::user()->id,
                        ]);
                    }
                }
                break;

            default:
                Log::warning('No action taken for jenis_perubahan: ' . $riwayat->jenis_perubahan);
                break;
        }
    }

    protected function formatFotoProfil($fotoProfil, $baseUrl)
    {
        if (!$fotoProfil) {
            return null;
        }

        return [
            'id' => $fotoProfil->id,
            'user_id' => $fotoProfil->user_id,
            'file_id' => $fotoProfil->file_id,
            'nama' => $fotoProfil->nama,
            'nama_file' => $fotoProfil->nama_file,
            'path' => $baseUrl . $fotoProfil->path,
            'ext' => $fotoProfil->ext,
            'size' => $fotoProfil->size,
        ];
    }

    private function createNotifikasiPerubahan($riwayat, $status)
    {
        try {
            $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
            $users = $riwayat->data_karyawans->users;

            $message = "Perubahan data '{$users->nama}' telah '{$statusText}', silahkan lakukan cek data terbaru anda.";
            $messageSuperAdmin = "Notifikasi untuk Super Admin: Perubahan data '{$users->nama}' telah '{$statusText}'.";

            $userIds = [$users->id, 1]; // Daftar user_id, termasuk user dan Super Admin

            foreach ($userIds as $userId) {
                $messageToSend = $userId === 1 ? $messageSuperAdmin : $message;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 11,
                    'user_id' => $userId,
                    'message' => $messageToSend,
                    'is_read' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('| Perubahan Data | - Error function createNotifikasiPerubahan: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
