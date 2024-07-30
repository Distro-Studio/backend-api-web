<?php

namespace App\Http\Controllers\Dashboard\Presensi;

use Carbon\Carbon;
use App\Models\Berkas;
use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\LokasiKantor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Helpers\StorageSeverHelper;
use Illuminate\Support\Facades\Log;
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

        $lokasi_kantor = LokasiKantor::orderBy('updated_at', 'desc')->get();
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

        $today = Carbon::today()->format('Y-m-d');

        // Ambil ID untuk setiap kategori dari tabel kategori_presensis
        $kategoriTepatWaktuId = DB::table('kategori_presensis')->where('label', 'Tepat Waktu')->value('id');
        $kategoriTerlambatId = DB::table('kategori_presensis')->where('label', 'Terlambat')->value('id');
        $kategoriCutiId = DB::table('kategori_presensis')->where('label', 'Cuti')->value('id');
        $kategoriAbsenId = DB::table('kategori_presensis')->where('label', 'Absen')->value('id');

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

        $countLibur = Jadwal::whereNull('shift_id')
            ->whereDate('tgl_mulai', '<=', $today)
            ->whereDate('tgl_selesai', '>=', $today)
            ->count('user_id');

        $totalHadir = $countTepatWaktu + $countTerlambat;
        $totalTidakHadir = $countCuti + $countAbsen + $countLibur;

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Perhitungan presensi seluruh karyawan berhasil ditampilkan.',
            'data' => [
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

        $presensi = Presensi::query();

        // Filter
        if ($request->has('tanggal')) {
            $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');
            $presensi->whereDate('jam_masuk', $tanggal);
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Pilih tanggal terlebih dahulu untuk menampilkan presensi.'), Response::HTTP_BAD_REQUEST);
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $presensi->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        if ($request->has('status_karyawan')) {
            if (is_array($request->status_karyawan)) {
                $presensi->whereHas('data_karyawans.status_karyawans', function ($query) use ($request) {
                    $query->whereIn('label', $request->status_karyawan);
                });
            } else {
                $presensi->whereHas('data_karyawans.status_karyawans', function ($query) use ($request) {
                    $query->where('label', $request->status_karyawan);
                });
            }
        }

        if ($request->has('masa_kerja')) {
            $masa_kerja = $request->masa_kerja;
            $presensi->whereHas('data_karyawans', function ($query) use ($masa_kerja) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$masa_kerja]);
            });
        }

        if ($request->has('status_aktif')) {
            $statusAktif = $request->status_aktif;
            $presensi->whereHas('users', function ($query) use ($statusAktif) {
                $query->where('status_aktif', $statusAktif);
            });
        }

        if ($request->has('tgl_masuk')) {
            $tglMasuk = $request->tgl_masuk;
            $tglMasuk = Carbon::parse($tglMasuk)->format('Y-m-d');
            $presensi->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                $query->where('tgl_masuk', $tglMasuk);
            });
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $presensi->where(function ($query) use ($searchTerm) {
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('data_karyawans.unit_kerjas', function ($query) use ($searchTerm) {
                    $query->where('nama_unit', 'like', $searchTerm);
                });
            });
        }

        // Pastikan limit adalah integer
        $limit = is_numeric($limit) ? (int)$limit : 10;

        $dataPresensi = $presensi->paginate($limit);

        if ($dataPresensi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data untuk output
        $formattedData = $dataPresensi->items();
        $formattedData = array_map(function ($presensi) {
            return [
                'id' => $presensi->id,
                'user' => $presensi->users,
                'unit_kerja' => $presensi->data_karyawans->unit_kerjas,
                'jam_masuk' => $presensi->jam_masuk,
                'jam_keluar' => $presensi->jam_keluar,
                'created_at' => $presensi->created_at,
                'updated_at' => $presensi->updated_at
            ];
        }, $formattedData);

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

        $presensi = Presensi::find($id);

        if (!$presensi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $fotoMasukBerkas = Berkas::where('id', $presensi->foto_masuk)->first();
        $fotoKeluarBerkas = Berkas::where('id', $presensi->foto_keluar)->first();

        $baseUrl = env('STORAGE_SERVER_DOMAIN'); // Ganti dengan URL domain Anda

        $fotoMasukExt = $fotoMasukBerkas ? StorageServerHelper::getExtensionFromMimeType($fotoMasukBerkas->ext) : null;
        $fotoMasukUrl = $fotoMasukBerkas ? $baseUrl . $fotoMasukBerkas->path . '.' . $fotoMasukExt : null;

        $fotoKeluarExt = $fotoKeluarBerkas ? StorageServerHelper::getExtensionFromMimeType($fotoKeluarBerkas->ext) : null;
        $fotoKeluarUrl = $fotoKeluarBerkas ? $baseUrl . $fotoKeluarBerkas->path . '.' . $fotoKeluarExt : null;


        $formattedData = [
            'id' => $presensi->id,
            'user' => $presensi->users,
            'unit_kerja' => $presensi->data_karyawans->unit_kerjas,
            'jadwal' => $presensi->jadwals,
            'jam_masuk' => $presensi->jam_masuk,
            'jam_keluar' => $presensi->jam_keluar,
            'durasi' => $presensi->durasi,
            'lat_masuk' => $presensi->lat,
            'long_masuk' => $presensi->long,
            'lat_keluar' => $presensi->latkeluar,
            'long_keluar' => $presensi->longkeluar,
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
            'kategori_presensi' => $presensi->kategori_presensis,
            'created_at' => $presensi->created_at,
            'updated_at' => $presensi->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Detail data presensi berhasil ditampilkan.',
            'data' => $formattedData,
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

        // Error
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $presensiCount = Presensi::whereBetween('jam_masuk', [$startDate, $endDate])->count();

        if ($presensiCount === 0) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan untuk periode yang diminta.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new PresensiExport([$month], $year), 'presensi-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data presensi karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function downloadPresensiTemplate()
    {
        if (!Gate::allows('export presensiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new TemplateImportPresensiExport, 'template_import_presensi.csv');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Template import presensi karyawan berhasil di download.'), Response::HTTP_OK);
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
