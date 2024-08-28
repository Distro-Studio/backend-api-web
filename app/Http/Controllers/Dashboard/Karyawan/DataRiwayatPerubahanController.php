<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\RiwayatPerubahan;
use App\Models\PerubahanKeluarga;
use App\Models\PerubahanPersonal;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataRiwayatPerubahanController extends Controller
{
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

        $formattedData = $dataPerubahan->map(function ($data_perubahan) {
            $relasiUser = $data_perubahan->data_karyawans->users ?? null;
            $relasiVerifikator = $data_perubahan->verifikator_1_users ?? null;

            $originalData = $data_perubahan->original_data;
            $updatedData = $data_perubahan->updated_data;

            if ($data_perubahan->jenis_perubahan === 'Keluarga') {
                $keluargaChanges = $data_perubahan->perubahan_keluargas;

                if ($keluargaChanges->isNotEmpty()) {
                    $firstKeluargaChange = $keluargaChanges->first();
                    $dataKeluarga = DataKeluarga::find($firstKeluargaChange->data_keluarga_id);

                    if ($dataKeluarga) {
                        $originalData = [[
                            'id' => $dataKeluarga->id,
                            'nama_keluarga' => $dataKeluarga->nama_keluarga ?? $data_perubahan->original_data,
                            'hubungan' => $dataKeluarga->hubungan ?? null,
                            'pendidikan_terakhir' => $dataKeluarga->pendidikan_terakhir ?? null,
                            'status_hidup' => $dataKeluarga->status_hidup ?? null,
                            'pekerjaan' => $dataKeluarga->pekerjaan ?? null,
                            'no_hp' => $dataKeluarga->no_hp ?? null,
                            'email' => $dataKeluarga->email ?? null,
                            'created_at' => $dataKeluarga->created_at,
                            'updated_at' => $dataKeluarga->updated_at
                        ]];
                    }

                    // Updated data might be a collection of changes
                    $updatedData = $keluargaChanges->map(function ($change) {
                        return [
                            'id' => $change->id,
                            'data_keluarga_id' => $change->data_keluarga_id,
                            'nama_keluarga' => $change->nama_keluarga,
                            'hubungan' => $change->hubungan,
                            'pendidikan_terakhir' => $change->pendidikan_terakhir,
                            'status_hidup' => $change->status_hidup,
                            'pekerjaan' => $change->pekerjaan,
                            'no_hp' => $change->no_hp,
                            'email' => $change->email,
                            'created_at' => $change->created_at,
                            'updated_at' => $change->updated_at
                        ];
                    });
                }
            }

            if ($data_perubahan->jenis_perubahan === 'Personal') {
                $dataKaryawan = $data_perubahan->data_karyawans;
                $originalData = [
                    [
                        'id' => $dataKaryawan->id,
                        'tempat_lahir' => $dataKaryawan->tempat_lahir,
                        'tgl_lahir' => $dataKaryawan->tgl_lahir,
                        'no_hp' => $dataKaryawan->no_hp,
                        'jenis_kelamin' => $dataKaryawan->jenis_kelamin,
                        'nik_ktp' => $dataKaryawan->nik_ktp,
                        'no_kk' => $dataKaryawan->no_kk,
                        'agama' => $dataKaryawan->kategori_agamas, // agama_id
                        'golongan_darah' => $dataKaryawan->kategori_darahs, // golongan_darah_id
                        'tinggi_badan' => $dataKaryawan->tinggi_badan,
                        'berat_badan' => $dataKaryawan->berat_badan,
                        'alamat' => $dataKaryawan->alamat,
                        'no_ijazah' => $dataKaryawan->no_ijazah,
                        'tahun_lulus' => $dataKaryawan->tahun_lulus,
                        'pendidikan_terakhir' => $dataKaryawan->kategori_pendidikans, // pendidikan_terakhir_id
                        'gelar_depan' => $dataKaryawan->gelar_depan,
                        'updated_at' => $dataKaryawan->updated_at
                    ]
                ];

                $personalChanges = $data_perubahan->perubahan_personals;
                if ($personalChanges->isNotEmpty()) {
                    $updatedData = $personalChanges->map(function ($change) {
                        return [
                            'id' => $change->id,
                            'tempat_lahir' => $change->tempat_lahir,
                            'tgl_lahir' => $change->tgl_lahir,
                            'no_hp' => $change->no_hp,
                            'jenis_kelamin' => $change->jenis_kelamin,
                            'nik_ktp' => $change->nik_ktp,
                            'no_kk' => $change->no_kk,
                            'agama' => $change->kategori_agamas, // agama_id
                            'golongan_darah' => $change->kategori_darahs, // golongan_darah_id
                            'tinggi_badan' => $change->tinggi_badan,
                            'berat_badan' => $change->berat_badan,
                            'alamat' => $change->alamat,
                            'no_ijazah' => $change->no_ijazah,
                            'tahun_lulus' => $change->tahun_lulus,
                            'pendidikan_terakhir' => $change->kategori_pendidikans, // pendidikan_terakhir_id
                            'gelar_depan' => $change->gelar_depan,
                            'updated_at' => $change->updated_at
                        ];
                    });
                }
            }

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
        if (!Gate::allows('verifikasi1 riwayatPerubahan')) {
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
            if ($status_perubahan_id == 1) {
                $riwayat->status_perubahan_id = 2;
                $riwayat->verifikator_1 = Auth::id();
                $riwayat->alasan = null;
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
            if ($status_perubahan_id == 1) {
                $riwayat->status_perubahan_id = 3;
                $riwayat->verifikator_1 = Auth::id();
                $riwayat->alasan = $request->input('alasan', null);
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
                // if ($dataKaryawan) {
                //     // $dataKaryawan->{$riwayat->kolom} = $riwayat->updated_data;
                //     $dataKaryawan->{$riwayat->kolom} = $riwayat->updated_data[0][$riwayat->kolom];
                //     $dataKaryawan->save();
                // }
                if ($dataKaryawan) {
                    $perubahanPersonal = PerubahanPersonal::where('riwayat_perubahan_id', $riwayat->id)->get();
                    foreach ($perubahanPersonal as $perubahan) {
                        $updateData = [];
                        if (!is_null($perubahan->tempat_lahir)) $updateData['tempat_lahir'] = $perubahan->tempat_lahir;
                        if (!is_null($perubahan->tgl_lahir)) $updateData['tgl_lahir'] = $perubahan->tgl_lahir;
                        if (!is_null($perubahan->no_hp)) $updateData['no_hp'] = $perubahan->no_hp;
                        if (!is_null($perubahan->jenis_kelamin)) $updateData['jenis_kelamin'] = $perubahan->jenis_kelamin;
                        if (!is_null($perubahan->nik_ktp)) $updateData['nik_ktp'] = $perubahan->nik_ktp;
                        if (!is_null($perubahan->no_kk)) $updateData['no_kk'] = $perubahan->no_kk;
                        if (!is_null($perubahan->kategori_agama_id)) $updateData['agama'] = $perubahan->kategori_agama_id;
                        if (!is_null($perubahan->kategori_darah_id)) $updateData['golongan_darah'] = $perubahan->kategori_darah_id;
                        if (!is_null($perubahan->tinggi_badan)) $updateData['tinggi_badan'] = $perubahan->tinggi_badan;
                        if (!is_null($perubahan->berat_badan)) $updateData['berat_badan'] = $perubahan->berat_badan;
                        if (!is_null($perubahan->alamat)) $updateData['alamat'] = $perubahan->alamat;
                        if (!is_null($perubahan->no_ijazah)) $updateData['no_ijazah'] = $perubahan->no_ijazah;
                        if (!is_null($perubahan->tahun_lulus)) $updateData['tahun_lulus'] = $perubahan->tahun_lulus;
                        if (!is_null($perubahan->pendidikan_terakhir)) $updateData['pendidikan_terakhir'] = $perubahan->pendidikan_terakhir;
                        if (!is_null($perubahan->gelar_depan)) $updateData['gelar_depan'] = $perubahan->gelar_depan;

                        // Lakukan update jika ada data yang berubah
                        if (!empty($updateData)) {
                            $dataKaryawan->update($updateData);
                        }
                    }
                }
                break;

            case 'Keluarga':
                $perubahanKeluarga = PerubahanKeluarga::where('riwayat_perubahan_id', $riwayat->id)->first();
                foreach ($perubahanKeluarga as $perubahan) {
                    $dataKeluarga = DataKeluarga::where('id', $perubahanKeluarga->data_keluarga_id)
                        ->where('data_karyawan_id', $riwayat->data_karyawan_id)
                        ->first();

                    // if ($dataKeluarga) {
                    //     // $dataKeluarga->{$riwayat->kolom} = $riwayat->updated_data;
                    //     $dataKeluarga->{$riwayat->kolom} = $riwayat->updated_data[0][$riwayat->kolom];
                    //     $dataKeluarga->save();
                    // }
                    if ($dataKeluarga) {
                        $updateData = [];
                        if (!is_null($perubahan->nama_keluarga)) $updateData['nama_keluarga'] = $perubahan->nama_keluarga;
                        if (!is_null($perubahan->hubungan)) $updateData['hubungan'] = $perubahan->hubungan;
                        if (!is_null($perubahan->pendidikan_terakhir)) $updateData['pendidikan_terakhir'] = $perubahan->pendidikan_terakhir;
                        if (!is_null($perubahan->status_hidup)) $updateData['status_hidup'] = $perubahan->status_hidup;
                        if (!is_null($perubahan->pekerjaan)) $updateData['pekerjaan'] = $perubahan->pekerjaan;
                        if (!is_null($perubahan->no_hp)) $updateData['no_hp'] = $perubahan->no_hp;
                        if (!is_null($perubahan->email)) $updateData['email'] = $perubahan->email;

                        // Lakukan update jika ada data yang berubah
                        if (!empty($updateData)) {
                            $dataKeluarga->update($updateData);
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
