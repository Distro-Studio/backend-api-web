<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use Carbon\Carbon;
use App\Models\DetailGaji;
use App\Models\Notifikasi;
use App\Models\Penggajian;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\PenyesuaianGaji;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Keuangan\PenyesuaianGajiExport;
use App\Http\Requests\StorePenyesuaianGajiRequest;
use App\Http\Requests\StorePenyesuaianGajiCustomRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Keuangan\PenyesuaianGajiResource;

class PenyesuaianGajiController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Tentukan limit default
        $limit = $request->input('limit', 10); // Default 10 jika tidak ada atau kosong

        $PenyesuaianGaji = PenyesuaianGaji::query()->orderBy('created_at', 'desc');

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $PenyesuaianGaji->whereHas('penggajians.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $PenyesuaianGaji->whereHas('penggajians.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $PenyesuaianGaji->whereHas('penggajians.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $PenyesuaianGaji->whereHas('penggajians.data_karyawans.users', function ($query) use ($statusAktif) {
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
                $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $PenyesuaianGaji->whereHas('penggajians.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $PenyesuaianGaji->whereHas('penggajians.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $PenyesuaianGaji->whereHas('penggajians.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $PenyesuaianGaji->whereHas('penggajians.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $PenyesuaianGaji->where(function ($query) use ($searchTerm) {
                $query->whereHas('penggajians.data_karyawans.users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('penggajians.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($limit == 0) {
            $dataPenyesuaianGaji = $PenyesuaianGaji->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataPenyesuaianGaji = $PenyesuaianGaji->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataPenyesuaianGaji->url(1),
                    'last' => $dataPenyesuaianGaji->url($dataPenyesuaianGaji->lastPage()),
                    'prev' => $dataPenyesuaianGaji->previousPageUrl(),
                    'next' => $dataPenyesuaianGaji->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataPenyesuaianGaji->currentPage(),
                    'last_page' => $dataPenyesuaianGaji->lastPage(),
                    'per_page' => $dataPenyesuaianGaji->perPage(),
                    'total' => $dataPenyesuaianGaji->total(),
                ]
            ];
        }
        if ($dataPenyesuaianGaji->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penyesuaian pengggajian karyawan yang tersedia.'), Response::HTTP_OK);
        }

        $formattedData = $dataPenyesuaianGaji->map(function ($penyesuaianGaji) {
            return [
                'id' => $penyesuaianGaji->id,
                'user' => $penyesuaianGaji->penggajians->data_karyawans->users,
                'unit_kerja' => $penyesuaianGaji->penggajians->data_karyawans->unit_kerjas,
                'kelompok_gaji' => $penyesuaianGaji->penggajians->data_karyawans->kelompok_gajis,
                'ptkp' => $penyesuaianGaji->penggajians->data_karyawans->ptkps,
                'kategori_gaji_id' => $penyesuaianGaji->kategori_gajis,
                'nama_detail' => $penyesuaianGaji->nama_detail,
                'besaran' => $penyesuaianGaji->besaran,
                'bulan_mulai' => $penyesuaianGaji->bulan_mulai,
                'bulan_selesai' => $penyesuaianGaji->bulan_selesai,
                'created_at' => $penyesuaianGaji->created_at,
                'updated_at' => $penyesuaianGaji->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data penyesuaian pengggajian karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StorePenyesuaianGajiRequest $request)
    {
        if (!Gate::allows('create penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kategori_penambah = DB::table('kategori_gajis')->where('label', 'Penambah')->value('id');
        $kategori_pengurang = DB::table('kategori_gajis')->where('label', 'Pengurang')->value('id');

        DB::beginTransaction();
        try {
            $responses = [];

            foreach ($data['penggajian_id'] as $penggajian_id) {
                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;

                // Cek apakah penggajian_id valid
                $penggajian = Penggajian::find($penggajian_id);
                if (!$penggajian) {
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penggajian terkait tidak ditemukan.'), Response::HTTP_NOT_FOUND);
                }

                // Validasi berdasarkan status_gaji_id
                if ($penggajian->status_gaji_id == 2) { // Status 2 berarti penggajian sudah dipublikasikan
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Penyesuaian gaji tidak dapat dilakukan karena penggajian sudah dipublikasikan.'), Response::HTTP_NOT_ACCEPTABLE);
                }

                // Tentukan kategori penyesuaian
                if ($data['kategori_gaji'] == 1) {
                    $kategori = $kategori_penambah;
                    $message = "Penyesuaian gaji '{$data['nama_detail']}' berhasil dilakukan untuk penambah Take Home Pay.";
                } else {
                    $kategori = $kategori_pengurang;
                    $message = "Penyesuaian gaji '{$data['nama_detail']}' berhasil dilakukan untuk pengurang Take Home Pay.";
                }

                $penyesuaianGaji = PenyesuaianGaji::create([
                    'penggajian_id' => $penggajian_id,
                    'kategori_gaji_id' => $kategori,
                    'nama_detail' => $data['nama_detail'],
                    'besaran' => $data['besaran'],
                    'bulan_mulai' => $data['bulan_mulai'],
                    'bulan_selesai' => $data['bulan_selesai']
                ]);

                // Cek apakah bulan mulai adalah bulan saat ini
                $bulanMulai = Carbon::parse(RandomHelper::convertToDateString($data['bulan_mulai']));
                if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
                    // Kurangi atau tambah take home pay sesuai dengan kategori
                    if ($kategori == $kategori_penambah) {
                        $penggajian->take_home_pay += $data['besaran'];
                    } else {
                        $penggajian->take_home_pay -= $data['besaran'];
                    }
                    $penggajian->save();

                    // Simpan detail gaji ke tabel detail_gajis
                    DetailGaji::create([
                        'penggajian_id' => $penggajian_id,
                        'kategori_gaji_id' => $kategori,
                        'nama_detail' => $penyesuaianGaji->nama_detail,
                        'besaran' => $penyesuaianGaji->besaran
                    ]);
                }

                // Kirim notifikasi kepada karyawan yang terkait
                $this->createNotifikasiPenyesuaianGaji($penggajian, $penyesuaianGaji);

                $responses[] = $penyesuaianGaji;
            }

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => $message,
                'data' => $responses
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan penyesuaian gaji: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportPenyesuaianGaji()
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataCuti = PenyesuaianGaji::all();
        if ($dataCuti->isEmpty()) {
            // Kembalikan respons JSON ketika tabel kosong
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penyesuaian gaji karyawan yang tersedia untuk diekspor.'), Response::HTTP_OK);
        }

        try {
            return Excel::download(new PenyesuaianGajiExport(), 'penyesuaian-gaji-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Penyesuaian Gaji
    public function storePenyesuaianGajiPenambah(StorePenyesuaianGajiCustomRequest $request, $penggajian_id)
    {
        if (!Gate::allows('create penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Cek apakah penggajian_id valid
        $penggajian = Penggajian::find($penggajian_id);
        if (!$penggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            // Simpan penyesuaian gaji
            $penyesuaianGaji = PenyesuaianGaji::create([
                'penggajian_id' => $penggajian_id,
                'kategori_gaji_id' => 2,
                'nama_detail' => $request->nama_detail,
                'besaran' => $request->besaran,
                'bulan_mulai' => $request->bulan_mulai,
                'bulan_selesai' => $request->bulan_selesai,
            ]);

            // Cek apakah bulan mulai adalah bulan saat ini
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            // $bulanMulai = Carbon::parse($request->bulan_mulai);
            $bulanMulai = Carbon::parse(RandomHelper::convertToDateString($request->bulan_mulai));

            if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
                // Kurangi take home pay dengan besaran penyesuaian yang baru dibuat
                $penggajian->take_home_pay += $request->besaran;
                $penggajian->save();

                // Simpan detail gaji ke tabel detail_gajis
                DetailGaji::create([
                    'penggajian_id' => $penggajian_id,
                    'kategori_gaji_id' => 2,
                    'nama_detail' => $penyesuaianGaji->nama_detail,
                    'besaran' => $penyesuaianGaji->besaran
                ]);
            }

            DB::commit();

            $userName = $penggajian->data_karyawans->users->nama;

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Penambahan penggajian '{$penyesuaianGaji->nama_detail}' berhasil dilakukan untuk karyawan '{$userName}'.",
                'data' => $penyesuaianGaji
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan penyesuaian gaji: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storePenyesuaianGajiPengurang(StorePenyesuaianGajiCustomRequest $request, $penggajian_id)
    {
        if (!Gate::allows('create penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Cek apakah penggajian_id valid
        $penggajian = Penggajian::find($penggajian_id);
        if (!$penggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            // Simpan penyesuaian gaji
            $penyesuaianGaji = PenyesuaianGaji::create([
                'penggajian_id' => $penggajian_id,
                'kategori_gaji_id' => 3,
                'nama_detail' => $request->nama_detail,
                'besaran' => $request->besaran,
                'bulan_mulai' => $request->bulan_mulai,
                'bulan_selesai' => $request->bulan_selesai,
            ]);

            // Cek apakah bulan mulai adalah bulan saat ini
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $bulanMulai = Carbon::parse(RandomHelper::convertToDateString($request->bulan_mulai));

            if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
                // Kurangi take home pay dengan besaran penyesuaian yang baru dibuat
                $penggajian->take_home_pay -= $request->besaran;
                $penggajian->save();

                // Simpan detail gaji ke tabel detail_gajis
                DetailGaji::create([
                    'penggajian_id' => $penggajian_id,
                    'kategori_gaji_id' => 3,
                    'nama_detail' => $penyesuaianGaji->nama_detail,
                    'besaran' => $penyesuaianGaji->besaran
                ]);
            }

            DB::commit();

            $userName = $penggajian->data_karyawans->users->nama;

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Pengurangan penggajian '{$penyesuaianGaji->nama_detail}' berhasil dilakukan untuk karyawan '{$userName}'.",
                'data' => $penyesuaianGaji
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan penyesuaian gaji: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiPenyesuaianGaji($penggajian, $penyesuaianGaji)
    {
        // Dapatkan user yang terkait dengan penggajian
        $user = $penggajian->data_karyawans->users;

        // Siapkan pesan notifikasi
        if ($penyesuaianGaji->kategori_gaji_id == 1) {
            $message = "Penyesuaian gaji untuk {$penyesuaianGaji->nama_detail} telah dilakukan, Silahkan lakukan pengecekkan kembali dan pastikan gaji telah sesuai.";
        } else {
            $message = "Penyesuaian gaji untuk {$penyesuaianGaji->nama_detail} telah dilakukan, Silahkan lakukan pengecekkan kembali dan pastikan gaji telah sesuai.";
        }

        // Buat notifikasi untuk user yang terkait
        Notifikasi::create([
            'kategori_notifikasi_id' => 9,
            'user_id' => $user->id,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}
