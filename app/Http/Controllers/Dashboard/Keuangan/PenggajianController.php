<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use App\Exports\Keuangan\LaporanPenggajian\LaporanGajiBankExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiPenerimaanExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiPotonganExport;
use Carbon\Carbon;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\Penggajian\CreateGajiJob;
use App\Helpers\RandomHelper;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PenggajianController extends Controller
{
    // public function calculatedInfo()
    // {
    //     if (!Gate::allows('view penggajianKaryawan')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $periodeSekarang = Carbon::now()->format('Y-m');
    //     $jumlahKaryawanTetap = DataKaryawan::where('status_karyawan', 'Tetap')->count();
    //     $jumlahKaryawanKontrak = DataKaryawan::where('status_karyawan', 'Kontrak')->count();
    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => 'Perhitungan informasi tambahan penggajian karyawan.',
    //         'data' => [
    //             'periode_sekarang' => $periodeSekarang,
    //             'karyawan_tetap' => $jumlahKaryawanTetap,
    //             'karyawan_kontrak' => $jumlahKaryawanKontrak
    //         ],
    //     ], Response::HTTP_OK);
    // }

    public function publikasiPenggajian()
    {
        if (!Gate::allows('create penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Ambil jadwal penggajian dari tabel jadwal_penggajians
        $jadwalPenggajian = DB::table('jadwal_penggajians')
            ->select('tgl_mulai')
            ->orderBy('tgl_mulai', 'desc')
            ->first();

        if (!$jadwalPenggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak ada tanggal penggajian yang tersedia.'), Response::HTTP_BAD_REQUEST);
        }

        $tgl_mulai = Carbon::create($currentYear, $currentMonth, $jadwalPenggajian->tgl_mulai);
        $currentDate = Carbon::now();

        // Ambil semua penggajian untuk periode saat ini dengan status_gaji_id 1
        $penggajians = Penggajian::whereMonth('tgl_penggajian', $currentMonth)
            ->whereYear('tgl_penggajian', $currentYear)
            ->where('status_gaji_id', 1)
            ->get();

        if ($penggajians->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penggajian yang perlu dipublikasikan.'), Response::HTTP_OK);
        }

        $updatedPenggajians = [];
        $riwayatPenggajians = [];
        $periode = $currentDate->locale('id')->isoFormat('MMMM Y');

        foreach ($penggajians as $penggajian) {
            $riwayatPenggajian = $penggajian->riwayat_penggajians;
            $tgl_penggajian = Carbon::parse(RandomHelper::convertToDateString($penggajian->tgl_penggajian));
            // Cek apakah tgl_penggajian sudah terlewat dari tgl_mulai
            // if ($tgl_penggajian->lessThan($tgl_mulai)) {
            //     if ($penggajian->status_gaji_id == 2) {
            //         $updatedPenggajians[] = [
            //             'penggajian_id' => $penggajian->id,
            //             'updated_at' => $penggajian->updated_at
            //         ];
            //     } else {
            //         $penggajian->status_gaji_id = 2;
            //         $penggajian->save();
            //         $updatedPenggajians[] = [
            //             'penggajian_id' => $penggajian->id,
            //             'updated_at' => $penggajian->updated_at
            //         ];
            //         if ($riwayatPenggajian->status_gaji_id != 2) {
            //             $riwayatPenggajian->status_gaji_id = 2;
            //             $riwayatPenggajian->save();
            //             $riwayatPenggajians[] = [
            //                 'riwayat_penggajian_id' => $riwayatPenggajian->id,
            //                 'updated_at' => $riwayatPenggajian->updated_at
            //             ];
            //         }
            //     }
            // } else {
            //     $penggajian->status_gaji_id = 2;
            //     $penggajian->save();
            //     $updatedPenggajians[] = [
            //         'penggajian_id' => $penggajian->id,
            //         'updated_at' => $penggajian->updated_at
            //     ];
            //     if ($riwayatPenggajian->status_gaji_id != 2) {
            //         $riwayatPenggajian->status_gaji_id = 2;
            //         $riwayatPenggajian->save();
            //         $riwayatPenggajians[] = [
            //             'riwayat_penggajian_id' => $riwayatPenggajian->id,
            //             'updated_at' => $riwayatPenggajian->updated_at
            //         ];
            //     }
            // }

            // ini yg baru
            // Validasi agar publikasi dilakukan hanya dalam rentang tgl_penggajian sampai tgl_mulai
            if ($currentDate->greaterThanOrEqualTo($tgl_penggajian) && $currentDate->lessThanOrEqualTo($tgl_mulai)) {
                // Update status penggajian ke "Dipublikasikan" jika belum
                if ($penggajian->status_gaji_id != 2) {
                    $penggajian->status_gaji_id = 2;
                    $penggajian->save();
                    $updatedPenggajians[] = [
                        'penggajian_id' => $penggajian->id,
                        'updated_at' => $penggajian->updated_at
                    ];
                    if ($riwayatPenggajian->status_gaji_id != 2) {
                        $riwayatPenggajian->status_gaji_id = 2;
                        $riwayatPenggajian->save();
                        $riwayatPenggajians[] = [
                            'riwayat_penggajian_id' => $riwayatPenggajian->id,
                            'updated_at' => $riwayatPenggajian->updated_at
                        ];
                    }
                }
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Publikasi penggajian hanya dapat dilakukan dari tanggal '{$tgl_penggajian->format('Y-m-d')}' hingga tanggal '{$tgl_mulai->format('Y-m-d')}'."), Response::HTTP_BAD_REQUEST);
            }
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, "Data penggajian untuk periode '{$periode}' telah dipublikasikan."), Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Tentukan limit default
        $limit = $request->input('limit', 10); // Default 10 jika tidak ada atau kosong

        $riwayatPenggajian = RiwayatPenggajian::query()->orderBy('created_at', 'desc');

        // Filter periode tahun jika ada
        if ($request->has('periode_tahun')) {
            $periode_tahun = $request->input('periode_tahun');
            if (is_array($periode_tahun)) {
                $riwayatPenggajian->whereIn(DB::raw('YEAR(periode)'), $periode_tahun);
            } else {
                $riwayatPenggajian->whereYear('periode', $periode_tahun);
            }
        }

        if ($limit == 0) {
            $datariwayatPenggajian = $riwayatPenggajian->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $datariwayatPenggajian = $riwayatPenggajian->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $datariwayatPenggajian->url(1),
                    'last' => $datariwayatPenggajian->url($datariwayatPenggajian->lastPage()),
                    'prev' => $datariwayatPenggajian->previousPageUrl(),
                    'next' => $datariwayatPenggajian->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $datariwayatPenggajian->currentPage(),
                    'last_page' => $datariwayatPenggajian->lastPage(),
                    'per_page' => $datariwayatPenggajian->perPage(),
                    'total' => $datariwayatPenggajian->total(),
                ]
            ];
        }
        if ($datariwayatPenggajian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data riwayat penggajian karyawan yang tersedia.'), Response::HTTP_OK);
        }

        $formattedData = $datariwayatPenggajian->map(function ($riwayatPenggajian) {
            return [
                'id' => $riwayatPenggajian->id,
                'periode' => $riwayatPenggajian->periode,
                'pembaruan_terakhir' => $riwayatPenggajian->updated_at,
                'karyawan_digaji' => $riwayatPenggajian->karyawan_verifikasi,
                'status_riwayat_gaji' => $riwayatPenggajian->status_gajis,
                'created_at' => $riwayatPenggajian->created_at,
                'updated_at' => $riwayatPenggajian->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data riwayat penggajian berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('create penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data_karyawan_ids = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')->pluck('id')->toArray();
        $sertakan_bor = $request->has('sertakan_bor') && $request->sertakan_bor == 1;

        // Ambil jadwal penggajian dari tabel jadwal_penggajians
        $jadwalPenggajian = DB::table('jadwal_penggajians')
            ->select('tgl_mulai')
            ->orderBy('tgl_mulai', 'desc')
            ->first();
        if (!$jadwalPenggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak ada jadwal penggajian yang tersedia.'), Response::HTTP_BAD_REQUEST);
        }

        // Konversi integer ke format tanggal tgl_mulai
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $tgl_mulai = Carbon::create($currentYear, $currentMonth, $jadwalPenggajian->tgl_mulai);
        $awalBulan = Carbon::now()->startOfMonth();

        if (Carbon::now()->lessThan($awalBulan) || Carbon::now()->greaterThan($tgl_mulai)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tanggal penggajian hanya dapat dilakukan mulai tanggal 1 hingga tanggal '{$tgl_mulai->format('Y-m-d')}'."), Response::HTTP_BAD_REQUEST);
        }

        // Validasi untuk memastikan penggajian belum dilakukan pada periode ini
        $existingRiwayat = DB::table('riwayat_penggajians')
            ->whereMonth('periode', $currentMonth)
            ->whereYear('periode', $currentYear)
            ->first();

        if ($existingRiwayat) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Penggajian sudah dilakukan pada {$existingRiwayat->created_at}."), Response::HTTP_BAD_REQUEST);
        }

        // Ambil nilai status dari tabel status_gajis
        $statusBelumDipublikasi = DB::table('status_gajis')->where('label', 'Belum Dipublikasi')->value('id');
        $statusSudahDipublikasi = DB::table('status_gajis')->where('label', 'Sudah Dipublikasi')->value('id');

        DB::beginTransaction();
        try {
            // Hitung jumlah karyawan yang terverifikasi berdasarkan data_karyawan_ids
            $verifikasiKaryawan = count($data_karyawan_ids);

            // Buat entri baru di tabel riwayat_penggajians untuk periode bulan ini
            $periode = Carbon::now()->startOfMonth()->format('Y-m-d');
            $status_riwayat_gaji = Carbon::now()->greaterThanOrEqualTo($tgl_mulai) ? $statusSudahDipublikasi : $statusBelumDipublikasi;
            $riwayatPenggajian = RiwayatPenggajian::create([
                'periode' => $periode,
                'karyawan_verifikasi' => $verifikasiKaryawan,
                'status_gaji_id' => $status_riwayat_gaji,
            ]);

            // Dispatch the job to handle the calculation in the background
            CreateGajiJob::dispatch($data_karyawan_ids, $sertakan_bor, $riwayatPenggajian->id);

            DB::commit();
            $periodeAt = Carbon::parse($riwayatPenggajian->periode)->locale('id')->isoFormat('MMMM Y');
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Riwayat penggajian karyawan berhasil disimpan untuk periode '{$periodeAt}'.",
                'data' => $riwayatPenggajian
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan riwayat penggajian: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($riwayat_penggajian_id)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $riwayatPenggajian = RiwayatPenggajian::with([
            'penggajians.data_karyawans.users',
            'penggajians.data_karyawans.unit_kerjas',
            'penggajians.data_karyawans.kelompok_gajis'
        ])
            ->where('id', $riwayat_penggajian_id)
            ->first();

        if (!$riwayatPenggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data riwayat penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $riwayatPenggajianData = [
            'id' => $riwayatPenggajian->id,
            'periode' => $riwayatPenggajian->periode,
            'pembaruan_terakhir' => $riwayatPenggajian->updated_at,
            'karyawan_digaji' => $riwayatPenggajian->karyawan_verifikasi,
            'status_riwayat_gaji' => $riwayatPenggajian->status_gajis
        ];

        $penggajians = $riwayatPenggajian->penggajians;

        $formattedData = $penggajians->map(function ($penggajian) {
            $dataKaryawan = $penggajian->data_karyawans;
            $user = $dataKaryawan->users;
            $unitKerja = $dataKaryawan->unit_kerjas;
            $kelompokGaji = $dataKaryawan->kelompok_gajis;

            return [
                'id' => $penggajian->id,
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
                'unit_kerja' => $unitKerja,
                'kelompok_gaji' => $kelompokGaji,
                'gaji_bruto' => $penggajian->gaji_bruto,
                'total_tunjangan' => $penggajian->total_tunjangan,
                'total_premi' => $penggajian->total_premi,
                'pph_21' => $penggajian->pph_21,
                'reward' => $penggajian->reward,
                'take_home_pay' => $penggajian->take_home_pay,
                'status_penggajian' => $penggajian->status_gajis,
                'created_at' => $penggajian->created_at,
                'updated_at' => $penggajian->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail data riwayat penggajian periode '{$riwayatPenggajian->periode}' berhasil ditampilkan.",
            'data' => [
                'data_riwayat' => $riwayatPenggajianData,
                'data_penggajian' => $formattedData
            ]
        ], Response::HTTP_OK);
    }

    public function showDetailGajiUser($penggajian_id)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $penggajian = Penggajian::with([
            'detail_gajis',
            'data_karyawans.users',
            'data_karyawans.unit_kerjas',
            'data_karyawans.kelompok_gajis',
            'data_karyawans.ptkps'
        ])
            ->where('id', $penggajian_id)
            ->first();

        if (!$penggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $dataKaryawan = $penggajian->data_karyawans;
        $user = $dataKaryawan->users;
        $unitKerja = $dataKaryawan->unit_kerjas;
        $kelompokGaji = $dataKaryawan->kelompok_gajis;
        $ptkp = $dataKaryawan->ptkps;

        $detailGajis = $penggajian->detail_gajis->map(function ($detail) {
            return [
                'kategori_gaji' => $detail->kategori_gajis,
                'nama_detail' => $detail->nama_detail,
                'besaran' => $detail->besaran,
                'created_at' => $detail->created_at,
                'updated_at' => $detail->updated_at
            ];
        });

        $formattedData = [
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
            'unit_kerja' => $unitKerja,
            'kelompok_gaji' => $kelompokGaji,
            'ptkp' => $ptkp,
            'detail_gaji' => $detailGajis,
            'take_home_pay' => $penggajian->take_home_pay,
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail gaji karyawan '{$user->nama}' berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function exportRekapPenerimaanGaji(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPenggajian = Penggajian::all();
        if ($dataPenggajian->isEmpty()) {
            // Kembalikan respons JSON ketika tabel kosong
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penggajian karyawan yang tersedia untuk diekspor.'), Response::HTTP_OK);
        }

        $months = $request->input('months', []);
        $years = $request->input('years', []);

        try {
            return Excel::download(new RekapGajiPenerimaanExport($months, $years), 'rekap-penerimaan-gaji.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data penggajian karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function exportRekapPotonganGaji(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPenggajian = Penggajian::all();
        if ($dataPenggajian->isEmpty()) {
            // Kembalikan respons JSON ketika tabel kosong
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penggajian karyawan yang tersedia untuk diekspor.'), Response::HTTP_OK);
        }

        $months = $request->input('months', []);
        $years = $request->input('years', []);

        try {
            return Excel::download(new RekapGajiPotonganExport($months, $years), 'rekap-potongan-gaji.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data rekap potongan gaji karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function exportLaporanGajiBank(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPenggajian = Penggajian::all();
        if ($dataPenggajian->isEmpty()) {
            // Kembalikan respons JSON ketika tabel kosong
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penggajian karyawan yang tersedia untuk diekspor.'), Response::HTTP_OK);
        }

        $months = $request->input('months', []);
        $years = $request->input('years', []);

        try {
            return Excel::download(new LaporanGajiBankExport($months, $years), 'laporan-penggajian-bank.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data laporan penggajian setoran bank berhasil di download.'), Response::HTTP_OK);
    }
}
