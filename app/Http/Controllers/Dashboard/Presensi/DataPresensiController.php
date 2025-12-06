<?php

namespace App\Http\Controllers\Dashboard\Presensi;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\Berkas;
use App\Models\Jadwal;
use App\Models\NonShift;
use App\Models\Presensi;
use App\Models\HariLibur;
use App\Models\DataKaryawan;
use App\Models\LokasiKantor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\Presensi\PresensiExport;
use App\Http\Requests\Excel_Import\ImportPresensiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Imports\Presensi\PresensiImportNew;

class DataPresensiController extends Controller
{
    public function getLokasiKantor()
    {
        if (!Gate::allows('view presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $lokasi_kantor = LokasiKantor::orderBy('updated_at', 'desc')->where('id', 1)->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieve lokasi kantor successfully.',
            'data' => $lokasi_kantor
        ], Response::HTTP_OK);
    }

    public function calculatedPresensi()
    {
        if (!Gate::allows('view presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Tetap menggunakan format 'd-m-Y' untuk $today
        $today = Carbon::today('Asia/Jakarta')->format('d-m-Y');

        // Ambil ID untuk setiap kategori dari tabel kategori_presensis
        $kategoriTepatWaktuId = DB::table('kategori_presensis')->where('label', 'Tepat Waktu')->value('id');
        $kategoriTerlambatId = DB::table('kategori_presensis')->where('label', 'Terlambat')->value('id');
        $kategoriAbsenId = DB::table('kategori_presensis')->where('label', 'Alpha')->value('id');

        // Validasi untuk memastikan kategori ditemukan
        if (is_null($kategoriTepatWaktuId) || is_null($kategoriTerlambatId) || is_null($kategoriAbsenId)) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Kategori presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Calculate total number of employees excluding the super admin
        $calculatedKaryawan = DataKaryawan::whereHas('users', function ($query) {
            $query->where('status_aktif', 2);
        })->where('id', '!=', 1)->count();

        // Hitung jumlah presensi dalam setiap kategori
        $countTepatWaktu = Presensi::where('kategori_presensi_id', $kategoriTepatWaktuId)
            ->whereDate('jam_masuk', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        $countTerlambat = Presensi::where('kategori_presensi_id', $kategoriTerlambatId)
            ->whereDate('jam_masuk', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        $countAbsen = Presensi::where('kategori_presensi_id', $kategoriAbsenId)
            ->whereDate('jam_masuk', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        // Perhitungan Cuti: Ambil data cuti langsung dari tabel cutis berdasarkan tgl_from dan tgl_to
        $countCuti = Cuti::where('status_cuti_id', 4)
            ->whereDate(DB::raw("STR_TO_DATE(tgl_from, '%d-%m-%Y')"), '<=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->whereDate(DB::raw("STR_TO_DATE(tgl_to, '%d-%m-%Y')"), '>=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        // Hitung karyawan shift yang libur berdasarkan user_id
        $countLiburShift = Jadwal::where('shift_id', 0)
            ->whereDate('tgl_mulai', '<=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->whereDate('tgl_selesai', '>=', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))
            ->count('user_id');

        // Periksa apakah hari ini adalah hari libur
        $isHariLibur = HariLibur::whereDate('tanggal', Carbon::createFromFormat('d-m-Y', $today)->format('Y-m-d'))->exists();

        // Hitung karyawan non-shift yang libur berdasarkan hari libur
        $countLiburNonShift = DataKaryawan::whereHas('unit_kerjas', function ($query) {
            $query->where('jenis_karyawan', 0);
        })->whereHas('users', function ($query) {
            $query->where('status_aktif', 2);
        })->when($isHariLibur, function ($query) {
            return $query->distinct('id')->count('id');  // Hitung berdasarkan user_id
        }, function ($query) {
            return 0;
        });

        // Total karyawan yang libur
        $countLibur = $countLiburShift + $countLiburNonShift;

        // Hitung total hadir dan total tidak hadir
        $totalHadir = $countTepatWaktu + $countTerlambat;
        $totalTidakHadir = $countCuti + $countAbsen + $countLibur;

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Perhitungan presensi seluruh karyawan berhasil ditampilkan.',
            'data' => [
                'total_karyawan' => $calculatedKaryawan,
                'total_hadir' => $totalHadir,
                'total_tepat_waktu' => $countTepatWaktu,
                'total_terlambat' => $countTerlambat,
                'total_tidak_hadir' => $totalTidakHadir,
                'total_libur' => $countLibur,
                'total_cuti' => $countCuti,
                'total_absen' => $countAbsen,
            ],
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Tentukan limit default
        $limit = $request->input('limit', 10); // Default 10 jika tidak ada atau kosong

        $presensi = Presensi::query()->orderBy('jam_masuk', 'desc');

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if ($request->has('tgl_mulai') && $request->has('tgl_selesai')) {
            $start_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_mulai'))->format('Y-m-d');
            $end_date = Carbon::createFromFormat('d-m-Y', $request->input('tgl_selesai'))->format('Y-m-d');
            $presensi->whereBetween(DB::raw("DATE(jam_masuk)"), [$start_date, $end_date]);
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal presensi mulai dan selesai tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
        }

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $presensi->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $presensi->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $presensi->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $presensi->whereHas('users.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $presensi->whereHas('users.data_karyawans', function ($query) use ($bulan, $currentDate) {
                    $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $presensi->whereHas('users', function ($query) use ($statusAktif) {
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
                $presensi->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->whereIn('tgl_masuk', $tglMasuk);
                });
            } else {
                $presensi->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->where('tgl_masuk', $tglMasuk);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $presensi->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $presensi->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $presensi->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $presensi->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $presensi->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $presensi->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        if (isset($filters['jenis_kompetensi'])) {
            $jenisKaryawan = $filters['jenis_kompetensi'];
            if (is_array($jenisKaryawan)) {
                $presensi->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_kompetensi', $jk);
                        }
                    });
                });
            } else {
                $presensi->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_kompetensi', $jenisKaryawan);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $presensi->where(function ($query) use ($searchTerm) {
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($limit == 0) {
            $dataPresensi = $presensi->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataPresensi = $presensi->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataPresensi->url(1),
                    'last' => $dataPresensi->url($dataPresensi->lastPage()),
                    'prev' => $dataPresensi->previousPageUrl(),
                    'next' => $dataPresensi->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataPresensi->currentPage(),
                    'last_page' => $dataPresensi->lastPage(),
                    'per_page' => $dataPresensi->perPage(),
                    'total' => $dataPresensi->total(),
                ]
            ];
        }

        if ($dataPresensi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data untuk output
        $formattedData = $dataPresensi->map(function ($presensi) {
            return [
                'id' => $presensi->id,
                'user' => $presensi->users,
                'unit_kerja' => $presensi->data_karyawans->unit_kerjas,
                'jadwal' => [
                    'id' => $presensi->jadwals->id ?? null,
                    'tgl_mulai' => $presensi->jadwals->tgl_mulai ?? null,
                    'tgl_selesai' => $presensi->jadwals->tgl_selesai ?? null,
                    'shift' => $presensi->jadwals->shifts ?? null,
                ],
                'jam_masuk' => $presensi->jam_masuk,
                'jam_keluar' => $presensi->jam_keluar,
                'durasi' => $presensi->durasi,
                'kategori_presensi' => $presensi->kategori_presensis,
                'pembatalan_reward' => $presensi->is_pembatalan_reward,
                'presensi_anulir' => $presensi->is_anulir_presensi,
                'created_at' => $presensi->created_at,
                'updated_at' => $presensi->updated_at
            ];
        },);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data presensi berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Mendapatkan data presensi karyawan berdasarkan id dan filter hari ini
        $presensiHariIni = Presensi::with([
            'users',
            'jadwals.shifts',
            'data_karyawans.unit_kerjas',
            'kategori_presensis'
        ])
            ->where('id', $id)
            ->first();

        if (!$presensiHariIni) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data presensi karyawan tidak ditemukan.'
            ], Response::HTTP_NOT_FOUND);
        }

        $fotoMasukBerkas = Berkas::where('id', $presensiHariIni->foto_masuk)->first();
        $fotoKeluarBerkas = Berkas::where('id', $presensiHariIni->foto_keluar)->first();

        $baseUrl = env('STORAGE_SERVER_DOMAIN'); // Ganti dengan URL domain Anda

        $fotoMasukExt = $fotoMasukBerkas ? StorageServerHelper::getExtensionFromMimeType($fotoMasukBerkas->ext) : null;
        $fotoMasukUrl = $fotoMasukBerkas ? $baseUrl . $fotoMasukBerkas->path : null;

        $fotoKeluarExt = $fotoKeluarBerkas ? StorageServerHelper::getExtensionFromMimeType($fotoKeluarBerkas->ext) : null;
        $fotoKeluarUrl = $fotoKeluarBerkas ? $baseUrl . $fotoKeluarBerkas->path : null;

        // Ambil data lokasi kantor
        $lokasiKantor = LokasiKantor::find(1);

        // Ambil data jadwal non-shift jika jenis_karyawan = false
        $jadwalNonShift = null;
        $jenisKaryawan = $presensiHariIni->users->data_karyawans->unit_kerjas->jenis_karyawan ?? null;
        if ($jenisKaryawan === 0) {
            $jamMasukDate = Carbon::parse($presensiHariIni->jam_masuk)->format('l');
            $hariNamaIndonesia = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ][$jamMasukDate] ?? 'Senin';
            $jadwalNonShift = NonShift::where('nama', $hariNamaIndonesia)->first();
        }

        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail data presensi karyawan '{$presensiHariIni->users->nama}' berhasil ditampilkan.",
            'data' => [
                'id' => $presensiHariIni->id,
                'user' => [
                    'id' => $presensiHariIni->users->id,
                    'nama' => $presensiHariIni->users->nama,
                    'username' => $presensiHariIni->users->username,
                    'email_verified_at' => $presensiHariIni->users->email_verified_at,
                    'data_karyawan_id' => $presensiHariIni->users->data_karyawan_id,
                    'foto_profil' => $presensiHariIni->users->foto_profiles ? [
                        'id' => $presensiHariIni->users->foto_profiles->id,
                        'user_id' => $presensiHariIni->users->foto_profiles->user_id,
                        'file_id' => $presensiHariIni->users->foto_profiles->file_id,
                        'nama' => $presensiHariIni->users->foto_profiles->nama,
                        'nama_file' => $presensiHariIni->users->foto_profiles->nama_file,
                        'path' => $baseUrl . $presensiHariIni->users->foto_profiles->path,
                        'ext' => $presensiHariIni->users->foto_profiles->ext,
                        'size' => $presensiHariIni->users->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $presensiHariIni->users->data_completion_step,
                    'status_aktif' => $presensiHariIni->users->status_aktif,
                    'created_at' => $presensiHariIni->users->created_at,
                    'updated_at' => $presensiHariIni->users->updated_at,
                ],
                'unit_kerja' => $presensiHariIni->data_karyawans->unit_kerjas,
                'data_presensi' => [
                    'jadwal_shift' => $presensiHariIni->jadwals ? [
                        'id' => $presensiHariIni->jadwals->id,
                        'tgl_mulai' => $presensiHariIni->jadwals->tgl_mulai,
                        'tgl_selesai' => $presensiHariIni->jadwals->tgl_selesai,
                        'shift' => $presensiHariIni->jadwals->shifts,
                    ] : null,
                    'jadwal_non_shift' => $jadwalNonShift ? [
                        'id' => $jadwalNonShift->id,
                        'nama' => $jadwalNonShift->nama,
                        'jam_from' => $jadwalNonShift->jam_from,
                        'jam_to' => $jadwalNonShift->jam_to,
                        'deleted_at' => $jadwalNonShift->deleted_at,
                        'created_at' => $jadwalNonShift->created_at,
                        'updated_at' => $jadwalNonShift->updated_at,
                    ] : null,
                    'jam_masuk' => $presensiHariIni->jam_masuk,
                    'jam_keluar' => $presensiHariIni->jam_keluar,
                    'durasi' => $presensiHariIni->durasi,
                    'lokasi_kantor' => [
                        'id' => $lokasiKantor->id,
                        'alamat' => $lokasiKantor->alamat,
                        'lat' => $lokasiKantor->lat,
                        'long' => $lokasiKantor->long,
                        'radius' => $lokasiKantor->radius,
                    ],
                    'lat_masuk' => $presensiHariIni->lat,
                    'long_masuk' => $presensiHariIni->long,
                    'lat_keluar' => $presensiHariIni->latkeluar,
                    'long_keluar' => $presensiHariIni->longkeluar,
                    'foto_masuk' => $fotoMasukBerkas ? [
                        'id' => $fotoMasukBerkas->id,
                        'user_id' => $fotoMasukBerkas->user_id,
                        'file_id' => $fotoMasukBerkas->file_id,
                        'nama' => $fotoMasukBerkas->nama,
                        'nama_file' => $fotoMasukBerkas->nama_file,
                        'path' => $fotoMasukUrl,
                        'ext' => $fotoMasukBerkas->ext,
                        'size' => $fotoMasukBerkas->size,
                    ] : null,
                    'foto_keluar' => $fotoKeluarBerkas ? [
                        'id' => $fotoKeluarBerkas->id,
                        'user_id' => $fotoKeluarBerkas->user_id,
                        'file_id' => $fotoKeluarBerkas->file_id,
                        'nama' => $fotoKeluarBerkas->nama,
                        'nama_file' => $fotoKeluarBerkas->nama_file,
                        'path' => $fotoKeluarUrl,
                        'ext' => $fotoKeluarBerkas->ext,
                        'size' => $fotoKeluarBerkas->size,
                    ] : null,
                    'kategori_presensi' => $presensiHariIni->kategori_presensis,
                    'pembatalan_reward' => $presensiHariIni->is_pembatalan_reward,
                    'presensi_anulir' => $presensiHariIni->is_anulir_presensi,
                    'created_at' => $presensiHariIni->created_at,
                    'updated_at' => $presensiHariIni->updated_at
                ]
            ],
        ], Response::HTTP_OK);
    }

    public function exportPresensi(Request $request)
    {
        if (!Gate::allows('export presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // $month = $request->input('month');
        // $year = $request->input('year');
        // if (empty($month) || empty($year)) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Periode bulan dan tahun tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
        // }

        // NEW UPDATE
        $tgl_mulai = $request->input('tgl_mulai');
        $tgl_selesai = $request->input('tgl_selesai');
        if (empty($tgl_mulai) || empty($tgl_selesai)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Periode tanggal mulai dan tanggal selesai tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
        }

        try {
            $startDate = Carbon::createFromFormat('d-m-Y', $tgl_mulai)->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', $tgl_selesai)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal yang dimasukkan tidak valid.'), Response::HTTP_BAD_REQUEST);
        }

        $presensiCount = Presensi::whereBetween('jam_masuk', [$startDate, $endDate])->count();
        if ($presensiCount === 0) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan untuk periode yang diminta.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new PresensiExport($startDate, $endDate, $request->all()), 'presensi-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti atau hubungi SIM RS.'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function downloadPresensiTemplate()
    {
        try {
            $filePath = 'templates/template_import_presensi.xls';

            if (!Storage::exists($filePath)) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'File template tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            return Storage::download($filePath, 'template_import_presensi.xls');
        } catch (\Throwable $e) {
            Log::error('| Presensi | - Error saat download template presensi: ' . $e->getMessage() . ' Line: ' . $e->getLine());
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error.'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importPresensi(ImportPresensiRequest $request)
    {
        if (!Gate::allows('import presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new PresensiImportNew, $file['presensi_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data presensi karyawan berhasil di import kedalam tabel.'), Response::HTTP_OK);
    }
}
