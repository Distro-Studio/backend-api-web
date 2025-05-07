<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use ZipArchive;
use Carbon\Carbon;
use App\Models\Premi;
use App\Models\Notifikasi;
use App\Models\Penggajian;
use Illuminate\Support\Str;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\DetailGajiHelper;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\Penggajian\CreateGajiJob;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiUnitExport;
use App\Exports\Keuangan\LaporanPenggajian\LaporanGajiBankExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiPotonganExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiKompetensiExport;
use App\Exports\Keuangan\LaporanPenggajian\RekapGajiPenerimaanExport;
use App\Models\RiwayatThr;

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
        $currentDate = Carbon::now('Asia/Jakarta');

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
                            'submitted_by' => auth()->id(),
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

        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = $datariwayatPenggajian->map(function ($riwayatPenggajian) use ($baseUrl) {
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
                    'foto_profil' => $riwayatPenggajian->created_users->foto_profiles ? [
                        'id' => $riwayatPenggajian->created_users->foto_profiles->id,
                        'user_id' => $riwayatPenggajian->created_users->foto_profiles->user_id,
                        'file_id' => $riwayatPenggajian->created_users->foto_profiles->file_id,
                        'nama' => $riwayatPenggajian->created_users->foto_profiles->nama,
                        'nama_file' => $riwayatPenggajian->created_users->foto_profiles->nama_file,
                        'path' => $baseUrl . $riwayatPenggajian->created_users->foto_profiles->path,
                        'ext' => $riwayatPenggajian->created_users->foto_profiles->ext,
                        'size' => $riwayatPenggajian->created_users->foto_profiles->size,
                    ] : null,
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
                    'foto_profil' => $riwayatPenggajian->submitted_users->foto_profiles ? [
                        'id' => $riwayatPenggajian->submitted_users->foto_profiles->id,
                        'user_id' => $riwayatPenggajian->submitted_users->foto_profiles->user_id,
                        'file_id' => $riwayatPenggajian->submitted_users->foto_profiles->file_id,
                        'nama' => $riwayatPenggajian->submitted_users->foto_profiles->nama,
                        'nama_file' => $riwayatPenggajian->submitted_users->foto_profiles->nama_file,
                        'path' => $baseUrl . $riwayatPenggajian->submitted_users->foto_profiles->path,
                        'ext' => $riwayatPenggajian->submitted_users->foto_profiles->ext,
                        'size' => $riwayatPenggajian->submitted_users->foto_profiles->size,
                    ] : null,
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
            ->whereHas('status_karyawans', function ($query) {
                $query->where('kategori_status_id', 1); // Kategori status "Fulltime"
            })
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
                'created_by' => auth()->id(),
                'submitted_by' => null,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            // Jika jenisRiwayat == 0, berarti ada THR, buat riwayat THR
            if ($jenisRiwayat == 0) {
                $riwayatThr = RiwayatThr::where('periode', $periode)->first();

                if ($riwayatThr) {
                    $riwayatThr->update([
                        'riwayat_penggajian_id' => $riwayatPenggajian->id,
                        'updated_by' => auth()->id(),
                        'updated_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            }

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
            Log::error('| Penggajian | - Error function store: ' . $e->getMessage());
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

        $baseUrl = env('STORAGE_SERVER_DOMAIN');
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
                'foto_profil' => $riwayatPenggajian->created_users->foto_profiles ? [
                    'id' => $riwayatPenggajian->created_users->foto_profiles->id,
                    'user_id' => $riwayatPenggajian->created_users->foto_profiles->user_id,
                    'file_id' => $riwayatPenggajian->created_users->foto_profiles->file_id,
                    'nama' => $riwayatPenggajian->created_users->foto_profiles->nama,
                    'nama_file' => $riwayatPenggajian->created_users->foto_profiles->nama_file,
                    'path' => $baseUrl . $riwayatPenggajian->created_users->foto_profiles->path,
                    'ext' => $riwayatPenggajian->created_users->foto_profiles->ext,
                    'size' => $riwayatPenggajian->created_users->foto_profiles->size,
                ] : null,
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
                'foto_profil' => $riwayatPenggajian->submitted_users->foto_profiles ? [
                    'id' => $riwayatPenggajian->submitted_users->foto_profiles->id,
                    'user_id' => $riwayatPenggajian->submitted_users->foto_profiles->user_id,
                    'file_id' => $riwayatPenggajian->submitted_users->foto_profiles->file_id,
                    'nama' => $riwayatPenggajian->submitted_users->foto_profiles->nama,
                    'nama_file' => $riwayatPenggajian->submitted_users->foto_profiles->nama_file,
                    'path' => $baseUrl . $riwayatPenggajian->submitted_users->foto_profiles->path,
                    'ext' => $riwayatPenggajian->submitted_users->foto_profiles->ext,
                    'size' => $riwayatPenggajian->submitted_users->foto_profiles->size,
                ] : null,
                'data_completion_step' => $riwayatPenggajian->submitted_users->data_completion_step,
                'status_aktif' => $riwayatPenggajian->submitted_users->status_aktif,
                'created_at' => $riwayatPenggajian->submitted_users->created_at,
                'updated_at' => $riwayatPenggajian->submitted_users->updated_at
            ] : null,
        ];

        $penggajians = $riwayatPenggajian->penggajians;
        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = $penggajians->map(function ($penggajian) use ($baseUrl) {
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
                    'foto_profil' => $user->foto_profiles ? [
                        'id' => $user->foto_profiles->id,
                        'user_id' => $user->foto_profiles->user_id,
                        'file_id' => $user->foto_profiles->file_id,
                        'nama' => $user->foto_profiles->nama,
                        'nama_file' => $user->foto_profiles->nama_file,
                        'path' => $baseUrl . $user->foto_profiles->path,
                        'ext' => $user->foto_profiles->ext,
                        'size' => $user->foto_profiles->size,
                    ] : null,
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

        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = [
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'username' => $user->username,
                'email_verified_at' => $user->email_verified_at,
                'data_karyawan_id' => $user->data_karyawan_id,
                'foto_profil' => $user->foto_profiles ? [
                    'id' => $user->foto_profiles->id,
                    'user_id' => $user->foto_profiles->user_id,
                    'file_id' => $user->foto_profiles->file_id,
                    'nama' => $user->foto_profiles->nama,
                    'nama_file' => $user->foto_profiles->nama_file,
                    'path' => $baseUrl . $user->foto_profiles->path,
                    'ext' => $user->foto_profiles->ext,
                    'size' => $user->foto_profiles->size,
                ] : null,
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

    public function exportRekapPenerimaanGaji(Request $request) // karyawan
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
            // **Generate Excel**
            $excelPath = $this->generateExcelPenerimaanGaji($months, $years);

            // **Generate PDF dari Sheet lainnya**
            $pdfPathOtherSheet = $this->generatePDFPenerimaanGajiSheets($months, $years);

            // **Generate PDF**
            $pdfPathSheet_1 = $this->generatePDFPenerimaanGaji($months, $years);

            // **Gabungkan semua file**
            $allFiles = array_merge([$excelPath, $pdfPathSheet_1], $pdfPathOtherSheet);

            // **Create ZIP**
            $zipPath = $this->generateZIPPenerimaanGaji($allFiles);

            // Return ZIP for download
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error.'), Response::HTTP_NOT_ACCEPTABLE);
        } finally {
            foreach ($allFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data penggajian karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function exportRekapPenerimaanGajiUnit(Request $request) // unit kerja
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
            // **Generate Excel**
            $excelPath = $this->generateExcelPenerimaanGajiUnit($months, $years);

            // **Generate PDF**
            $pdfPathSheet_1 = $this->generatePDFPenerimaanGajiUnit($months, $years);

            // **Generate PDF dari Sheet penambah**
            $pdfPathSheet_2 = $this->generatePDFPenerimaanGajiUnitPenambah($months, $years);

            // **Generate PDF dari Sheet pengurang**
            $pdfPathSheet_3 = $this->generatePDFPenerimaanGajiUnitPengurang($months, $years);

            // **Gabungkan semua file**
            // $allFiles = array_merge([$excelPath, $pdfPathSheet_1], $pdfPathSheet_2, $pdfPathSheet_3);
            $allFiles = array_merge(
                [$excelPath, $pdfPathSheet_1],
                is_array($pdfPathSheet_2) ? $pdfPathSheet_2 : [$pdfPathSheet_2],
                is_array($pdfPathSheet_3) ? $pdfPathSheet_3 : [$pdfPathSheet_3]
            );

            // **Create ZIP**
            $zipPath = $this->generateZIPPenerimaanGajiUnit($allFiles);

            // Return ZIP for download
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error.' . ' Line: ' . $e->getLine()), Response::HTTP_NOT_ACCEPTABLE);
        } finally {
            foreach ($allFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function exportRekapPenerimaanGajiKompetensi(Request $request) // profesi / kompetensi
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
            // **Generate Excel**
            $excelPath = $this->generateExcelPenerimaanGajiProfesi($months, $years);

            // **Generate PDF dari Sheet lainnya**
            $pdfPathOtherSheet = $this->generatePDFPenerimaanGajiProfesiSheets($months, $years);

            // **Generate PDF**
            $pdfPathSheet_1 = $this->generatePDFPenerimaanGajiProfesi($months, $years);

            // **Gabungkan semua file**
            $allFiles = array_merge([$excelPath, $pdfPathSheet_1], $pdfPathOtherSheet);

            // **Create ZIP**
            $zipPath = $this->generateZIPPenerimaanGajiProfesi($allFiles);

            // Return ZIP for download
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf, tidak ada data yang dapat diekspor atau terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } finally {
            foreach ($allFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
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
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error.'), Response::HTTP_NOT_ACCEPTABLE);
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
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error.'), Response::HTTP_NOT_ACCEPTABLE);
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

    // ** Export Rekap Penerimaan gaji per karyawan **
    private function generateExcelPenerimaanGaji(array $months, array $years)
    {
        $fileName = 'rekap-penerimaan-gaji-karyawan.xls';
        $filePath = storage_path('app/public/' . $fileName);

        Excel::store(new RekapGajiPenerimaanExport($months, $years), 'public/' . $fileName);

        return $filePath;
    }

    private function generatePDFPenerimaanGaji(array $months, array $years)
    {
        $data = (new RekapGajiPenerimaanExport($months, $years))->sheets()[0]->collection();

        $filteredData = $data->filter(function ($row) {
            return $row['no'] !== 'Total'; // filter
        });

        $dataChunks = $filteredData->chunk(20); // Maksimal data per halaman

        $totals = $filteredData->reduce(function ($carry, $item) {
            foreach ($item as $key => $value) {
                if (is_numeric($value)) {
                    $carry[$key] = ($carry[$key] ?? 0) + $value;
                }
            }
            return $carry;
        }, []);

        $pdf = Pdf::loadView('gajis.PenerimaanKaryawan', [
            'dataChunks' => $dataChunks,
            'months' => $months,
            'years' => $years,
            'totals' => $totals,
        ])->setPaper('F4', 'landscape');

        $fileName = 'rekap-penerimaan-gaji-karyawan.pdf';
        $filePath = storage_path('app/public/' . $fileName);

        Storage::put('public/' . $fileName, $pdf->output());

        return $filePath;
    }

    private function generatePDFPenerimaanGajiSheets(array $months, array $years)
    {
        $export = new RekapGajiPenerimaanExport($months, $years);
        $sheets = $export->sheets();

        $pdfPaths = [];

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        foreach ($sheets as $index => $sheet) {
            if ($index === 0) {
                continue; // Skip sheet pertama (all karyawan)
            }

            // Ambil data dari sheet
            $data = $sheet->collection();

            $filteredData = $data->filter(function ($row) {
                return isset($row['no']) && $row['no'] !== 'Total';
            });

            $dataChunks = $filteredData->chunk(20);

            $totals = $filteredData->reduce(function ($carry, $item) {
                foreach ($item as $key => $value) {
                    if (is_numeric($value)) {
                        $carry[$key] = ($carry[$key] ?? 0) + $value;
                    }
                }
                return $carry;
            }, []);

            $title = $sheet->title();
            $unitKerjaNama = explode(' - ', $title)[0];

            $pdf = Pdf::loadView('gajis.PenerimaanKaryawan', [
                'dataChunks' => $dataChunks,
                'months' => $months,
                'years' => $years,
                'totals' => $totals,
                'nama_unit' => $unitKerjaNama,
            ])->setPaper('F4', 'landscape');

            // Pastikan nama file tidak memiliki karakter ilegal
            $safeTitle = Str::slug($sheet->title(), '-');
            $fileName = 'rekap-penerimaan-gaji-karyawan-' . $safeTitle . '.pdf';
            $filePath = storage_path('app/public/' . $fileName);

            Storage::put('public/' . $fileName, $pdf->output());

            $pdfPaths[] = $filePath;
        }

        return $pdfPaths;
    }

    private function generateZIPPenerimaanGaji(array $files)
    {
        $zipFileName = 'rekap-penerimaan-gaji-karyawan.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        return $zipPath;
    }
    // ** Export Rekap Penerimaan gaji per karyawan **

    // ** Export Rekap Penerimaan gaji per unit kerja **
    private function generateExcelPenerimaanGajiUnit(array $months, array $years)
    {
        $fileName = 'rekap-penerimaan-gaji-unit.xls';
        $filePath = storage_path('app/public/' . $fileName);

        Excel::store(new RekapGajiUnitExport($months, $years), 'public/' . $fileName);

        return $filePath;
    }

    private function generatePDFPenerimaanGajiUnit(array $months, array $years)
    {
        $data = (new RekapGajiUnitExport($months, $years))->sheets()[0]->collection();

        $filteredData = $data->filter(function ($row) {
            return $row['No'] !== 'Total';
        });

        $dataChunks = $filteredData->chunk(20);

        $totals = $filteredData->reduce(function ($carry, $item) {
            foreach ($item as $key => $value) {
                if (is_numeric($value)) {
                    $carry[$key] = ($carry[$key] ?? 0) + $value;
                }
            }
            return $carry;
        }, []);

        $premis = Premi::pluck('nama_premi', 'id')->toArray();
        $pdf = Pdf::loadView('gajis.PenerimaanUnitKerja', [
            'dataChunks' => $dataChunks,
            'months' => $months,
            'years' => $years,
            'totals' => $totals,
            'premis' => $premis,
        ])->setPaper('F4', 'landscape');

        $fileName = 'rekap-penerimaan-gaji-unit.pdf';
        $filePath = storage_path('app/public/' . $fileName);

        Storage::put('public/' . $fileName, $pdf->output());

        return $filePath;
    }

    private function generatePDFPenerimaanGajiUnitPenambah(array $months, array $years)
    {
        $data = (new RekapGajiUnitExport($months, $years))->sheets()[2]->collection();

        $filteredData = $data->filter(function ($row) {
            return $row['No'] !== 'Total';
        });

        $dataChunks = $filteredData->chunk(20);

        $totals = $filteredData->reduce(function ($carry, $item) {
            foreach ($item as $key => $value) {
                if (is_numeric($value)) {
                    $carry[$key] = ($carry[$key] ?? 0) + $value;
                }
            }
            return $carry;
        }, []);

        $premis = Premi::pluck('nama_premi', 'id')->toArray();

        $pdf = Pdf::loadView('gajis.PenerimaanUnitKerjaPenambah', [
            'dataChunks' => $dataChunks,
            'months' => $months,
            'years' => $years,
            'totals' => $totals,
            'premis' => $premis,
        ])->setPaper('F4', 'landscape');

        $fileName = 'rekap-penerimaan-gaji-unit-penambah.pdf';
        $filePath = storage_path('app/public/' . $fileName);

        Storage::put('public/' . $fileName, $pdf->output());

        return [$filePath];
    }

    private function generatePDFPenerimaanGajiUnitPengurang(array $months, array $years)
    {
        $data = (new RekapGajiUnitExport($months, $years))->sheets()[1]->collection();

        $filteredData = $data->filter(function ($row) {
            return $row['No'] !== 'Total';
        });

        $dataChunks = $filteredData->chunk(15);

        $totals = $filteredData->reduce(function ($carry, $item) {
            foreach ($item as $key => $value) {
                if (is_numeric($value)) {
                    $carry[$key] = ($carry[$key] ?? 0) + $value;
                }
            }
            return $carry;
        }, []);

        $premis = Premi::pluck('nama_premi', 'id')->toArray();

        $pdf = Pdf::loadView('gajis.PenerimaanUnitKerjaPengurang', [
            'dataChunks' => $dataChunks,
            'months' => $months,
            'years' => $years,
            'totals' => $totals,
            'premis' => $premis,
        ])->setPaper('F4', 'landscape');

        $fileName = 'rekap-penerimaan-gaji-unit-pengurang.pdf';
        $filePath = storage_path('app/public/' . $fileName);

        Storage::put('public/' . $fileName, $pdf->output());

        return [$filePath];
    }

    private function generateZIPPenerimaanGajiUnit(array $files)
    {
        $zipFileName = 'rekap-penerimaan-gaji-unit.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        return $zipPath;
    }
    // ** Export Rekap Penerimaan gaji per unit kerja **

    // ** Export Rekap Penerimaan gaji per profesi **
    private function generateExcelPenerimaanGajiProfesi(array $months, array $years)
    {
        $fileName = 'rekap-penerimaan-gaji-profesi.xls';
        $filePath = storage_path('app/public/' . $fileName);

        Excel::store(new RekapGajiKompetensiExport($months, $years), 'public/' . $fileName);

        return $filePath;
    }

    private function generatePDFPenerimaanGajiProfesi(array $months, array $years)
    {
        $data = (new RekapGajiKompetensiExport($months, $years))->sheets()[0]->collection();

        $filteredData = $data->filter(function ($row) {
            return $row['No'] !== 'Total'; // filter
        });

        $dataChunks = $filteredData->chunk(20); // Maksimal data per halaman

        $totals = $filteredData->reduce(function ($carry, $item) {
            foreach ($item as $key => $value) {
                if (is_numeric($value)) {
                    $carry[$key] = ($carry[$key] ?? 0) + $value;
                }
            }
            return $carry;
        }, []);

        $pdf = Pdf::loadView('gajis.PenerimaanProfesi', [
            'dataChunks' => $dataChunks,
            'months' => $months,
            'years' => $years,
            'totals' => $totals,
        ])->setPaper('F4', 'landscape');

        $fileName = 'rekap-penerimaan-gaji-profesi.pdf';
        $filePath = storage_path('app/public/' . $fileName);

        Storage::put('public/' . $fileName, $pdf->output());

        return $filePath;
    }

    private function generatePDFPenerimaanGajiProfesiSheets(array $months, array $years)
    {
        $export = new RekapGajiKompetensiExport($months, $years);
        $sheets = $export->sheets();

        $pdfPaths = [];

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        foreach ($sheets as $index => $sheet) {
            if ($index === 0) {
                continue; // Skip sheet pertama (all karyawan)
            }

            // Ambil data dari sheet
            $data = $sheet->collection();

            $filteredData = $data->filter(function ($row) {
                return $row['No'] !== 'Total';
            });

            $dataChunks = $filteredData->chunk(20);

            $totals = $filteredData->reduce(function ($carry, $item) {
                foreach ($item as $key => $value) {
                    if (is_numeric($value)) {
                        $carry[$key] = ($carry[$key] ?? 0) + $value;
                    }
                }
                return $carry;
            }, []);

            $title = $sheet->title();
            $profesiNama = explode(' - ', $title)[0];

            $pdf = Pdf::loadView('gajis.PenerimaanProfesi', [
                'dataChunks' => $dataChunks,
                'months' => $months,
                'years' => $years,
                'totals' => $totals,
                'nama_profesi' => $profesiNama,
            ])->setPaper('F4', 'landscape');

            // Pastikan nama file tidak memiliki karakter ilegal
            $safeTitle = Str::slug($sheet->title(), '-');
            $fileName = 'rekap-penerimaan-gaji-profesi-' . $safeTitle . '.pdf';
            $filePath = storage_path('app/public/' . $fileName);

            Storage::put('public/' . $fileName, $pdf->output());

            $pdfPaths[] = $filePath;
        }

        return $pdfPaths;
    }

    private function generateZIPPenerimaanGajiProfesi(array $files)
    {
        $zipFileName = 'rekap-penerimaan-gaji-profesi.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        return $zipPath;
    }
    // ** Export Rekap Penerimaan gaji per profesi **
}
