<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\TukarJadwal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\TukarJadwalExport;
use App\Http\Requests\StoreTukarJadwalRequest;
use App\Http\Resources\Dashboard\Jadwal\TukarJadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class TukarJadwalController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllJadwalTukar()
    {
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $tukarJadwal = TukarJadwal::with(['user_pengajuans.data_karyawans', 'user_ditukars.data_karyawans', 'jadwal_pengajuans.shifts', 'jadwal_ditukars.shifts'])->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all tukar jadwal for dropdown.',
            'data' => $tukarJadwal
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function getKaryawanJadwal()
    {
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $karyawanJadwal = User::whereHas('jadwals')
            ->where('username', '!=', 'super_admin')
            ->with(['jadwals', 'data_karyawans.unit_kerjas'])->get();

        if ($karyawanJadwal->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada karyawan yang memiliki jadwal.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Berhasil menampilkan karyawan yang memiliki jadwal.',
            'data' => $karyawanJadwal
        ], Response::HTTP_OK);
    }

    public function getShiftByDate(Request $request)
    {
        // Ambil data shift berdasarkan tgl_mulai
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'tgl_mulai_pengajuan' => 'required|date',
        ], [
            'tgl_mulai_pengajuan.required' => 'Tanggal tidak diperbolehkan kosong.',
            'tgl_mulai_pengajuan.date' => 'Tanggal yang valid harus berupa tanggal.',
        ]);

        $tglMulai = $validated['tgl_mulai_pengajuan'];

        // Ambil semua shift pada tanggal tertentu
        $shifts = Shift::whereHas('jadwals', function ($query) use ($tglMulai) {
            $query->where('tgl_mulai', $tglMulai);
        })->get();

        if ($shifts->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada shift yang tersedia untuk tanggal {$tglMulai}."), Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Berhasil menampilkan semua jadwal shift yang tersedia untuk tanggal {$tglMulai}.",
            'data' => $shifts
        ], Response::HTTP_OK);
    }

    public function getKaryawanByShiftAndDate(Request $request)
    {
        // Ambil data karyawan berdasarkan tgl_mulai dan shift yang tersedia
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'tgl_mulai_pengajuan' => 'required|date',
        ], [
            'shift_id.required' => 'Silahkan pilih shift yang tersedia terlebih dahulu.',
            'shift_id.exists' => 'Data shift yang dipilih tidak valid.',
            'tgl_mulai_pengajuan.required' => 'Tanggal tidak diperbolehkan kosong.',
            'tgl_mulai_pengajuan.date' => 'Tanggal yang valid harus berupa tanggal.',
        ]);

        $shiftId = $validated['shift_id'];
        $tglMulai = $validated['tgl_mulai_pengajuan'];

        // Ambil nama shift berdasarkan shift_id
        $shift = Shift::find($shiftId);

        if ($shift->nama == 'Libur') {
            // Ambil karyawan yang hanya memiliki shift Libur
            $karyawan = User::whereHas('jadwals', function ($query) use ($tglMulai) {
                $query->where('tgl_mulai', $tglMulai)
                    ->whereHas('shifts', function ($query) {
                        $query->where('nama', 'Libur');
                    });
            })->get();
        } else {
            // Ambil karyawan yang memiliki shift selain Libur
            $karyawan = User::whereHas('jadwals', function ($query) use ($shiftId, $tglMulai) {
                $query->where('tgl_mulai', $tglMulai)
                    ->where('shift_id', $shiftId)
                    ->whereHas('shifts', function ($query) {
                        $query->where('nama', '!=', 'Libur');
                    });
            })->get();
        }

        if ($karyawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada karyawan yang bekerja pada shift {$shift->nama} untuk tanggal {$tglMulai}."), Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Berhasil menampilkan karyawan yang bekerja pada shift {$shift->nama} untuk tanggal {$tglMulai}.",
            'data' => $karyawan
        ], Response::HTTP_OK);
    }

    // Libur ke Shift = Penukar harus cek jadwalnya pada hari yag ditukar (validasi apakah)
    public function store(StoreTukarJadwalRequest $request)
    {
        if (!Gate::allows('create tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Ambil jadwal yang akan ditukar
        $jadwalPengajuan = Jadwal::find($data['jadwal_pengajuan']);
        $jadwalDitukar = Jadwal::find($data['jadwal_ditukar']);

        // Pastikan jadwal sesuai dengan pengguna
        if ($jadwalPengajuan->user_id != $data['user_pengajuan'] || $jadwalDitukar->user_id != $data['user_ditukar']) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Jadwal yang dipilih tidak sesuai dengan pengguna.'), Response::HTTP_BAD_REQUEST);
        }

        // Ambil shift dari jadwal
        $shiftPengajuan = Shift::find($jadwalPengajuan->shift_id);
        $shiftDitukar = Shift::find($jadwalDitukar->shift_id);

        // Validasi shift "Libur"
        if ($shiftPengajuan->nama == 'Libur' && $shiftDitukar->nama == 'Libur') {
            // Validasi apakah tgl_mulai dari karyawan ke-2 cocok dengan shift Libur mereka
            $jadwalLiburDitukar = Jadwal::where('user_id', $data['user_ditukar'])
                ->where('shift_id', $shiftDitukar->id)
                ->where('tgl_mulai', $data['tgl_mulai_ditukar'])
                ->first();

            if (!$jadwalLiburDitukar) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal mulai untuk karyawan yang ditukar Tidak Cocok dengan Shift Libur mereka.'), Response::HTTP_BAD_REQUEST);
            }

            // Tukar shift_id, tgl_mulai, dan tgl_selesai antara dua jadwal
            DB::transaction(function () use ($jadwalPengajuan, $jadwalLiburDitukar) {
                $tempShiftId = $jadwalPengajuan->shift_id;
                $tempTglMulai = $jadwalPengajuan->tgl_mulai;
                $tempTglSelesai = $jadwalPengajuan->tgl_selesai;

                $jadwalPengajuan->shift_id = $jadwalLiburDitukar->shift_id;
                $jadwalPengajuan->tgl_mulai = $jadwalLiburDitukar->tgl_mulai;
                $jadwalPengajuan->tgl_selesai = $jadwalLiburDitukar->tgl_selesai;

                $jadwalLiburDitukar->shift_id = $tempShiftId;
                $jadwalLiburDitukar->tgl_mulai = $tempTglMulai;
                $jadwalLiburDitukar->tgl_selesai = $tempTglSelesai;

                $jadwalPengajuan->save();
                $jadwalLiburDitukar->save();
            });

            // Simpan informasi penukaran
            TukarJadwal::create([
                'user_pengajuan' => $data['user_pengajuan'],
                'user_ditukar' => $data['user_ditukar'],
                'jadwal_pengajuan' => $data['jadwal_pengajuan'],
                'jadwal_ditukar' => $jadwalLiburDitukar->id,
                'status_penukaran' => 0, // 0 untuk berhasil
            ]);

            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Penukaran jadwal shift berhasil dilakukan.'), Response::HTTP_OK);
        } elseif ($shiftPengajuan->nama != 'Libur' && $shiftDitukar->nama != 'Libur') {
            // Pastikan kedua jadwal memiliki tgl_mulai yang sama
            if ($jadwalPengajuan->tgl_mulai != $jadwalDitukar->tgl_mulai) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Penukaran shift hanya dapat dilakukan untuk jadwal dengan Tanggal Mulai yang Sama.'), Response::HTTP_BAD_REQUEST);
            }

            // Tukar shift_id, tgl_mulai, dan tgl_selesai antara dua jadwal
            DB::transaction(function () use ($jadwalPengajuan, $jadwalDitukar) {
                $tempShiftId = $jadwalPengajuan->shift_id;
                $tempTglMulai = $jadwalPengajuan->tgl_mulai;
                $tempTglSelesai = $jadwalPengajuan->tgl_selesai;

                $jadwalPengajuan->shift_id = $jadwalDitukar->shift_id;
                $jadwalPengajuan->tgl_mulai = $jadwalDitukar->tgl_mulai;
                $jadwalPengajuan->tgl_selesai = $jadwalDitukar->tgl_selesai;

                $jadwalDitukar->shift_id = $tempShiftId;
                $jadwalDitukar->tgl_mulai = $tempTglMulai;
                $jadwalDitukar->tgl_selesai = $tempTglSelesai;

                $jadwalPengajuan->save();
                $jadwalDitukar->save();
            });

            // Simpan informasi penukaran
            TukarJadwal::create([
                'user_pengajuan' => $data['user_pengajuan'],
                'user_ditukar' => $data['user_ditukar'],
                'jadwal_pengajuan' => $data['jadwal_pengajuan'],
                'jadwal_ditukar' => $data['jadwal_ditukar'],
                'status_penukaran' => 0, // 0 untuk berhasil
            ]);

            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Penukaran jadwal shift berhasil dilakukan.'), Response::HTTP_OK);
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Penukaran shift tidak dapat dilakukan antara Shift Libur dan Shift Selain Libur.'), Response::HTTP_BAD_REQUEST);
        }
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $tukarJadwal = TukarJadwal::with(['user_pengajuans.data_karyawans', 'user_ditukars.data_karyawans', 'jadwal_pengajuans.shifts', 'jadwal_ditukars.shifts']);

        // filter
        if ($request->has('status_penukaran') && $request->status_penukaran !== 'semua_status') {
            $statusPenukaran = (bool) $request->status_penukaran ? 1 : 0;
            $tukarJadwal->where('status_penukaran', '=', $statusPenukaran);
        }

        // Search
        if ($request->has('search')) {
            $tukarJadwal = $tukarJadwal->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('user_pengajuans', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })
                    ->orWhereHas('user_pengajuans.data_karyawans.unit_kerjas', function ($query) use ($searchTerm) {
                        $query->where('nama_unit', 'like', $searchTerm);
                    });
            });
        }

        $dataTukarJadwal = $tukarJadwal->paginate(10);
        if ($dataTukarJadwal->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penukaran jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataTukarJadwal->items();
        $formattedData = array_map(function ($tukar_jadwal) {
            return [
                'id' => $tukar_jadwal->id,
                'user' => $tukar_jadwal->user_pengajuans,
                'unit_kerja' => $tukar_jadwal->user_pengajuans->data_karyawans->unit_kerjas,
                'tanggal_pengajuan' => $tukar_jadwal->created_at,
                'jadwal_pengajuan' => $tukar_jadwal->jadwal_pengajuans,
                'jadwal_ditukar' => $tukar_jadwal->jadwal_ditukars,
                'user_ditukar' => $tukar_jadwal->user_ditukars,
                'status_penukaran' => $tukar_jadwal->status_penukaran,
                'created_at' => $tukar_jadwal->created_at,
                'updated_at' => $tukar_jadwal->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $dataTukarJadwal->url(1),
                'last' => $dataTukarJadwal->url($dataTukarJadwal->lastPage()),
                'prev' => $dataTukarJadwal->previousPageUrl(),
                'next' => $dataTukarJadwal->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $dataTukarJadwal->currentPage(),
                'last_page' => $dataTukarJadwal->lastPage(),
                'per_page' => $dataTukarJadwal->perPage(),
                'total' => $dataTukarJadwal->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data tukar jadwal karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $tukar_jadwal = TukarJadwal::with(['user_pengajuans.data_karyawans', 'user_ditukars.data_karyawans', 'jadwal_pengajuans.shifts', 'jadwal_ditukars.shifts'])->find($id);
        if (!$tukar_jadwal) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tukar jadwal karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = [
            'id' => $tukar_jadwal->id,
            'user' => $tukar_jadwal->user_pengajuans,
            'unit_kerja' => $tukar_jadwal->user_pengajuans->data_karyawans->unit_kerjas,
            'tanggal_pengajuan' => $tukar_jadwal->created_at,
            'jadwal_pengajuan' => $tukar_jadwal->jadwal_pengajuans,
            'jadwal_ditukar' => $tukar_jadwal->jadwal_ditukars,
            'user_ditukar' => $tukar_jadwal->user_ditukars,
            'status_penukaran' => $tukar_jadwal->status_penukaran,
            'created_at' => $tukar_jadwal->created_at,
            'updated_at' => $tukar_jadwal->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail data tukar jadwal karyawan {$tukar_jadwal->user_pengajuans->nama} berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function exportJadwalTukar(Request $request)
    {
        if (!Gate::allows('export tukarJadwal')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new TukarJadwalExport(), 'tukar-jadwal-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jadwal lembur karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
