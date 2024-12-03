<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use Carbon\Carbon;
use App\Models\Premi;
use App\Models\Notifikasi;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Helpers\DetailGajiHelper;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\Penggajian\CreateGajiJob;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Exports\Keuangan\LaporanPenggajian\LaporanGajiBankExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiKompetensiExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiPotonganExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiPenerimaanExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiUnitExport;

class PenggajianController extends Controller
{
    public function publikasiPenggajian()
    {
        if (!Gate::allows('create penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $currentMonth = Carbon::now('Asia/Jakarta')->month;
        $currentYear = Carbon::now('Asia/Jakarta')->year;

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
            $tgl_penggajian = Carbon::parse(RandomHelper::convertToDateString($penggajian->tgl_penggajian), 'Asia/Jakarta');
            // dd($tgl_penggajian);

            // ini yg baru
            // Validasi agar publikasi dilakukan hanya dalam rentang tgl_penggajian sampai tgl_mulai
            if ($currentDate->greaterThanOrEqualTo($tgl_penggajian) && $currentDate->lessThanOrEqualTo($tgl_mulai)) {
                // Update status penggajian ke "Dipublikasikan" jika belum
                if ($penggajian->status_gaji_id != 2) {
                    DB::beginTransaction();
                    try {
                        $penggajian->status_gaji_id = 2;
                        $penggajian->save();

                        // Sum take_home_pay untuk riwayat_penggajian_id terkait
                        $totalTakeHomePay = Penggajian::where('riwayat_penggajian_id', $riwayatPenggajian->id)
                            ->where('status_gaji_id', 2)
                            ->sum('take_home_pay');

                        $riwayatPenggajian->update([
                            'status_gaji_id' => 2,
                            'submitted_by' => Auth::id(),
                            'periode_gaji_karyawan' => $totalTakeHomePay
                        ]);

                        $updatedPenggajians[] = [
                            'penggajian_id' => $penggajian->id,
                            'updated_at' => $penggajian->updated_at
                        ];
                        $riwayatPenggajians[] = [
                            'riwayat_penggajian_id' => $riwayatPenggajian->id,
                            'updated_at' => $riwayatPenggajian->updated_at
                        ];

                        DB::commit();
                        $this->createNotifikasiPenggajianPublish($penggajian, $periode);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Gagal mempublikasikan penggajian ID {$penggajian->id}, Pesan Error: " . $e->getMessage());
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
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data riwayat penggajian karyawan yang tersedia.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $datariwayatPenggajian->map(function ($riwayatPenggajian) {
            return [
                'id' => $riwayatPenggajian->id,
                'periode' => $riwayatPenggajian->periode,
                'pembaruan_terakhir' => $riwayatPenggajian->updated_at,
                'karyawan_digaji' => $riwayatPenggajian->karyawan_verifikasi,
                'status_riwayat_gaji' => $riwayatPenggajian->status_gajis,
                'created_by' => $riwayatPenggajian->created_users ? [
                    'id' => $riwayatPenggajian->created_users->id,
                    'nama' => $riwayatPenggajian->created_users->nama,
                    'email_verified_at' => $riwayatPenggajian->created_users->email_verified_at,
                    'data_karyawan_id' => $riwayatPenggajian->created_users->data_karyawan_id,
                    'foto_profil' => $riwayatPenggajian->created_users->foto_profil,
                    'data_completion_step' => $riwayatPenggajian->created_users->data_completion_step,
                    'status_aktif' => $riwayatPenggajian->created_users->status_aktif,
                    'created_at' => $riwayatPenggajian->created_users->created_at,
                    'updated_at' => $riwayatPenggajian->created_users->updated_at
                ] : null,
                'submitted_by' => $riwayatPenggajian->submitted_users ? [
                    'id' => $riwayatPenggajian->submitted_users->id,
                    'nama' => $riwayatPenggajian->submitted_users->nama,
                    'email_verified_at' => $riwayatPenggajian->submitted_users->email_verified_at,
                    'data_karyawan_id' => $riwayatPenggajian->submitted_users->data_karyawan_id,
                    'foto_profil' => $riwayatPenggajian->submitted_users->foto_profil,
                    'data_completion_step' => $riwayatPenggajian->submitted_users->data_completion_step,
                    'status_aktif' => $riwayatPenggajian->submitted_users->status_aktif,
                    'created_at' => $riwayatPenggajian->submitted_users->created_at,
                    'updated_at' => $riwayatPenggajian->submitted_users->updated_at
                ] : null,
                'periode_gaji_karyawan' => $riwayatPenggajian->periode_gaji_karyawan ?? null,
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

        $data_karyawan_ids = DataKaryawan::where('id', '!=', 1)
            ->whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
            ->whereIn('status_karyawan_id', [1, 2, 3])
            ->pluck('id')
            ->toArray();
        // dd($data_karyawan_ids);
        $sertakan_bor = $request->has('bor') && $request->bor == 1;

        // Ambil jadwal penggajian dari tabel jadwal_penggajians
        $jadwalPenggajian = DB::table('jadwal_penggajians')
            ->select('tgl_mulai')
            ->orderBy('tgl_mulai', 'desc')
            ->first();
        if (!$jadwalPenggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak ada tanggal penggajian yang tersedia.'), Response::HTTP_BAD_REQUEST);
        }

        $currentMonth = Carbon::now('Asia/Jakarta')->month;
        $currentYear = Carbon::now('Asia/Jakarta')->year;
        $tgl_mulai = Carbon::createFromFormat('Y-m-d', "$currentYear-$currentMonth-{$jadwalPenggajian->tgl_mulai}", 'Asia/Jakarta');
        $tgl_akhir = $tgl_mulai->copy()->endOfDay();
        $awalBulan = Carbon::now('Asia/Jakarta')->startOfMonth();
        $currentDateTime = Carbon::now('Asia/Jakarta');

        if ($currentDateTime->lessThan($awalBulan) || $currentDateTime->greaterThan($tgl_akhir)) {
            return response()->json(new WithoutDataResource(
                Response::HTTP_BAD_REQUEST,
                "Penggajian hanya dapat dilakukan mulai tanggal 1 hingga tanggal '{$tgl_mulai->format('d-m-Y')}' sampai jam 23:59."
            ), Response::HTTP_BAD_REQUEST);
        }

        // Validasi untuk memastikan penggajian belum dilakukan pada periode ini
        $existingRiwayat = DB::table('riwayat_penggajians')
            ->whereMonth('periode', $currentMonth)
            ->whereYear('periode', $currentYear)
            ->first();

        if ($existingRiwayat) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Penggajian sudah dilakukan pada {$existingRiwayat->created_at}."), Response::HTTP_BAD_REQUEST);
        }

        // Cek apakah THR sudah ada untuk bulan dan tahun ini
        $thrExists = DB::table('run_thrs')
            ->whereMonth('tgl_run_thr', $currentMonth)
            ->whereYear('tgl_run_thr', $currentYear)
            ->exists();

        // Tentukan nilai jenis_riwayat
        $jenisRiwayat = $thrExists ? 0 : 1; // 0 = thr, 1 = tanpa_thr

        // Ambil nilai status dari tabel status_gajis
        $statusBelumDipublikasi = DB::table('status_gajis')->where('label', 'Belum Dipublikasi')->value('id');
        $statusSudahDipublikasi = DB::table('status_gajis')->where('label', 'Sudah Dipublikasi')->value('id');

        DB::beginTransaction();
        try {
            // Hitung jumlah karyawan yang terverifikasi berdasarkan data_karyawan_ids
            $verifikasiKaryawan = count($data_karyawan_ids);

            // Buat entri baru di tabel riwayat_penggajians untuk periode bulan ini
            $periode = Carbon::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
            $status_riwayat_gaji = Carbon::now('Asia/Jakarta')->greaterThanOrEqualTo($tgl_mulai) ? $statusSudahDipublikasi : $statusBelumDipublikasi;
            $riwayatPenggajian = RiwayatPenggajian::create([
                'periode' => $periode,
                'karyawan_verifikasi' => $verifikasiKaryawan,
                'status_gaji_id' => $status_riwayat_gaji,
                'jenis_riwayat' => $jenisRiwayat,
                'created_by' => Auth::id(),
                'submitted_by' => null,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            // Dispatch the job to handle the calculation in the background
            CreateGajiJob::dispatch($data_karyawan_ids, $sertakan_bor, $riwayatPenggajian->id);

            $this->createNotifikasiPenggajian($riwayatPenggajian, $periode);

            DB::commit();
            $periodeAt = Carbon::parse($riwayatPenggajian->periode)->locale('id')->isoFormat('MMMM Y');
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Riwayat penggajian karyawan berhasil disimpan untuk periode '{$periodeAt}'."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan riwayat penggajian. Silahkan coba lagi atau hubungi SIM RS.'), Response::HTTP_INTERNAL_SERVER_ERROR);
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
            'status_riwayat_gaji' => $riwayatPenggajian->status_gajis,
            'created_by' => $riwayatPenggajian->created_users ? [
                'id' => $riwayatPenggajian->created_users->id,
                'nama' => $riwayatPenggajian->created_users->nama,
                'email_verified_at' => $riwayatPenggajian->created_users->email_verified_at,
                'data_karyawan_id' => $riwayatPenggajian->created_users->data_karyawan_id,
                'foto_profil' => $riwayatPenggajian->created_users->foto_profil,
                'data_completion_step' => $riwayatPenggajian->created_users->data_completion_step,
                'status_aktif' => $riwayatPenggajian->created_users->status_aktif,
                'created_at' => $riwayatPenggajian->created_users->created_at,
                'updated_at' => $riwayatPenggajian->created_users->updated_at
            ] : null,
            'submitted_by' => $riwayatPenggajian->submitted_users ? [
                'id' => $riwayatPenggajian->submitted_users->id,
                'nama' => $riwayatPenggajian->submitted_users->nama,
                'email_verified_at' => $riwayatPenggajian->submitted_users->email_verified_at,
                'data_karyawan_id' => $riwayatPenggajian->submitted_users->data_karyawan_id,
                'foto_profil' => $riwayatPenggajian->submitted_users->foto_profil,
                'data_completion_step' => $riwayatPenggajian->submitted_users->data_completion_step,
                'status_aktif' => $riwayatPenggajian->submitted_users->status_aktif,
                'created_at' => $riwayatPenggajian->submitted_users->created_at,
                'updated_at' => $riwayatPenggajian->submitted_users->updated_at
            ] : null,
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
                    'username' => $user->username,
                    'email_verified_at' => $user->email_verified_at,
                    'data_karyawan_id' => $user->data_karyawan_id,
                    'foto_profil' => $user->foto_profil,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'nik' => $dataKaryawan->nik,
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

    // ini v2
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

        $gaji_pokok = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Gaji Pokok');
        $tunjangan_jabatan = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Jabatan');
        $tunjangan_fungsional = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Fungsional');
        $tunjangan_khusus = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Khusus');
        $tunjangan_lainnya = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Lainnya');

        $brutoPremi = $gaji_pokok +
            $tunjangan_jabatan +
            $tunjangan_fungsional +
            $tunjangan_khusus +
            $tunjangan_lainnya;

        $dataKaryawan = $penggajian->data_karyawans;
        $user = $dataKaryawan->users;
        $unitKerja = $dataKaryawan->unit_kerjas;
        $kelompokGaji = $dataKaryawan->kelompok_gajis;
        $ptkp = $dataKaryawan->ptkps;

        // Kategori pendapatan tetap
        $pendapatanTetapList = [
            'Gaji Pokok',
            'Tunjangan Jabatan',
            'Tunjangan Fungsional',
            'Tunjangan Khusus',
            'Tunjangan Lainnya'
        ];

        $pendapatanTambahanList = [
            'Uang Lembur',
            'Uang Makan',
            'Reward BOR',
            'Reward Absensi',
            'THR'
        ];

        $potonganTetapList = array_merge(Premi::pluck('nama_premi')->toArray(), ['PPh21']);
        // $potonganTetapList = Premi::pluck('nama_premi')->toArray();

        // Filter pendapatan tetap
        $pendapatanTetap = $penggajian->detail_gajis->filter(function ($detail) use ($pendapatanTetapList) {
            return in_array($detail->nama_detail, $pendapatanTetapList);
        })->map(function ($detail) {
            return [
                'kategori_gaji' => $detail->kategori_gajis,
                'nama_detail' => $detail->nama_detail,
                'besaran' => $detail->besaran,
                'created_at' => $detail->created_at,
                'updated_at' => $detail->updated_at
            ];
        })->values();

        // Filter pendapatan tambahan (selain pendapatan tetap)
        $pendapatanTambahan = $penggajian->detail_gajis->filter(function ($detail) use ($pendapatanTambahanList, $pendapatanTetapList) {
            return (in_array($detail->nama_detail, $pendapatanTambahanList) || $detail->kategori_gaji_id == 2)
                && !in_array($detail->nama_detail, $pendapatanTetapList);
        })->map(function ($detail) {
            return [
                'kategori_gaji' => $detail->kategori_gajis,
                'nama_detail' => $detail->nama_detail,
                'besaran' => $detail->besaran,
                'created_at' => $detail->created_at,
                'updated_at' => $detail->updated_at
            ];
        })->values();

        $potonganTetap = $penggajian->detail_gajis->filter(function ($detail) use ($potonganTetapList) {
            return in_array($detail->nama_detail, $potonganTetapList);
        })->map(function ($detail) use ($dataKaryawan, $brutoPremi) {
            $keluargaTerkenaPotongan = [];
            if ($detail->nama_detail == 'BPJS Kesehatan') {
                // Ambil data keluarga yang terkena potongan 1% untuk BPJS
                $dataKeluargas = DB::table('data_keluargas')
                    ->where('data_karyawan_id', $dataKaryawan->id)
                    ->where('is_bpjs', 1)
                    ->whereIn('hubungan', ['Anak Ke-4', 'Anak Ke-5', 'Bapak', 'Ibu', 'Bapak Mertua', 'Ibu Mertua'])
                    ->where('status_hidup', 1)
                    ->whereNotNull('verifikator_1')
                    ->get();

                // Validasi bahwa semua anggota keluarga memiliki status_keluarga_id = 2
                $allVerified = $dataKeluargas->every(function ($keluarga) {
                    return $keluarga->status_keluarga_id == 2;
                });

                // Jika tidak semua anggota keluarga terverifikasi, kosongkan $dataKeluargas
                if (!$allVerified) {
                    $dataKeluargas = collect(); // Membuatnya kosong
                }

                $brutoPremi = ($brutoPremi > 12000000) ? 12000000 : $brutoPremi;
                $totalAnggota = $dataKeluargas->count() + 1; // +1 untuk karyawan sendiri (Personal)
                $besaranPerIndividu = ceil($detail->besaran / $totalAnggota);
                foreach ($dataKeluargas as $keluarga) {
                    $keluargaTerkenaPotongan[] = [
                        'hubungan' => $keluarga->hubungan,
                        'besaran' => $besaranPerIndividu
                    ];
                }

                $keluargaTerkenaPotongan[] = [
                    'hubungan' => 'Pribadi',
                    'besaran' => $besaranPerIndividu
                ];
            }

            return [
                'kategori_gaji' => $detail->kategori_gajis,
                'nama_detail' => $detail->nama_detail,
                'besaran' => $detail->besaran,
                'keluarga_terkena_potongan' => ($detail->nama_detail == 'BPJS Kesehatan') ? $keluargaTerkenaPotongan : null,
                'created_at' => $detail->created_at,
                'updated_at' => $detail->updated_at
            ];
        })->values();


        $potonganTambahan = $penggajian->detail_gajis->filter(function ($detail) use ($potonganTetapList) {
            return !in_array($detail->nama_detail, $potonganTetapList) && $detail->kategori_gaji_id == 3;
        })->map(function ($detail) {
            return [
                'kategori_gaji' => $detail->kategori_gajis,
                'nama_detail' => $detail->nama_detail,
                'besaran' => $detail->besaran,
                'created_at' => $detail->created_at,
                'updated_at' => $detail->updated_at
            ];
        })->values();

        $totalPendapatanTetap = $pendapatanTetap->sum('besaran');
        $totalPendapatanTambahan = $pendapatanTambahan->sum('besaran');
        $totalPendapatan = $totalPendapatanTetap + $totalPendapatanTambahan;

        $totalPotonganTetap = $potonganTetap->sum('besaran');
        $totalPotonganTambahan = $potonganTambahan->sum('besaran');
        $totalPotongan = $totalPotonganTetap + $totalPotonganTambahan;

        $formattedData = [
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'username' => $user->username,
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
            'pendapatan' => [
                'pendapatan_tetap' => $pendapatanTetap,
                'pendapatan_tambahan' => $pendapatanTambahan,
                'total_pendapatan' => $totalPendapatan,
            ],
            'potongan' => [
                'potongan_tetap' => $potonganTetap,
                'potongan_tambahan' => $potonganTambahan,
                'total_potongan' => $totalPotongan,
            ],
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
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data penggajian karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        $months = $request->input('months', []);
        $years = $request->input('years', []);

        try {
            return Excel::download(new RekapGajiPenerimaanExport($months, $years), 'rekap-penerimaan-gaji.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data penggajian karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function exportRekapPenerimaanGajiUnit(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPenggajian = Penggajian::all();
        if ($dataPenggajian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data penggajian karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        $months = $request->input('months', []);
        $years = $request->input('years', []);

        try {
            $sheets = new RekapGajiUnitExport($months, $years);
            // if ($sheets->sheets() === []) {
            //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data penggajian karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            // }

            return Excel::download($sheets, 'rekap-penerimaan-gaji-unit-kerja.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf, tidak ada data yang dapat diekspor atau terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }


        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data penggajian karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function exportRekapPenerimaanGajiKompetensi(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPenggajian = Penggajian::all();
        if ($dataPenggajian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data penggajian karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        $months = $request->input('months', []);
        $years = $request->input('years', []);

        try {
            $sheets = new RekapGajiKompetensiExport($months, $years);
            // if ($sheets->sheets() === []) {
            //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data penggajian karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            // }

            return Excel::download($sheets, 'rekap-penerimaan-gaji-kompetensi.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf, tidak ada data yang dapat diekspor atau terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
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
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
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
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data laporan penggajian setoran bank berhasil di download.'), Response::HTTP_OK);
    }

    private function createNotifikasiPenggajian($riwayatPenggajian, $periode)
    {
        $created_at = Carbon::parse($riwayatPenggajian->created_at)->format('d-m-Y H:i:s');
        $messageSuperAdmin = "Notifikasi untuk Super Admin: Penggajian pada periode {$periode} telah dibuat oleh '{$riwayatPenggajian->created_users->nama}' pada {$created_at}.";
        Notifikasi::create([
            'kategori_notifikasi_id' => 5,
            'user_id' => 1,
            'message' => $messageSuperAdmin,
            'is_read' => false,
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);
    }

    private function createNotifikasiPenggajianPublish($penggajian, $periode)
    {
        try {
            if ($penggajian->data_karyawans && $penggajian->data_karyawans->users) {
                $user = $penggajian->data_karyawans->users;

                $message = "Penggajian untuk periode {$periode} telah dipublikasikan. Silakan cek slip gaji Anda.";
                $messageSuperAdmin = "Notifikasi untuk Super Admin: Penggajian untuk karyawan pada periode {$periode} telah dipublikasikan.";

                $userIds = [$user->id];

                foreach ($userIds as $userId) {
                    $messageToSend = $userId === 1 ? $messageSuperAdmin : $message;
                    Notifikasi::create([
                        'kategori_notifikasi_id' => 5,
                        'user_id' => $userId,
                        'message' => $messageToSend,
                        'is_read' => false,
                        'created_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('| Penggajian | - Error function createNotifikasiPenggajianPublish: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
