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
use Illuminate\Support\Facades\DB;
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
                $role = $dataKaryawan->users->roles->first();
                $originalData = [
                    [
                        'id' => $dataKaryawan->id,
                        'user' => [
                            'id' => $dataKaryawan->users->id,
                            'nama' => $dataKaryawan->users->nama,
                            'email_verified_at' => $dataKaryawan->users->email_verified_at,
                            'data_karyawan_id' => $dataKaryawan->users->data_karyawan_id,
                            'foto_profil' => $dataKaryawan->users->foto_profil,
                            'data_completion_step' => $dataKaryawan->users->data_completion_step,
                            'status_aktif' => $dataKaryawan->users->status_aktif,
                            'created_at' => $dataKaryawan->users->created_at,
                            'updated_at' => $dataKaryawan->users->updated_at
                        ],
                        'role' => [
                            'id' => $role->id,
                            'name' => $role->name,
                            'deskripsi' => $role->deskripsi,
                            'created_at' => $role->created_at,
                            'updated_at' => $role->updated_at
                        ],
                        'potongan_gaji' => DB::table('pengurang_gajis')
                            ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
                            ->where('pengurang_gajis.data_karyawan_id', $dataKaryawan->id)
                            ->select(
                                'premis.id',
                                'premis.nama_premi',
                                'premis.kategori_potongan_id',
                                'premis.jenis_premi',
                                'premis.besaran_premi',
                                'premis.minimal_rate',
                                'premis.maksimal_rate',
                                'premis.created_at',
                                'premis.updated_at'
                            )
                            ->get(),
                        'email' => $dataKaryawan->email,
                        'nik' => $dataKaryawan->nik,
                        'no_rm' => $dataKaryawan->no_rm,
                        'no_sip' => $dataKaryawan->no_sip,
                        'masa_berlaku_sip' => $dataKaryawan->masa_berlaku_sip,
                        'no_manulife' => $dataKaryawan->no_manulife,
                        'tgl_masuk' => $dataKaryawan->tgl_masuk,
                        'unit_kerja' => $dataKaryawan->unit_kerjas, // unit_kerja_id
                        'jabatan' => $dataKaryawan->jabatans, // jabatan_id
                        'kompetensi' => $dataKaryawan->kompetensis, // kompetensi_id
                        'nik_ktp' => $dataKaryawan->nik_ktp,
                        'status_karyawan' => $dataKaryawan->status_karyawans, // status_karyawan_id
                        'tempat_lahir' => $dataKaryawan->tempat_lahir,
                        'tgl_lahir' => $dataKaryawan->tgl_lahir,
                        'kelompok_gaji' => $dataKaryawan->kelompok_gajis, // kelompok_gaji_id
                        'no_rekening' => $dataKaryawan->no_rekening,
                        'tunjangan_jabatan' => $dataKaryawan->tunjangan_jabatan,
                        'tunjangan_fungsional' => $dataKaryawan->tunjangan_fungsional,
                        'tunjangan_khusus' => $dataKaryawan->tunjangan_khusus,
                        'tunjangan_lainnya' => $dataKaryawan->tunjangan_lainnya,
                        'uang_lembur' => $dataKaryawan->uang_lembur,
                        'uang_makan' => $dataKaryawan->uang_makan,
                        'ptkp' => $dataKaryawan->ptkps, // ptkp_id
                        'tgl_keluar' => $dataKaryawan->tgl_keluar,
                        'no_kk' => $dataKaryawan->no_kk,
                        'alamat' => $dataKaryawan->alamat,
                        'gelar_depan' => $dataKaryawan->gelar_depan,
                        'no_hp' => $dataKaryawan->no_hp,
                        'no_bpjsksh' => $dataKaryawan->no_bpjsksh,
                        'no_bpjsktk' => $dataKaryawan->no_bpjsktk,
                        'tgl_diangkat' => $dataKaryawan->tgl_diangkat,
                        'masa_kerja' => $dataKaryawan->masa_kerja,
                        'npwp' => $dataKaryawan->npwp,
                        'jenis_kelamin' => $dataKaryawan->jenis_kelamin,
                        'agama' => $dataKaryawan->kategori_agamas, // agama_id
                        'golongan_darah' => $dataKaryawan->kategori_darahs, // golongan_darah_id
                        'pendidikan_terakhir' => $dataKaryawan->kategori_pendidikans, // pendidikan_terakhir_id
                        'tinggi_badan' => $dataKaryawan->tinggi_badan,
                        'berat_badan' => $dataKaryawan->berat_badan,
                        'no_ijazah' => $dataKaryawan->no_ijazah,
                        'tahun_lulus' => $dataKaryawan->tahun_lulus,
                        'no_str' => $dataKaryawan->no_str,
                        'masa_berlaku_str' => $dataKaryawan->masa_berlaku_str,
                        'tgl_berakhir_pks' => $dataKaryawan->tgl_berakhir_pks,
                        'masa_diklat' => $dataKaryawan->masa_diklat,
                        'created_at' => $dataKaryawan->created_at,
                        'updated_at' => $dataKaryawan->updated_at
                    ]
                ];

                $personalChanges = $data_perubahan->perubahan_personals;
                if ($personalChanges->isNotEmpty()) {
                    $updatedData = $personalChanges->map(function ($change) {
                        $role = $change->riwayat_perubahans->data_karyawans->users->roles->first();
                        $user = $change->riwayat_perubahans->data_karyawans->users;
                        return [
                            'id' => $change->id,
                            'user' => [
                                'id' => $user->id,
                                'nama' => $user->nama,
                                'email_verified_at' => $user->email_verified_at,
                                'data_karyawan_id' => $user->data_karyawan_id,
                                'foto_profil' => $user->foto_profil,
                                'data_completion_step' => $user->data_completion_step,
                                'status_aktif' => $user->status_aktif,
                                'created_at' => $user->created_at,
                                'updated_at' => $user->updated_at
                            ],
                            'role' => [
                                'id' => $role->id,
                                'name' => $role->name,
                                'deskripsi' => $role->deskripsi,
                                'created_at' => $role->created_at,
                                'updated_at' => $role->updated_at
                            ],
                            'potongan_gaji' => DB::table('pengurang_gajis')
                                ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
                                ->where('pengurang_gajis.data_karyawan_id', $change->id)
                                ->select(
                                    'premis.id',
                                    'premis.nama_premi',
                                    'premis.kategori_potongan_id',
                                    'premis.jenis_premi',
                                    'premis.besaran_premi',
                                    'premis.minimal_rate',
                                    'premis.maksimal_rate',
                                    'premis.created_at',
                                    'premis.updated_at'
                                )
                                ->get(),
                            'email' => $change->email,
                            'nik' => $change->nik,
                            'no_rm' => $change->no_rm,
                            'no_sip' => $change->no_sip,
                            'masa_berlaku_sip' => $change->masa_berlaku_sip,
                            'no_manulife' => $change->no_manulife,
                            'tgl_masuk' => $change->tgl_masuk,
                            'unit_kerja' => $change->unit_kerjas, // unit_kerja_id
                            'jabatan' => $change->jabatans, // jabatan_id
                            'kompetensi' => $change->kompetensis, // kompetensi_id
                            'nik_ktp' => $change->nik_ktp,
                            'status_karyawan' => $change->status_karyawans, // status_karyawan_id
                            'tempat_lahir' => $change->tempat_lahir,
                            'tgl_lahir' => $change->tgl_lahir,
                            'kelompok_gaji' => $change->kelompok_gajis, // kelompok_gaji_id
                            'no_rekening' => $change->no_rekening,
                            'tunjangan_jabatan' => $change->tunjangan_jabatan,
                            'tunjangan_fungsional' => $change->tunjangan_fungsional,
                            'tunjangan_khusus' => $change->tunjangan_khusus,
                            'tunjangan_lainnya' => $change->tunjangan_lainnya,
                            'uang_lembur' => $change->uang_lembur,
                            'uang_makan' => $change->uang_makan,
                            'ptkp' => $change->ptkps, // ptkp_id
                            'tgl_keluar' => $change->tgl_keluar,
                            'no_kk' => $change->no_kk,
                            'alamat' => $change->alamat,
                            'gelar_depan' => $change->gelar_depan,
                            'no_hp' => $change->no_telp,
                            'no_bpjsksh' => $change->no_bpjsksh,
                            'no_bpjsktk' => $change->no_bpjsktk,
                            'tgl_diangkat' => $change->tgl_diangkat,
                            'masa_kerja' => $change->masa_kerja,
                            'npwp' => $change->npwp,
                            'jenis_kelamin' => $change->jenis_kelamin,
                            'agama' => $change->kategori_agamas, // agama_id
                            'golongan_darah' => $change->kategori_darahs, // golongan_darah_id
                            'pendidikan_terakhir' => $change->kategori_pendidikans, // pendidikan_terakhir_id
                            'tinggi_badan' => $change->tinggi_badan,
                            'berat_badan' => $change->berat_badan,
                            'no_ijazah' => $change->no_ijazah,
                            'tahun_lulus' => $change->tahun_lulus,
                            'no_str' => $change->no_str,
                            'masa_berlaku_str' => $change->masa_berlaku_str,
                            'tgl_berakhir_pks' => $change->tgl_berakhir_pks,
                            'masa_diklat' => $change->masa_diklat,
                            'created_at' => $change->created_at,
                            'updated_at' => $change->updated_at
                        ];
                    });
                }
            }

            if ($data_perubahan->jenis_perubahan === 'Berkas') {
                // Fetch the original data from the related Berkas
                $berkasChanges = $data_perubahan->perubahan_berkas;

                if ($berkasChanges->isNotEmpty()) {
                    $firstBerkasChange = $berkasChanges->first();
                    $originalBerkas = Berkas::find($firstBerkasChange->berkas_id);
                    $baseUrl = env('STORAGE_SERVER_DOMAIN');

                    if ($originalBerkas) {
                        $originalData = [[
                            'id' => $originalBerkas->id,
                            'file_id' => $originalBerkas->file_id,
                            'nama' => $originalBerkas->nama,
                            'path' => $originalBerkas->path,
                            'tgl_upload' => $originalBerkas->tgl_upload,
                            'nama_file' => $originalBerkas->nama_file,
                            'ext' => $originalBerkas->ext,
                            'size' => $originalBerkas->size,
                            'url' => $baseUrl . $originalBerkas->path,
                            'created_at' => $originalBerkas->created_at,
                            'updated_at' => $originalBerkas->updated_at
                        ]];
                    }

                    // Updated data reflects the new changes
                    $updatedData = [[
                        'id' => $firstBerkasChange->id,
                        'file_id' => $firstBerkasChange->file_id,
                        'nama' => $firstBerkasChange->nama,
                        'path' => $firstBerkasChange->path,
                        'tgl_upload' => $firstBerkasChange->tgl_upload,
                        'nama_file' => $firstBerkasChange->nama_file,
                        'ext' => $firstBerkasChange->ext,
                        'size' => $firstBerkasChange->size,
                        'url' => $baseUrl . $originalBerkas->path,
                        'created_at' => $firstBerkasChange->created_at,
                        'updated_at' => $firstBerkasChange->updated_at
                    ]];
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
            // Jika status_perubahan_id = 1 (menunggu) atau 3 (ditolak sebelumnya)
            if ($status_perubahan_id == 1) {
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
                if ($dataKaryawan) {
                    // $dataKaryawan->{$riwayat->kolom} = $riwayat->updated_data;
                    $dataKaryawan->{$riwayat->kolom} = $riwayat->updated_data[0][$riwayat->kolom];
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
                    // $berkas->{$riwayat->kolom} = $riwayat->updated_data;
                    $berkas->{$riwayat->kolom} = $riwayat->updated_data[0][$riwayat->kolom];
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
                            // $dataKeluarga->{$riwayat->kolom} = $riwayat->updated_data;
                            $dataKeluarga->{$riwayat->kolom} = $riwayat->updated_data[0][$riwayat->kolom];
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
