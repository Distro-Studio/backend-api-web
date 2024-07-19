<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use App\Exports\Keuangan\LaporanPenggajian\LaporanGajiBankExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiPenerimaanExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiPotonganExport;
use Carbon\Carbon;
use App\Models\Ptkp;
use App\Models\DetailGaji;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\Penggajian\CreateGajiJob;
use App\Exports\Keuangan\PenggajianExport;
use App\Imports\Keuangan\PenggajianImport;
use App\Exports\Keuangan\RiwayatGajiExport;
use App\Http\Requests\StorePenggajianRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Requests\Excel_Import\ImportKeuanganPenggajianRequest;
use App\Http\Requests\StorePenyesuaianGajiRequest;
use App\Models\PenyesuaianGaji;

class PenggajianController extends Controller
{
    public function calculatedInfo()
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $periodeSekarang = Carbon::now()->format('Y-m');
        $jumlahKaryawanTetap = DataKaryawan::where('status_karyawan', 'Tetap')->count();
        $jumlahKaryawanKontrak = DataKaryawan::where('status_karyawan', 'Kontrak')->count();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Perhitungan informasi tambahan penggajian karyawan.',
            'data' => [
                'periode_sekarang' => $periodeSekarang,
                'karyawan_tetap' => $jumlahKaryawanTetap,
                'karyawan_kontrak' => $jumlahKaryawanKontrak
            ],
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $riwayatPenggajian = RiwayatPenggajian::query()->with('penggajians');

        // Filter
        if ($request->has('periode_tahun')) {
            $periode_tahun = $request->periode_tahun;
            $riwayatPenggajian->whereYear('periode', $periode_tahun);
        }

        $datariwayatPenggajian = $riwayatPenggajian->paginate(10);
        if ($datariwayatPenggajian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data riwayat penggajian karyawan yang tersedia.'), Response::HTTP_OK);
        }

