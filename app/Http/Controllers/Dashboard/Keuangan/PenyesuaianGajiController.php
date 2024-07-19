<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use Carbon\Carbon;
use App\Models\DetailGaji;
use App\Models\Penggajian;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\PenyesuaianGaji;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Keuangan\PenyesuaianGajiExport;
use App\Http\Requests\StorePenyesuaianGajiCustomRequest;
use App\Http\Requests\StorePenyesuaianGajiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Keuangan\PenyesuaianGajiResource;

class PenyesuaianGajiController extends Controller
{
    public function getAllKaryawanPenggajian()
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $penggajian = Penggajian::with(['data_karyawans.users', 'data_karyawans.kelompok_gajis'])->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all penggajian for dropdown',
            'data' => $penggajian
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $PenyesuaianGaji = PenyesuaianGaji::query()->with('penggajians');

        // Filter
        if ($request->has('status_karyawan')) {
            $statuskaryawan = $request->status_karyawan;
            $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($statuskaryawan) {
                if (is_array($statuskaryawan)) {
                    $query->whereIn('status_karyawan', $statuskaryawan);
                } else {
                    $query->where('status_karyawan', '=', $statuskaryawan);
                }
            });
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;
            $PenyesuaianGaji->whereHas('penggajians.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';

            $PenyesuaianGaji->where(function ($query) use ($searchTerm) {
                $query->whereHas('penggajians.data_karyawans.users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('penggajians.data_karyawans.unit_kerjas', function ($query) use ($searchTerm) {
                    $query->where('nama_unit', 'like', $searchTerm);
                });
            });
        }

        $dataPenyesuaianGaji = $PenyesuaianGaji->paginate(10);
        if ($dataPenyesuaianGaji->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penyesuaian pengggajian karyawan yang tersedia.'), Response::HTTP_OK);
        }

        $formattedData = $dataPenyesuaianGaji->items();
        $formattedData = array_map(function ($penyesuaianGaji) {
            return [
                'id' => $penyesuaianGaji->id,
                'user' => $penyesuaianGaji->penggajians->data_karyawans->users,
                'unit_kerja' => $penyesuaianGaji->penggajians->data_karyawans->unit_kerjas,
                'kelompok_gaji' => $penyesuaianGaji->penggajians->data_karyawans->kelompok_gajis,
                'ptkp' => $penyesuaianGaji->penggajians->data_karyawans->ptkps,
                'kategori' => $penyesuaianGaji->kategori,
                'nama_detail' => $penyesuaianGaji->nama_detail,
                'besaran' => $penyesuaianGaji->besaran,
                'bulan_mulai' => $penyesuaianGaji->bulan_mulai,
                'bulan_selesai' => $penyesuaianGaji->bulan_selesai,
                'created_at' => $penyesuaianGaji->created_at,
                'updated_at' => $penyesuaianGaji->updated_at
            ];
        }, $formattedData);

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

                // Tentukan kategori penyesuaian
                $kategori = $data['kategori'] === 'penambah' ? PenyesuaianGaji::STATUS_PENAMBAH : PenyesuaianGaji::STATUS_PENGURANG;

                $penyesuaianGaji = PenyesuaianGaji::create([
                    'penggajian_id' => $penggajian_id,
                    'kategori' => $kategori,
                    'nama_detail' => $data['nama_detail'],
                    'besaran' => $data['besaran'],
                    'bulan_mulai' => $data['bulan_mulai'],
                    'bulan_selesai' => $data['bulan_selesai']
                ]);

                // Cek apakah bulan mulai adalah bulan saat ini

                $bulanMulai = Carbon::parse($data['bulan_mulai']);
                if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
                    // Kurangi atau tambah take home pay sesuai dengan kategori
                    if ($kategori == PenyesuaianGaji::STATUS_PENAMBAH) {
                        $penggajian->take_home_pay += $data['besaran'];
                    } else {
                        $penggajian->take_home_pay -= $data['besaran'];
                    }
                    $penggajian->save();

                    // Simpan detail gaji ke tabel detail_gajis
                    DetailGaji::create([
                        'penggajian_id' => $penggajian_id,
                        'kategori' => $kategori,
                        'nama_detail' => $penyesuaianGaji->nama_detail,
                        'besaran' => $penyesuaianGaji->besaran
                    ]);
                }

                $responses[] = $penyesuaianGaji;
            }

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Penyesuaian gaji berhasil dilakukan untuk pengurang atau penambah Take Home Pay.',
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

        try {
            return Excel::download(new PenyesuaianGajiExport(), 'keuangan-penyesuaian-gaji.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data presensi karyawan berhasil di download.'), Response::HTTP_OK);
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
                'kategori' => PenyesuaianGaji::STATUS_PENAMBAH,
                'nama_detail' => $request->nama_detail,
                'besaran' => $request->besaran,
                'bulan_mulai' => $request->bulan_mulai,
                'bulan_selesai' => $request->bulan_selesai,
            ]);

            // Cek apakah bulan mulai adalah bulan saat ini
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $bulanMulai = Carbon::parse($request->bulan_mulai);

            if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
                // Kurangi take home pay dengan besaran penyesuaian yang baru dibuat
                $penggajian->take_home_pay += $request->besaran;
                $penggajian->save();

                // Simpan detail gaji ke tabel detail_gajis
                DetailGaji::create([
                    'penggajian_id' => $penggajian_id,
                    'kategori' => DetailGaji::STATUS_PENAMBAH,
                    'nama_detail' => $penyesuaianGaji->nama_detail,
                    'besaran' => $penyesuaianGaji->besaran
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Pengurangan Take Home Pay dari {$penyesuaianGaji->nama_detail} berhasil dilakukan.",
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
                'kategori' => PenyesuaianGaji::STATUS_PENGURANG,
                'nama_detail' => $request->nama_detail,
                'besaran' => $request->besaran,
                'bulan_mulai' => $request->bulan_mulai,
                'bulan_selesai' => $request->bulan_selesai,
            ]);

            // Cek apakah bulan mulai adalah bulan saat ini
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $bulanMulai = Carbon::parse($request->bulan_mulai);

            if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
                // Kurangi take home pay dengan besaran penyesuaian yang baru dibuat
                $penggajian->take_home_pay -= $request->besaran;
                $penggajian->save();

                // Simpan detail gaji ke tabel detail_gajis
                DetailGaji::create([
                    'penggajian_id' => $penggajian_id,
                    'kategori' => DetailGaji::STATUS_PENGURANG,
                    'nama_detail' => $penyesuaianGaji->nama_detail,
                    'besaran' => $penyesuaianGaji->besaran
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Pengurangan Take Home Pay dari {$penyesuaianGaji->nama_detail} berhasil dilakukan.",
                'data' => $penyesuaianGaji
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan penyesuaian gaji: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
