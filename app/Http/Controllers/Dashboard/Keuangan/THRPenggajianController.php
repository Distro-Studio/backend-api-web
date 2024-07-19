<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use App\Exports\Keuangan\THRGajiExport;
use Carbon\Carbon;
use App\Models\DetailGaji;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreRunTHRRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\DataKaryawan;

class THRPenggajianController extends Controller
{
    public function getDataKaryawan()
    {
        if (!Gate::allows('view thrKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKaryawan = DataKaryawan::where('status_karyawan', 'Tetap')->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Berhasil menampilkan seluruh karyawan yang memiliki status Tetap.',
            'data' => $dataKaryawan
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view thrKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $riwayatPenggajian = RiwayatPenggajian::query()
            ->with('penggajians')
            ->whereHas('penggajians.detail_gajis', function ($query) {
                $query->where('kategori', DetailGaji::STATUS_PENAMBAH)
                    ->where('nama_detail', 'THR')
                    ->whereNotNull('besaran');
            });

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

        $data_karyawan_ids = $request->input('data_karyawan_ids', []);
        $tglRunTHR = Carbon::parse($request->input('tgl_run_thr'));
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
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
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak ada jadwal penggajian yang tersedia.'), Response::HTTP_BAD_REQUEST);
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
                $message = 'Tanggal THR berhasil ditambahkan sesuai tanggal yang diberikan.';
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
                $query->where('kategori', DetailGaji::STATUS_PENAMBAH)
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

        try {
            return Excel::download(new THRGajiExport(), 'keuangan-thr.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data penggajian THR berhasil di download.'), Response::HTTP_OK);
    }
}