        $formattedData = $datariwayatPenggajian->items();
        $formattedData = array_map(function ($riwayatGaji) {
            return [
                'id' => $riwayatGaji->id,
                'periode' => $riwayatGaji->periode,
                'update_terakhir' => $riwayatGaji->updated_at,
                'karyawan_verifikasi' => $riwayatGaji->karyawan_verifikasi,
                'status_riwayat_gaji' => $riwayatGaji->status_riwayat_gaji,
                'created_at' => $riwayatGaji->created_at,
                'updated_at' => $riwayatGaji->updated_at
            ];
        }, $formattedData);

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

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data riwayat penggajian berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StorePenggajianRequest $request)
    {
        if (!Gate::allows('create penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data_karyawan_ids = $request->input('data_karyawan_ids', []);
        $sertakan_bor = $request->has('sertakan_bor') && $request->sertakan_bor == 0;

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
        $endOfMonth = Carbon::now()->endOfMonth();

        if (Carbon::now()->lessThan($tgl_mulai) || Carbon::now()->greaterThan($endOfMonth)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Tanggal penggajian hanya dapat dilakukan mulai tanggal {$tgl_mulai->format('Y-m-d')} hingga akhir bulan."), Response::HTTP_BAD_REQUEST);
        }

        // Validasi untuk memastikan penggajian belum dilakukan pada periode ini
        $existingRiwayat = DB::table('riwayat_penggajians')
            ->whereMonth('periode', $currentMonth)
            ->whereYear('periode', $currentYear)
            ->first();

        if ($existingRiwayat) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Penggajian sudah dilakukan pada {$existingRiwayat->created_at}."), Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            // Hitung jumlah karyawan yang terverifikasi berdasarkan data_karyawan_ids
            $verifikasiKaryawan = count($data_karyawan_ids);

            // Buat entri baru di tabel riwayat_penggajians untuk periode bulan ini
            $periode = Carbon::now()->startOfMonth()->format('Y-m-d');
            $status_riwayat_gaji = Carbon::now()->greaterThanOrEqualTo($tgl_mulai) ? RiwayatPenggajian::STATUS_PUBLISHED : RiwayatPenggajian::STATUS_CREATED;
            $riwayatPenggajian = RiwayatPenggajian::create([
                'periode' => $periode,
                'karyawan_verifikasi' => $verifikasiKaryawan,
                'status_riwayat_gaji' => $status_riwayat_gaji,
            ]);

            // Dispatch the job to handle the calculation in the background
            CreateGajiJob::dispatch($data_karyawan_ids, $sertakan_bor, $riwayatPenggajian->id);

            DB::commit();
            $periodeAt = Carbon::parse($riwayatPenggajian->periode)->locale('id')->isoFormat('MMMM Y');
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Riwayat penggajian karyawan berhasil disimpan untuk periode {$periodeAt}.",
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

        $riwayatPenggajian = RiwayatPenggajian::with('penggajians')
            ->where('id', $riwayat_penggajian_id)
            ->first();

        if (!$riwayatPenggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data riwayat penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $riwayatPenggajianData = [
            'id' => $riwayatPenggajian->id,
            'periode' => $riwayatPenggajian->periode,
            'karyawan_verifikasi' => $riwayatPenggajian->karyawan_verifikasi,
            'status_riwayat_gaji' => $riwayatPenggajian->status_riwayat_gaji,
            'update_terakhir' => $riwayatPenggajian->updated_at,
        ];

        $penggajians = $riwayatPenggajian->penggajians()->paginate(10);

        $formattedData = $penggajians->map(function ($penggajian) {
            $dataKaryawan = DB::table('data_karyawans')->where('id', $penggajian->data_karyawan_id)->first();
            $unitKerja = DB::table('unit_kerjas')->where('id', $dataKaryawan->unit_kerja_id)->first();
            $kelompokGaji = DB::table('kelompok_gajis')->where('id', $dataKaryawan->kelompok_gaji_id)->first();
            $user = DB::table('users')->where('id', $dataKaryawan->user_id)->select('id', 'nama', 'username', 'role_id', 'foto_profil', 'status_akun', 'data_completion_step', 'created_at', 'updated_at')->first();

            return [
                'id' => $penggajian->id,
                'user' => $user,
                'unit_kerja' => $unitKerja,
                'kelompok_gaji' => $kelompokGaji,
                'gaji_bruto' => $penggajian->gaji_bruto,
                'total_tunjangan' => $penggajian->total_tunjangan,
                'total_premi' => $penggajian->total_premi,
                'pph_21' => $penggajian->pph_21,
                'reward' => $penggajian->reward,
                'take_home_pay' => $penggajian->take_home_pay,
                'status_penggajian' => $penggajian->status_penggajian,
            ];
        });

        $paginationData = [
            'links' => [
                'first' => $penggajians->url(1),
                'last' => $penggajians->url($penggajians->lastPage()),
                'prev' => $penggajians->previousPageUrl(),
                'next' => $penggajians->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $penggajians->currentPage(),
                'last_page' => $penggajians->lastPage(),
                'per_page' => $penggajians->perPage(),
                'total' => $penggajians->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail data riwayat penggajian periode {$riwayatPenggajian->periode} berhasil ditampilkan.",
            'data' => [
                'data_riwayat' => $riwayatPenggajianData,
                'data_penggajian' => $formattedData
            ],
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function showDetailGajiUser($penggajian_id)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $penggajians = Penggajian::with('detail_gajis')
            ->where('id', $penggajian_id)
            ->first();

        if (!$penggajians) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $details = DetailGaji::where('penggajian_id', $penggajian_id)->get();

        $dataKaryawan = DB::table('data_karyawans')->where('id', $penggajians->data_karyawan_id)->first();
        $unitKerja = DB::table('unit_kerjas')->where('id', $dataKaryawan->unit_kerja_id)->first();
        $kelompokGaji = DB::table('kelompok_gajis')->where('id', $dataKaryawan->kelompok_gaji_id)->first();
        $user = DB::table('users')->where('id', $dataKaryawan->user_id)->select('id', 'nama', 'username', 'role_id', 'foto_profil', 'status_akun', 'data_completion_step', 'created_at', 'updated_at')->first();
        $ptkp = DB::table('ptkps')->where('id', $dataKaryawan->ptkp_id)->first();

        $detailGajis = $details->map(function ($detail) {
            return [
                'kategori' => $detail->kategori,
                'nama_detail' => $detail->nama_detail,
                'besaran' => $detail->besaran,
            ];
        });

        $formattedData = [
            'user' => $user,
            'unit_kerja' => $unitKerja,
            'kelompok_gaji' => $kelompokGaji,
            'ptkp' => $ptkp,
            'detail_gaji' => $detailGajis,
            'take_home_pay' => $penggajians->take_home_pay,
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail gaji karyawan {$user->nama} berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function exportRiwayatPenggajian(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        // Ambil parameter months dan year dari request
        $months = $request->input('months', []);
        $year = $request->input('year');

        // Validasi tahun
        if (!$year || $year > Carbon::now()->year) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tahun penggajian tidak valid, silahkan gunakan tahun saat ini atau lebih kecil.'), Response::HTTP_BAD_REQUEST);
        }

        // Pastikan months adalah array
        if (!is_array($months)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Bulan penggajian harus berupa array.'), Response::HTTP_BAD_REQUEST);
        }

        try {
            return Excel::download(new RiwayatGajiExport($months, $year), 'keuangan-riwayat-penggajian.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data penggajian karyawan berhasil di download.'), Response::HTTP_OK);
    }

    // by unit kerja
    public function exportRekapPenerimaanGaji(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja_ids = $request->input('unit_kerja_id', []);

        try {
            return Excel::download(new RekapGajiPenerimaanExport($unit_kerja_ids), 'rekap-penerimaan-gaji.xls');
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

        $unit_kerja_ids = $request->input('unit_kerja_id', []);

        try {
            return Excel::download(new RekapGajiPotonganExport($unit_kerja_ids), 'rekap-potongan-gaji.xls');
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

        try {
            return Excel::download(new LaporanGajiBankExport(), 'laporan-penggajian-bank.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data laporan penggajian setoran bank berhasil di download.'), Response::HTTP_OK);
    }
}
