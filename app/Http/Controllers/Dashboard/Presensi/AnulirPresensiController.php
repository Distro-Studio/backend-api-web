<?php

namespace App\Http\Controllers\Dashboard\Presensi;

use App\Exports\Presensi\AnulirPresensiExport;
use Carbon\Carbon;
use App\Models\Berkas;
use App\Models\Presensi;
use App\Helpers\LogHelper;
use App\Helpers\StorageServerHelper;
use Illuminate\Support\Str;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AnulirPresensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreAnulirPresensiRequest;
use App\Http\Requests\UpdateAnulirPresensiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class AnulirPresensiController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!Gate::allows('view anulirPresensi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $loggedInUser = auth()->user();
            $isSuperAdmin = $loggedInUser->id == 1 || $loggedInUser->nama == 'Super Admin';

            $limit = $request->input('limit', 10);
            $presensiAnulir = AnulirPresensi::whereHas('data_karyawans', function ($query) {
                $query->where('id', '!=', 1)
                    ->orderBy('nik', 'asc');
            });
            $filters = $request->all();

            if (isset($filters['unit_kerja'])) {
                $namaUnitKerja = $filters['unit_kerja'];
                $presensiAnulir->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                    if (is_array($namaUnitKerja)) {
                        $query->whereIn('id', $namaUnitKerja);
                    } else {
                        $query->where('id', '=', $namaUnitKerja);
                    }
                });
            }

            if (isset($filters['jabatan'])) {
                $namaJabatan = $filters['jabatan'];
                $presensiAnulir->whereHas('data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                    if (is_array($namaJabatan)) {
                        $query->whereIn('id', $namaJabatan);
                    } else {
                        $query->where('id', '=', $namaJabatan);
                    }
                });
            }

            if (isset($filters['status_karyawan'])) {
                $statusKaryawan = $filters['status_karyawan'];
                $presensiAnulir->whereHas('data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                        foreach ($masaKerja as $masa) {
                            $bulan = $masa * 12;
                            $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                        }
                    });
                } else {
                    $bulan = $masaKerja * 12;
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($bulan, $currentDate) {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    });
                }
            }

            if (isset($filters['status_aktif'])) {
                $statusAktif = $filters['status_aktif'];
                $presensiAnulir->whereHas('data_karyawans.users', function ($query) use ($statusAktif) {
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
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                        $query->whereIn('tgl_masuk', $tglMasuk);
                    });
                } else {
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                        $query->where('tgl_masuk', $tglMasuk);
                    });
                }
            }

            if (isset($filters['agama'])) {
                $namaAgama = $filters['agama'];
                $presensiAnulir->whereHas('data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where(function ($query) use ($jenisKelamin) {
                            foreach ($jenisKelamin as $jk) {
                                $query->orWhere('jenis_kelamin', $jk);
                            }
                        });
                    });
                } else {
                    $presensiAnulir->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                        $query->where('jenis_kelamin', $jenisKelamin);
                    });
                }
            }

            if (isset($filters['pendidikan_terakhir'])) {
                $namaPendidikan = $filters['pendidikan_terakhir'];
                $presensiAnulir->whereHas('data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                    $presensiAnulir->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_karyawan', $jk);
                            }
                        });
                    });
                } else {
                    $presensiAnulir->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_karyawan', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['jenis_kompetensi'])) {
                $jenisKaryawan = $filters['jenis_kompetensi'];
                if (is_array($jenisKaryawan)) {
                    $presensiAnulir->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where(function ($query) use ($jenisKaryawan) {
                            foreach ($jenisKaryawan as $jk) {
                                $query->orWhere('jenis_kompetensi', $jk);
                            }
                        });
                    });
                } else {
                    $presensiAnulir->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                        $query->where('jenis_kompetensi', $jenisKaryawan);
                    });
                }
            }

            if (isset($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $presensiAnulir->where(function ($query) use ($searchTerm) {
                    $query->whereHas('data_karyawans.users', function ($query) use ($searchTerm) {
                        $query->where('nama', 'like', $searchTerm);
                    })->orWhere('nik', 'like', $searchTerm);
                });
            }

            if ($limit == 0) {
                $dataKaryawanAnulir = $presensiAnulir->get();
                $paginationData = null;
            } else {
                $limit = is_numeric($limit) ? (int)$limit : 10;
                $dataKaryawanAnulir = $presensiAnulir->paginate($limit);

                $paginationData = [
                    'links' => [
                        'first' => $dataKaryawanAnulir->url(1),
                        'last' => $dataKaryawanAnulir->url($dataKaryawanAnulir->lastPage()),
                        'prev' => $dataKaryawanAnulir->previousPageUrl(),
                        'next' => $dataKaryawanAnulir->nextPageUrl(),
                    ],
                    'meta' => [
                        'current_page' => $dataKaryawanAnulir->currentPage(),
                        'last_page' => $dataKaryawanAnulir->lastPage(),
                        'per_page' => $dataKaryawanAnulir->perPage(),
                        'total' => $dataKaryawanAnulir->total(),
                    ]
                ];
            }

            if ($dataKaryawanAnulir->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data karyawan anulir presensi tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = $dataKaryawanAnulir->map(function ($karyawanAnulir) use ($isSuperAdmin, $baseUrl) {
                $baseUrl = env('STORAGE_SERVER_DOMAIN');
                $cariBerkasAnulir = Berkas::where('id', $karyawanAnulir->dokumen_anulir_id)->first();
                $berkasAnulir = $cariBerkasAnulir ? $baseUrl . $cariBerkasAnulir->path : null;
                $role = $karyawanAnulir->data_karyawans->users->roles->first();

                return [
                    'id' => $karyawanAnulir->id,
                    'data_karyawan' => $karyawanAnulir->data_karyawans ? [
                        'id' => $karyawanAnulir->data_karyawans->id,
                        'user' => [
                            'id' => $karyawanAnulir->data_karyawans->users->id,
                            'nama' => $karyawanAnulir->data_karyawans->users->nama,
                            'username' => $karyawanAnulir->data_karyawans->users->username,
                            'email_verified_at' => $karyawanAnulir->data_karyawans->users->email_verified_at,
                            'data_karyawan_id' => $karyawanAnulir->data_karyawans->users->data_karyawan_id,
                            'foto_profil' => $karyawanAnulir->data_karyawans->users->foto_profiles ? [
                                'id' => $karyawanAnulir->data_karyawans->users->foto_profiles->id,
                                'user_id' => $karyawanAnulir->data_karyawans->users->foto_profiles->user_id,
                                'file_id' => $karyawanAnulir->data_karyawans->users->foto_profiles->file_id,
                                'nama' => $karyawanAnulir->data_karyawans->users->foto_profiles->nama,
                                'nama_file' => $karyawanAnulir->data_karyawans->users->foto_profiles->nama_file,
                                'path' => $baseUrl . $karyawanAnulir->data_karyawans->users->foto_profiles->path,
                                'ext' => $karyawanAnulir->data_karyawans->users->foto_profiles->ext,
                                'size' => $karyawanAnulir->data_karyawans->users->foto_profiles->size,
                            ] : null,
                            'data_completion_step' => $karyawanAnulir->data_karyawans->users->data_completion_step,
                            'status_aktif' => $karyawanAnulir->data_karyawans->users->status_aktif,
                            'tgl_dinonaktifkan' => $karyawanAnulir->data_karyawans->users->tgl_dinonaktifkan,
                            'alasan' => $karyawanAnulir->data_karyawans->users->alasan,
                            'created_at' => $karyawanAnulir->data_karyawans->users->created_at,
                            'updated_at' => $karyawanAnulir->data_karyawans->users->updated_at
                        ],
                        'role' => $isSuperAdmin ? [
                            'id' => $role->id,
                            'name' => $role->name,
                            'deskripsi' => $role->deskripsi,
                            'created_at' => $role->created_at,
                            'updated_at' => $role->updated_at
                        ] : null,
                        'email' => $karyawanAnulir->data_karyawans->email,
                        'nik' => $karyawanAnulir->data_karyawans->nik,
                        'nik_ktp' => $karyawanAnulir->data_karyawans->nik_ktp,
                        'status_karyawan' => $karyawanAnulir->data_karyawans->status_karyawans,
                        'tempat_lahir' => $karyawanAnulir->data_karyawans->tempat_lahir,
                        'tgl_lahir' => $karyawanAnulir->data_karyawans->tgl_lahir,
                        'no_kk' => $karyawanAnulir->data_karyawans->no_kk,
                        'alamat' => $karyawanAnulir->data_karyawans->alamat,
                        'gelar_depan' => $karyawanAnulir->data_karyawans->gelar_depan,
                        'gelar_belakang' => $karyawanAnulir->data_karyawans->gelar_belakang,
                        'no_hp' => $karyawanAnulir->data_karyawans->no_hp,
                        'jenis_kelamin' => $karyawanAnulir->data_karyawans->jenis_kelamin,
                        'status_reward_presensi' => $karyawanAnulir->data_karyawans->status_reward_presensi,
                        'created_at' => $karyawanAnulir->data_karyawans->created_at,
                        'updated_at' => $karyawanAnulir->data_karyawans->updated_at
                    ] : null,
                    'presensi' => $karyawanAnulir->presensis ? [
                        'id' => $karyawanAnulir->presensis->id,
                        'user' => $karyawanAnulir->presensis->users,
                        'unit_kerja' => $karyawanAnulir->presensis->data_karyawans->unit_kerjas,
                        'jadwal' => [
                            'id' => $karyawanAnulir->presensis->jadwals->id ?? null,
                            'tgl_mulai' => $karyawanAnulir->presensis->jadwals->tgl_mulai ?? null,
                            'tgl_selesai' => $karyawanAnulir->presensis->jadwals->tgl_selesai ?? null,
                            'shift' => $karyawanAnulir->presensis->jadwals->shifts ?? null,
                        ],
                        'jam_masuk' => $karyawanAnulir->presensis->jam_masuk,
                        'jam_keluar' => $karyawanAnulir->presensis->jam_keluar,
                        'durasi' => $karyawanAnulir->presensis->durasi,
                        'kategori_presensi' => $karyawanAnulir->presensis->kategori_presensis,
                        'created_at' => $karyawanAnulir->presensis->created_at,
                        'updated_at' => $karyawanAnulir->presensis->updated_at
                    ] : null,
                    'alasan' => $karyawanAnulir->alasan ?? null,
                    'keterangan' => $karyawanAnulir->keterangan ?? null,
                    'dokumen_anulir' => $karyawanAnulir->dokumen_anulir ? [
                        'id' => $karyawanAnulir->dokumen_anulir->id,
                        'user_id' => $karyawanAnulir->dokumen_anulir->user_id,
                        'file_id' => $karyawanAnulir->dokumen_anulir->file_id,
                        'nama' => $karyawanAnulir->dokumen_anulir->nama,
                        'nama_file' => $karyawanAnulir->dokumen_anulir->nama_file,
                        'path' => $berkasAnulir,
                        'ext' => $karyawanAnulir->dokumen_anulir->ext,
                        'size' => $karyawanAnulir->dokumen_anulir->size,
                    ] : null,
                    'created_at' => $karyawanAnulir->created_at,
                    'updated_at' => $karyawanAnulir->updated_at,
                    'deleted_at' => $karyawanAnulir->deleted_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data karyawan anulir presensi berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Anulir Presensi | - Error function index: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreAnulirPresensiRequest $request)
    {
        try {
            if (!Gate::allows('create anulirPresensi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();

            $presensi = Presensi::find($data['presensi_id']);
            if (!$presensi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Presensi tersebut tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $karyawanAnulir = DataKaryawan::find($presensi->data_karyawan_id);
            if (!$karyawanAnulir) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Karyawan yang melakukan presensi tersebut tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Ambil bulan dan tahun dari jam_masuk presensi
            $bulanPresensi = Carbon::parse($presensi->jam_masuk)->format('m');
            $tahunPresensi = Carbon::parse($presensi->jam_masuk)->format('Y');

            // Hitung total pembatalan di bulan dan tahun (semua tipe)
            $totalPembatalanBulanIni = DB::table('riwayat_pembatalan_rewards')
                ->where('data_karyawan_id', $presensi->data_karyawan_id)
                ->whereYear('tgl_pembatalan', $tahunPresensi)
                ->whereMonth('tgl_pembatalan', $bulanPresensi)
                ->count();

            // Hitung total pembatalan tipe 'presensi' dengan presensi_id yang sama
            $totalPembatalanPresensiIni = DB::table('riwayat_pembatalan_rewards')
                ->where('data_karyawan_id', $presensi->data_karyawan_id)
                ->where('tipe_pembatalan', 'presensi')
                ->where('presensi_id', $presensi->id)
                ->whereYear('tgl_pembatalan', $tahunPresensi)
                ->whereMonth('tgl_pembatalan', $bulanPresensi)
                ->count();

            if ($totalPembatalanBulanIni >= 1 && $totalPembatalanPresensiIni >= 2) {
                Log::info("Jumlah riwayat pembatalan reward di bulan ini: '{$totalPembatalanBulanIni}'. Tidak mengubah status reward presensi.");
                $message = "Data anulir dari karyawan '{$karyawanAnulir->users->nama}' berhasil ditambahkan. Namun tidak mengubah reward presensi karena ada beberapa riwayat pembatalan di bulan tersebut.";
                $keterangan = "Data anulir berhasil ditambahkan. Namun tidak mengubah reward presensi karena ada beberapa riwayat pembatalan di bulan tersebut.";
            } elseif ($totalPembatalanPresensiIni === 1) {
                Log::info("Pembatalan presensi tunggal di bulan ini, status reward presensi dapat diperbarui.");
                $message = "Data anulir dari karyawan '{$karyawanAnulir->users->nama}' berhasil ditambahkan dan reward presensi berhasil diperbarui.";
                $keterangan = "Data anulir berhasil ditambahkan dan reward presensi berhasil diperbarui.";
            } else {
                // Kasus lain, misal tidak ada pembatalan presensi sama sekali (jarang terjadi)
                Log::info("Pembatalan presensi tidak ditemukan meskipun total pembatalan kurang dari 2. Status reward tidak berubah.");
                $message = "Data anulir dari karyawan '{$karyawanAnulir->users->nama}' berhasil ditambahkan, namun reward presensi tidak diubah.";
                $keterangan = "Data anulir berhasil ditambahkan, namun reward presensi tidak diubah.";
            }

            DB::beginTransaction();

            if ($request->hasFile('dokumen')) {
                StorageServerHelper::login();

                $random_filename = Str::random(20);
                $dataupload = StorageServerHelper::uploadToServer($request, $random_filename);
                $data['dokumen'] = $dataupload['path'];

                $berkas = Berkas::create([
                    'user_id' => $karyawanAnulir->users->id,
                    'file_id' => $dataupload['id_file']['id'],
                    'nama' => $random_filename,
                    'kategori_berkas_id' => 7, // umum
                    'status_berkas_id' => 2,
                    'path' => $dataupload['path'],
                    'tgl_upload' => now('Asia/Jakarta'),
                    'nama_file' => $dataupload['nama_file'],
                    'ext' => $dataupload['ext'],
                    'size' => $dataupload['size'],
                ]);
                Log::info('Berkas anulir ' . $karyawanAnulir->users->nama . ' berhasil di upload.');
                StorageServerHelper::logout();

                if (!$berkas) {
                    throw new Exception('Berkas gagal di upload');
                }
            }

            AnulirPresensi::create([
                'data_karyawan_id' => $presensi->data_karyawan_id,
                'presensi_id' => $presensi->id,
                'alasan' => $data['alasan'],
                'keterangan' => $keterangan,
                'dokumen_anulir_id' => $berkas->id,
                'created_at' => now('Asia/Jakarta'),
                'updated_at' => now('Asia/Jakarta'),
            ]);

            // Update is_anulir_presensi pada pembatalan yang sesuai presensi_id
            DB::table('riwayat_pembatalan_rewards')
                ->where('data_karyawan_id', $presensi->data_karyawan_id)
                ->where('presensi_id', $presensi->id)
                ->update(['is_anulir_presensi' => true]);

            // Jika hanya 1 data pembatalan (data yang sedang dibuat), update riwayat_pembatalan_rewards terkait
            if ($totalPembatalanPresensiIni === 1) {
                // Cek ada tidaknya riwayat penggajian bulan ini untuk karyawan ini
                $gajiBulanIni = DB::table('riwayat_penggajians')
                    ->whereYear('periode', $tahunPresensi)
                    ->whereMonth('periode', $bulanPresensi)
                    ->where('data_karyawan_id', $presensi->data_karyawan_id)
                    ->exists();
                if ($gajiBulanIni) {
                    // Jika ada gaji bulan ini, update di data_karyawans
                    DB::table('data_karyawans')
                        ->where('id', $presensi->data_karyawan_id)
                        ->update(['status_reward_presensi' => true]);
                    Log::info("Status reward presensi karyawan ID {$presensi->data_karyawan_id} diperbarui menjadi false di data_karyawans.");
                } else {
                    // Jika tidak ada, update di reward_bulan_lalus
                    DB::table('reward_bulan_lalus')
                        ->where('data_karyawan_id', $presensi->data_karyawan_id)
                        ->update(['status_reward' => true]);
                    Log::info("Status reward bulan lalu karyawan ID {$presensi->data_karyawan_id} diperbarui menjadi false di reward_bulan_lalus.");
                }
            }

            DB::commit();

            LogHelper::logAction('Anulir Presensi', 'create', $presensi->data_karyawan_id);

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => $message
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Anulir Presensi | - Error function store: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Gate::allows('delete anulirPresensi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data_anulir = AnulirPresensi::find($id);
            if (!$data_anulir) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data anulir presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $presensi = Presensi::find($data_anulir->presensi_id);
            if (!$presensi) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil bulan dan tahun dari jam_masuk presensi
            $bulanPresensi = Carbon::parse($presensi->jam_masuk)->format('m');
            $tahunPresensi = Carbon::parse($presensi->jam_masuk)->format('Y');

            DB::beginTransaction();
            // Delete berkas nya dulu
            $berkasLama = Berkas::find($data_anulir->dokumen_anulir_id);
            if ($berkasLama) {
                try {
                    StorageServerHelper::deleteFromServer($berkasLama->file_id);
                } catch (\Exception $e) {
                    Log::warning("Gagal hapus dokumen_anulir_id lama dari server (file_id: {$berkasLama->file_id}): " . $e->getMessage());
                }

                // Set hubungan ke NULL dulu sebelum hapus
                $data_anulir->dokumen_anulir_id = null;
                $data_anulir->save();

                // Setelah tidak ada referensi, baru aman dihapus
                $berkasLama->delete();
            }

            $data_anulir->delete();

            // Update is_anulir_presensi pada pembatalan yang sesuai presensi_id
            DB::table('riwayat_pembatalan_rewards')
                ->where('data_karyawan_id', $data_anulir->data_karyawan_id)
                ->where('presensi_id', $data_anulir->presensi_id)
                ->update(['is_anulir_presensi' => false]);

            // Cek ada tidaknya riwayat penggajian bulan ini untuk karyawan ini
            $gajiBulanIni = DB::table('riwayat_penggajians')
                ->whereYear('periode', $tahunPresensi)
                ->whereMonth('periode', $bulanPresensi)
                ->where('data_karyawan_id', $presensi->data_karyawan_id)
                ->exists();
            if ($gajiBulanIni) {
                // Jika ada gaji bulan ini, update di data_karyawans
                DB::table('data_karyawans')
                    ->where('id', $presensi->data_karyawan_id)
                    ->update(['status_reward_presensi' => false]);
                Log::info("Status reward presensi karyawan ID {$presensi->data_karyawan_id} diperbarui menjadi false di data_karyawans.");
            } else {
                // Jika tidak ada, update di reward_bulan_lalus
                DB::table('reward_bulan_lalus')
                    ->where('data_karyawan_id', $presensi->data_karyawan_id)
                    ->update(['status_reward' => false]);
                Log::info("Status reward bulan lalu karyawan ID {$presensi->data_karyawan_id} diperbarui menjadi false di reward_bulan_lalus.");
            }

            DB::commit();

            LogHelper::logAction('Anulir Presensi', 'delete', $data_anulir->data_karyawan_id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Anulir Presensi | - Error function destroy: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportAnulir(Request $request)
    {
        try {
            if (!Gate::allows('export anulirPresensi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data_anulir = AnulirPresensi::all();
            if ($data_anulir->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data anulir presensi yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new AnulirPresensiExport($request->all()), 'anulir-presensi.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Anulir Presensi | - Error function exportAnulir: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
