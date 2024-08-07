<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\TipeCuti;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\CutiJadwalExport;
use App\Http\Requests\StoreCutiJadwalRequest;
use App\Http\Requests\UpdateCutiJadwalRequest;
use App\Http\Resources\Dashboard\Jadwal\CutiJadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataCutiController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $cuti = Cuti::query();

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $cuti->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $cuti->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $cuti->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $cuti->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $cuti->whereHas('users.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $cuti->whereHas('users', function ($query) use ($statusAktif) {
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
                $cuti->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $cuti->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $cuti->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $cuti->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $cuti->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $cuti->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $cuti->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $cuti->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $cuti->where(function ($query) use ($searchTerm) {
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        // Paginate
        if ($limit == 0) {
            $dataCuti = $cuti->get();
            $paginationData = null;
        } else {
            // Pastikan limit adalah integer
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataCuti = $cuti->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataCuti->url(1),
                    'last' => $dataCuti->url($dataCuti->lastPage()),
                    'prev' => $dataCuti->previousPageUrl(),
                    'next' => $dataCuti->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataCuti->currentPage(),
                    'last_page' => $dataCuti->lastPage(),
                    'per_page' => $dataCuti->perPage(),
                    'total' => $dataCuti->total(),
                ]
            ];
        }

        if ($dataCuti->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataCuti->map(function ($dataCuti) {
            return [
                'id' => $dataCuti->id,
                'user' => [
                    'id' => $dataCuti->users->id,
                    'nama' => $dataCuti->users->nama,
                    'email_verified_at' => $dataCuti->users->email_verified_at,
                    'data_karyawan_id' => $dataCuti->users->data_karyawan_id,
                    'foto_profil' => $dataCuti->users->foto_profil,
                    'data_completion_step' => $dataCuti->users->data_completion_step,
                    'status_aktif' => $dataCuti->users->status_aktif,
                    'created_at' => $dataCuti->users->created_at,
                    'updated_at' => $dataCuti->users->updated_at
                ],
                'unit_kerja' => $dataCuti->users->data_karyawans->unit_kerjas,
                'tipe_cuti' => $dataCuti->tipe_cutis,
                'tgl_from' => $dataCuti->tgl_from,
                'tgl_to' => $dataCuti->tgl_to,
                'catatan' => $dataCuti->catatan,
                'durasi' => $dataCuti->durasi,
                'status_cuti' => $dataCuti->status_cutis,
                'created_at' => $dataCuti->created_at,
                'updated_at' => $dataCuti->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data cuti karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreCutiJadwalRequest $request)
    {
        if (!Gate::allows('create cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Mengonversi tanggal dari request menggunakan helper hanya untuk perhitungan durasi
        $tglFrom = RandomHelper::convertToDateString($data['tgl_from']);
        $tglTo = RandomHelper::convertToDateString($data['tgl_to']);

        // Menghitung durasi cuti dalam hari
        $durasi = Carbon::parse($tglFrom)->diffInDays(Carbon::parse($tglTo)) + 1;

        // Validasi durasi cuti terhadap kuota tipe cuti
        $tipeCuti = TipeCuti::find($data['tipe_cuti_id']);
        if (!$tipeCuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tipe cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Cek apakah pengguna sudah mengambil cuti dengan tipe yang sama dalam tahun berjalan
        $currentYear = Carbon::now()->year;
        $cutiTakenThisYear = Cuti::where('user_id', $data['user_id'])
            ->where('tipe_cuti_id', $data['tipe_cuti_id'])
            ->whereYear('tgl_from', $currentYear)
            ->sum('durasi');

        // Jika tipe cuti memiliki kuota (kuota > 0) dan durasi melebihi kuota, tampilkan pesan error
        $sisaCuti = $tipeCuti->kuota - $cutiTakenThisYear;
        if ($tipeCuti->kuota > 0 && $durasi > $sisaCuti) {
            $message = "Durasi cuti ({$durasi} hari) melebihi sisa kuota yang diizinkan untuk tipe cuti '{$tipeCuti->nama}'. Sisa kuota cuti tahun ini: {$sisaCuti} hari.";
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $message), Response::HTTP_BAD_REQUEST);
        }

        // Menambahkan durasi ke data sebelum menyimpan
        $data['durasi'] = $durasi;
        $dataCuti = Cuti::create($data);

        $message = "Data cuti karyawan '{$dataCuti->users->nama}' berhasil dibuat untuk tipe cuti '{$dataCuti->tipe_cutis->nama}' dengan durasi {$dataCuti->durasi} hari.";

        return response()->json(new CutiJadwalResource(Response::HTTP_OK, $message, $dataCuti), Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataCuti = Cuti::with(['users.data_karyawans.unit_kerjas', 'users.cutis', 'tipe_cutis', 'status_cutis'])->find($id);
        if (!$dataCuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $listCuti = $dataCuti->users->cutis->map(function ($cuti) {
            return [
                'id' => $cuti->id,
                'tipe_cuti' => $cuti->tipe_cutis,
                'tgl_from' => $cuti->tgl_from,
                'tgl_to' => $cuti->tgl_to,
                'catatan' => $cuti->catatan,
                'durasi' => $cuti->durasi,
                'status_cuti' => $cuti->status_cutis,
                'created_at' => $cuti->created_at,
                'updated_at' => $cuti->updated_at
            ];
        });

        $formattedData = [
            'id' => $dataCuti->id,
            'user' => [
                'id' => $dataCuti->users->id,
                'nama' => $dataCuti->users->nama,
                'email_verified_at' => $dataCuti->users->email_verified_at,
                'data_karyawan_id' => $dataCuti->users->data_karyawan_id,
                'foto_profil' => $dataCuti->users->foto_profil,
                'data_completion_step' => $dataCuti->users->data_completion_step,
                'status_aktif' => $dataCuti->users->status_aktif,
                'created_at' => $dataCuti->users->created_at,
                'updated_at' => $dataCuti->users->updated_at
            ],
            'unit_kerja' => $dataCuti->users->data_karyawans->unit_kerjas,
            'list_cuti' => $listCuti
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data cuti karyawan '{$dataCuti->users->nama}' berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update(UpdateCutiJadwalRequest $request, $id)
    {
        if (!Gate::allows('edit cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $dataCuti = Cuti::find($id);
        if (!$dataCuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data cuti karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Mengonversi tanggal dari request menggunakan helper hanya untuk perhitungan durasi
        $tglFrom = RandomHelper::convertToDateString($data['tgl_from']);
        $tglTo = RandomHelper::convertToDateString($data['tgl_to']);

        // Menghitung durasi cuti dalam hari
        $durasi = Carbon::parse($tglFrom)->diffInDays(Carbon::parse($tglTo)) + 1;

        // Validasi durasi cuti terhadap kuota tipe cuti
        $tipeCuti = TipeCuti::find($data['tipe_cuti_id']);
        if (!$tipeCuti) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tipe cuti tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Periksa cuti yang telah diambil pada tahun berjalan
        $currentYear = Carbon::now()->year;
        $cutiTakenThisYear = Cuti::where('user_id', $dataCuti->user_id)
            ->where('tipe_cuti_id', $data['tipe_cuti_id'])
            ->whereYear('tgl_from', $currentYear)
            ->where('id', '!=', $id)
            ->sum('durasi');

        // Tambahkan durasi cuti yang sedang diupdate
        $totalCutiTaken = $cutiTakenThisYear + $durasi;

        // Hitung sisa kuota cuti
        $sisaCuti = $tipeCuti->kuota - $cutiTakenThisYear;
        if ($tipeCuti->kuota > 0 && $totalCutiTaken > $tipeCuti->kuota) {
            $message = "Durasi cuti ({$durasi} hari) melebihi sisa kuota yang diizinkan untuk tipe cuti '{$tipeCuti->nama}' ({$sisaCuti} hari tersisa).";
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $message), Response::HTTP_BAD_REQUEST);
        }

        // Menambahkan durasi ke data sebelum memperbarui
        $data['durasi'] = $durasi;

        $dataCuti->update($data);

        $message = "Data cuti karyawan '{$dataCuti->users->nama}' berhasil diperbarui untuk tipe cuti '{$dataCuti->tipe_cutis->nama}' dengan durasi {$dataCuti->durasi} hari.";

        return response()->json(new CutiJadwalResource(Response::HTTP_OK, $message, $dataCuti), Response::HTTP_OK);
    }

    public function exportJadwalCuti()
    {
        if (!Gate::allows('export cutiKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new CutiJadwalExport(), 'cuti-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jadwal cuti karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
