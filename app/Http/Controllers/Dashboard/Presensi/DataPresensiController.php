<?php

namespace App\Http\Controllers\Dashboard\Presensi;

use Carbon\Carbon;
use App\Models\Berkas;
use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\HariLibur;
use App\Models\DataKaryawan;
use App\Models\LokasiKantor;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Presensi\PresensiExport;
use App\Imports\Presensi\PresensiImport;
use App\Exports\Presensi\TemplateImportPresensiExport;
use App\Http\Requests\Excel_Import\ImportPresensiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

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

        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        // Ambil ID untuk setiap kategori dari tabel kategori_presensis
        $kategoriTepatWaktuId = DB::table('kategori_presensis')->where('label', 'Tepat Waktu')->value('id');
        $kategoriTerlambatId = DB::table('kategori_presensis')->where('label', 'Terlambat')->value('id');
        $kategoriCutiId = DB::table('kategori_presensis')->where('label', 'Cuti')->value('id');
        $kategoriAbsenId = DB::table('kategori_presensis')->where('label', 'Absen')->value('id');

        // Validasi untuk memastikan kategori ditemukan
        if (is_null($kategoriTepatWaktuId) || is_null($kategoriTerlambatId) || is_null($kategoriCutiId) || is_null($kategoriAbsenId)) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Kategori presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Calculate total number of employees excluding the super admin
        $calculatedKaryawan = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')->count();

        // Hitung jumlah presensi dalam setiap kategori
        $countTepatWaktu = Presensi::where('kategori_presensi_id', $kategoriTepatWaktuId)
            ->whereDate('jam_masuk', $today)
            ->count('user_id');

        $countTerlambat = Presensi::where('kategori_presensi_id', $kategoriTerlambatId)
            ->whereDate('jam_masuk', $today)
            ->count('user_id');

        $countCuti = Presensi::where('kategori_presensi_id', $kategoriCutiId)
            ->whereDate('jam_masuk', $today)
            ->count('user_id');

        $countAbsen = Presensi::where('kategori_presensi_id', $kategoriAbsenId)
            ->whereDate('jam_masuk', $today)
            ->count('user_id');

        // Hitung karyawan shift yang libur berdasarkan user_id
        $countLiburShift = Jadwal::where('shift_id', 0)
            ->whereDate('tgl_mulai', '<=', $today)
            ->whereDate('tgl_selesai', '>=', $today)
            ->count('user_id');

        // Periksa apakah hari ini adalah hari libur
        $isHariLibur = HariLibur::whereDate('tanggal', $today)->exists();

        // Hitung karyawan non-shift yang libur berdasarkan hari libur
        $countLiburNonShift = DataKaryawan::whereHas('unit_kerjas', function ($query) {
            $query->where('jenis_karyawan', 0);
        })->when($isHariLibur, function ($query) {
            return $query->distinct('id')->count('id');  // Hitung berdasarkan user_id
        }, function ($query) {
            return 0;
        });

        // Total karyawan yang libur
        $countLibur = $countLiburShift + $countLiburNonShift;

        // Hitung total hadir dan total tidak hadir
        $totalHadir = $countTepatWaktu + $countTerlambat;
        $totalTidakHadir = $countCuti + $countAbsen;

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

        $presensi = Presensi::query()->orderBy('created_at', 'desc');

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if ($request->has('tanggal')) {
            $tanggal = RandomHelper::convertToDateString($request->tanggal);
            $presensi->whereDate('created_at', $tanggal);
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Pilih tanggal terlebih dahulu untuk menampilkan presensi.'), Response::HTTP_BAD_REQUEST);
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
            if (is_array($masaKerja)) {
                $presensi->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $presensi->whereHas('users.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
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
                $convertedDates = array_map([RandomHelper::class, 'convertToDateString'], $tglMasuk);
                $presensi->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $presensi->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
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
                    'id' => $presensi->jadwals->id,
                    'tgl_mulai' => $presensi->jadwals->tgl_mulai,
                    'tgl_selesai' => $presensi->jadwals->tgl_selesai,
                    'shift' => $presensi->jadwals->shifts,
                ],
                'jam_masuk' => $presensi->jam_masuk,
                'jam_keluar' => $presensi->jam_keluar,
                'durasi' => $presensi->durasi,
                'kategori_presensi' => $presensi->kategori_presensis,
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

    public function show($id) // ini detail lv 1
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

        // Ambil semua presensi bulan ini dari karyawan yang sama
        $presensiBulanIni = Presensi::where('id', $id)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->orderBy('created_at', 'desc')
            ->get();

        // Memformat aktivitas presensi
        $aktivitasPresensi = [];
        foreach ($presensiBulanIni as $presensi) {
            if ($presensi->created_at) {
                $aktivitasPresensi[] = [
                    'presensi' => 'Masuk',
                    'tanggal' => $presensi->created_at,
                    'lat_masuk' => $presensiHariIni->lat,
                    'long_masuk' => $presensiHariIni->long,
                    'foto_masuk' => [
                        'id' => $fotoMasukBerkas->id,
                        'user_id' => $fotoMasukBerkas->user_id,
                        'file_id' => $fotoMasukBerkas->file_id,
                        'nama' => $fotoMasukBerkas->nama,
                        'nama_file' => $fotoMasukBerkas->nama_file,
                        'path' => $fotoMasukUrl,
                        'ext' => $fotoMasukBerkas->ext,
                        'size' => $fotoMasukBerkas->size,
                    ],
                ];
            }
            if ($presensi->updated_at) {
                $aktivitasPresensi[] = [
                    'presensi' => 'Keluar',
                    'tanggal' => $presensi->updated_at,
                    'lat_keluar' => $presensiHariIni->latkeluar,
                    'long_keluar' => $presensiHariIni->longkeluar,
                    'foto_keluar' => [
                        'id' => $fotoKeluarBerkas->id,
                        'user_id' => $fotoKeluarBerkas->user_id,
                        'file_id' => $fotoKeluarBerkas->file_id,
                        'nama' => $fotoKeluarBerkas->nama,
                        'nama_file' => $fotoKeluarBerkas->nama_file,
                        'path' => $fotoKeluarUrl,
                        'ext' => $fotoKeluarBerkas->ext,
                        'size' => $fotoKeluarBerkas->size,
                    ],
                ];
            }
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail data presensi karyawan '{$presensiHariIni->users->nama}' berhasil ditampilkan.",
            'data' => [
                'id' => $presensiHariIni->id,
                'user' => $presensiHariIni->users,
                'unit_kerja' => $presensiHariIni->data_karyawans->unit_kerjas,
                'data_presensi' => [
                    'jadwal' => [
                        'id' => $presensiHariIni->jadwals->id,
                        'tgl_mulai' => $presensiHariIni->jadwals->tgl_mulai,
                        'tgl_selesai' => $presensiHariIni->jadwals->tgl_selesai,
                        'shift' => $presensiHariIni->jadwals->shifts,
                    ],
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
                    'foto_masuk' => [
                        'id' => $fotoMasukBerkas->id,
                        'user_id' => $fotoMasukBerkas->user_id,
                        'file_id' => $fotoMasukBerkas->file_id,
                        'nama' => $fotoMasukBerkas->nama,
                        'nama_file' => $fotoMasukBerkas->nama_file,
                        'path' => $fotoMasukUrl,
                        'ext' => $fotoMasukBerkas->ext,
                        'size' => $fotoMasukBerkas->size,
                    ],
                    'foto_keluar' => [
                        'id' => $fotoKeluarBerkas->id,
                        'user_id' => $fotoKeluarBerkas->user_id,
                        'file_id' => $fotoKeluarBerkas->file_id,
                        'nama' => $fotoKeluarBerkas->nama,
                        'nama_file' => $fotoKeluarBerkas->nama_file,
                        'path' => $fotoKeluarUrl,
                        'ext' => $fotoKeluarBerkas->ext,
                        'size' => $fotoKeluarBerkas->size,
                    ],
                ],
                'list_presensi' => $aktivitasPresensi
            ],
        ], Response::HTTP_OK);
    }

    public function exportPresensi(Request $request)
    {
        if (!Gate::allows('export presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $month = $request->input('month');
        $year = $request->input('year');

        if (empty($month) || empty($year)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Periode bulan dan tahun tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
        }

        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal yang dimasukkan tidak valid.'), Response::HTTP_BAD_REQUEST);
        }

        $presensiCount = Presensi::whereBetween('jam_masuk', [$startDate, $endDate])->count();

        if ($presensiCount === 0) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan untuk periode yang diminta.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new PresensiExport([$month], $year), 'presensi-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function downloadPresensiTemplate()
    {
        try {
            return Excel::download(new TemplateImportPresensiExport, 'template_import_presensi.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importPresensi(ImportPresensiRequest $request)
    {
        if (!Gate::allows('import presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new PresensiImport, $file['presensi_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data presensi karyawan berhasil di import kedalam tabel.'), Response::HTTP_OK);
    }
}
