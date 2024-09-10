<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Keuangan\THRGajiExport;
use App\Http\Requests\StoreRunTHRRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\Thr;

class THRPenggajianController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view thrKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Tentukan limit default
        $limit = $request->input('limit', 10); // Default 10 jika tidak ada atau kosong

        // $riwayatPenggajian = RiwayatPenggajian::query()
        //     ->whereHas('penggajians.detail_gajis', function ($query) {
        //         $query->where('kategori_gaji_id', 2)
        //             ->where('nama_detail', 'THR')
        //             ->whereNotNull('besaran');
        //     })->orderBy('created_at', 'desc');

        $riwayatPenggajian = RiwayatPenggajian::query()->where('jenis_riwayat', 0)->orderBy('created_at', 'desc');

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
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data riwayat penggajian karyawan yang tersedia.'), Response::HTTP_NOT_FOUND);
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
            'message' => 'Data riwayat penggajian THR berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreRunTHRRequest $request)
    {
        if (!Gate::allows('create thrKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data_karyawan_ids = DataKaryawan::where('id', '!=', 1)->pluck('id')->toArray();
        // $tglRunTHR = Carbon::parse($request->input('tgl_run_thr'));
        $tglRunTHR = Carbon::parse(RandomHelper::convertToDateString($request->input('tgl_run_thr')));
        // $tglRunTHR = Carbon::createFromFormat('d-m-Y',($request->input('tgl_run_thr')))->format('Y-m-d');
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $currentDate = Carbon::now()->timezone('Asia/Jakarta');

        // Validasi tgl_run_thr tidak boleh melewati hari ini
        if ($tglRunTHR->lessThan($currentDate->startOfDay())) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Tanggal THR hanya diperbolehkan untuk hari ini atau seterusnya.'), Response::HTTP_NOT_ACCEPTABLE);
        }

        $yearRunTHR = $tglRunTHR->year;
        $monthRunTHR = $tglRunTHR->month;

        // 1. Validasi THR tahun ini
        $thrExist = DB::table('run_thrs')
            ->whereYear('tgl_run_thr', $currentYear)
            ->exists();

        if ($thrExist) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'THR sudah dijalankan untuk tahun ini.'), Response::HTTP_NOT_ACCEPTABLE);
        }

        // 2. Validasi penggajian untuk bulan dari tgl_run_thr
        $penggajianExist = DB::table('penggajians')
            ->whereYear('tgl_penggajian', $yearRunTHR)
            ->whereMonth('tgl_penggajian', $monthRunTHR)
            ->exists();

        if ($penggajianExist) {
            // Jika sudah ada penggajian untuk bulan dari tgl_run_thr, atur tgl_run_thr ke bulan selanjutnya
            $tglRunTHR = $tglRunTHR->addMonth()->startOfMonth();
            $message = 'Penggajian sudah dilakukan untuk bulan ini. Tanggal THR diatur ke tanggal 1 bulan selanjutnya.';
        } else {
            // 3. Ambil jadwal penggajian dari tabel jadwal_penggajians
            $jadwalPenggajian = DB::table('jadwal_penggajians')
                ->select('tgl_mulai')
                ->orderBy('tgl_mulai', 'desc')
                ->first();

            if (!$jadwalPenggajian) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak ada tanggal penggajian yang tersedia.'), Response::HTTP_BAD_REQUEST);
            }

            // Konversi integer ke format tanggal tgl_mulai (ambil hanya tanggalnya)
            $tgl_mulai = $jadwalPenggajian->tgl_mulai;

            // 4. Validasi tgl_run_thr apakah melewati tgl_mulai atau tidak
            if ($tglRunTHR->day > $tgl_mulai) {
                // Jika tgl_run_thr melewati tgl_mulai, atur ke tanggal 1 bulan selanjutnya
                $tglRunTHR = $tglRunTHR->copy()->addMonth()->startOfMonth();
                $message = 'Tanggal THR telah diatur ke tanggal 1 bulan selanjutnya karena melewati tanggal penggajian.';
            } else {
                // Jika tgl_run_thr tidak melewati tgl_mulai, simpan tgl_run_thr tersebut
                $message = "Tanggal THR berhasil ditambahkan pada tanggal '{$request->input('tgl_run_thr')}'.";
            }
        }

        // Simpan data THR
        foreach ($data_karyawan_ids as $karyawanId) {
            DB::table('run_thrs')->insert([
                'data_karyawan_id' => $karyawanId,
                'tgl_run_thr' => $tglRunTHR->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function show($riwayat_penggajian_id)
    {
        if (!Gate::allows('view thrKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $riwayatPenggajian = RiwayatPenggajian::with(['penggajians' => function ($query) {
            $query->whereHas('detail_gajis', function ($query) {
                $query->where('kategori_gaji_id', 2)
                    ->where('nama_detail', 'THR')
                    ->whereNotNull('besaran');
            });
        }])
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
            'message' => "Detail data riwayat penggajian THR periode {$riwayatPenggajian->periode} berhasil ditampilkan.",
            'data' => [
                'data_riwayat' => $riwayatPenggajianData,
                'data_penggajian' => $formattedData
            ],
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function exportTHRPenggajian()
    {
        if (!Gate::allows('export thrKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataTHR = Thr::all();
        if ($dataTHR->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data penggajian THR karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new THRGajiExport(), 'penggajian-thr-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
