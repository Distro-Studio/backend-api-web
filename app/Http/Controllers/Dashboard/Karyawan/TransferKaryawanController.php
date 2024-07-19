<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Mail\SendNotifyTransfer;
use App\Models\TransferKaryawan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\Karyawan\TransferExport;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreTransferKaryawanRequest;
use App\Http\Requests\UpdateTransferKaryawanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Karyawan\TransferKaryawanResource;
use App\Jobs\EmailNotification\TransferEmailJob;
use App\Models\TrackRecord;

class TransferKaryawanController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllTransferKaryawan()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $transfer = TransferKaryawan::with('users', 'unit_kerja_asals', 'unit_kerja_tujuans', 'jabatan_asals', 'jabatan_tujuans')->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all transfer karyawan for dropdown',
            'data' => $transfer
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $transfer = TransferKaryawan::query();

        // Filter
        if ($request->has('status_transfer') && $request->status_transfer != 'semua_status') {
            $statusTransfer = $request->status_transfer;
            $currentDate = Carbon::now();

            $transfer->where(function ($query) use ($statusTransfer, $currentDate) {
                if ($statusTransfer == 'sukses') {
                    $query->where('tgl_mulai', '<=', $currentDate);
                } elseif ($statusTransfer == 'menunggu') {
                    $query->where('tgl_mulai', '>', $currentDate);
                }
            });
        }

        if ($request->has('status_karyawan')) {
            $namaStatus = $request->status_karyawan;

            $transfer->whereHas('users.data_karyawans', function ($query) use ($namaStatus) {
                if (is_array($namaStatus)) {
                    $query->whereIn('status_karyawan', $namaStatus);
                } else {
                    $query->where('status_karyawan', '=', $namaStatus);
                }
            });
        }

        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $transfer->whereHas('unit_kerja_tujuans', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        // Search
        if ($request->has('search')) {
            $transfer = $transfer->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                });
                $query->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
                $query->orWhereHas('unit_kerja_tos', function ($query) use ($searchTerm) {
                    $query->where('nama_unit', 'like', $searchTerm);
                });

                $query->orWhere('tipe', 'like', $searchTerm)
                    ->orWhere('alasan', 'like', $searchTerm);
            });
        }

        $dataTransfer = $transfer->paginate(10);
        if ($dataTransfer->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data transfer karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataTransfer->items();
        $formattedData = array_map(function ($transfer) {
            $status_transfer = Carbon::now()->greaterThanOrEqualTo(Carbon::parse($transfer->tgl_mulai)) ? 'Sukses' : 'Menunggu';

            return [
                'id' => $transfer->id,
                'user' => $transfer->users,
                'tgl_mulai' => $transfer->tgl_mulai,
                'nik' => $transfer->users->data_karyawans->nik ?? null,
                'unit_kerja_asal' => $transfer->unit_kerja_asals,
                'unit_kerja_tujuan' => $transfer->unit_kerja_tujuans,
                'jabatan_asal' => $transfer->jabatan_asals,
                'jabatan_tujuan' => $transfer->jabatan_tujuans,
                'tipe' => $transfer->tipe,
                'alasan' => $transfer->alasan,
                'dokumen' => $transfer->dokumen,
                'status_transfer' => $status_transfer,
                'created_at' => $transfer->created_at,
                'updated_at' => $transfer->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $dataTransfer->url(1),
                'last' => $dataTransfer->url($dataTransfer->lastPage()),
                'prev' => $dataTransfer->previousPageUrl(),
                'next' => $dataTransfer->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $dataTransfer->currentPage(),
                'last_page' => $dataTransfer->lastPage(),
                'per_page' => $dataTransfer->perPage(),
                'total' => $dataTransfer->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data transfer karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreTransferKaryawanRequest $request)
    {
        if (!Gate::allows('create dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');
            $filePath = $file->store('/berkas/karyawan/karyawan-transfer', 'public');
            $data['dokumen'] = $filePath;
        }
        $transfer = TransferKaryawan::create($data);

        $users = $transfer->users;
        $unit_kerja_asals = $transfer->unit_kerja_asals->nama_unit;
        $unit_kerja_tujuans = $transfer->unit_kerja_tujuans->nama_unit;
        $jabatan_asals = $transfer->jabatan_asals->nama_jabatan;
        $jabatan_tujuans = $transfer->jabatan_tujuans->nama_jabatan;
        $alasan = $transfer->alasan;
        $tgl_mulai = $transfer->tgl_mulai;

        // create track record
        TrackRecord::create([
            'user_id' => $users->id,
            'tgl_masuk' => $users->data_karyawans->tgl_masuk,
            'tgl_keluar' => $users->data_karyawans->tgl_keluar,
            'mutasi' => "Mutasi dari Unit Kerja {$unit_kerja_asals} ke {$unit_kerja_tujuans} - Jabatan {$jabatan_asals} ke {$jabatan_tujuans}",
        ]);

        $details = [
            'nama' => $users->nama,
            'email' => $users->data_karyawans->email,
            'unit_kerja_asals' => $unit_kerja_asals,
            'unit_kerja_tujuans' => $unit_kerja_tujuans,
            'jabatan_asals' => $jabatan_asals,
            'jabatan_tujuans' => $jabatan_tujuans,
            'alasan' => $alasan,
            'tgl_mulai' => $tgl_mulai
        ];

        if ($request->has('beri_tahu_manajer_direktur') && $request->beri_tahu_manajer_direktur == 1) {
            // TODO: ganti email
            TransferEmailJob::dispatch('manager@example.com', ['direktur@example.com'], $details);
        }
        if ($request->has('beri_tahu_karyawan') && $request->beri_tahu_karyawan == 1) {
            TransferEmailJob::dispatch($users->data_karyawans->email, [], $details);
        }

        return response()->json(new TransferKaryawanResource(Response::HTTP_OK, "Berhasil melakukan transfer karyawan {$users->nama}.", $transfer), Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melihat data ini.'), Response::HTTP_FORBIDDEN);
        }

        $transfer = TransferKaryawan::with(['users', 'unit_kerja_asals', 'unit_kerja_tujuans', 'jabatan_asals', 'jabatan_tujuans'])->find($id);

        if (!$transfer) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data transfer karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $status_transfer = Carbon::now()->greaterThanOrEqualTo(Carbon::parse($transfer->tgl_mulai)) ? 'Sukses' : 'Menunggu';

        $formattedData = [
            'id' => $transfer->id,
            'user' => $transfer->users,
            'tgl_mulai' => $transfer->tgl_mulai,
            'nik' => $transfer->users->data_karyawans->nik ?? null,
            'unit_kerja_asal' => $transfer->unit_kerja_asals,
            'unit_kerja_tujuan' => $transfer->unit_kerja_tujuans,
            'jabatan_asal' => $transfer->jabatan_asals,
            'jabatan_tujuan' => $transfer->jabatan_tujuans,
            'tipe' => $transfer->tipe,
            'alasan' => $transfer->alasan,
            'dokumen' => $transfer->dokumen,
            'status_transfer' => $status_transfer,
            'created_at' => $transfer->created_at,
            'updated_at' => $transfer->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail transfer karyawan {$transfer->users->nama} berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update(UpdateTransferKaryawanRequest $request, $id)
    {
        if (!Gate::allows('edit dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $transfer = TransferKaryawan::find($id);

        if (!$transfer) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data transfer karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        if ($request->hasFile('dokumen')) {
            // Hapus dokumen lama jika ada
            if ($transfer->dokumen && Storage::exists('public/' . $transfer->dokumen)) {
                Storage::delete('public/' . $transfer->dokumen);
            }

            $file = $request->file('dokumen');
            $filePath = $file->store('/berkas/karyawan/karyawan-transfer', 'public');
            $data['dokumen'] = $filePath;
        }

        $transfer->update($data);

        $users = $transfer->users;
        $unit_kerja_asals = $transfer->unit_kerja_asals->nama_unit;
        $unit_kerja_tujuans = $transfer->unit_kerja_tujuans->nama_unit;
        $jabatan_asals = $transfer->jabatan_asals->nama_jabatan;
        $jabatan_tujuans = $transfer->jabatan_tujuans->nama_jabatan;
        $alasan = $transfer->alasan;
        $tgl_mulai = $transfer->tgl_mulai;

        // create track record
        TrackRecord::create([
            'user_id' => $users->id,
            'tgl_masuk' => $users->data_karyawans->tgl_masuk,
            'tgl_keluar' => $users->data_karyawans->tgl_keluar,
            'mutasi' => "Pembaharuan mutasi dari Unit Kerja {$unit_kerja_asals} ke {$unit_kerja_tujuans} - Jabatan {$jabatan_asals} ke {$jabatan_tujuans}",
        ]);

        $details = [
            'nama' => $users->nama,
            'email' => $users->data_karyawans->email,
            'unit_kerja_asals' => $unit_kerja_asals,
            'unit_kerja_tujuans' => $unit_kerja_tujuans,
            'jabatan_asals' => $jabatan_asals,
            'jabatan_tujuans' => $jabatan_tujuans,
            'alasan' => $alasan,
            'tgl_mulai' => $tgl_mulai
        ];

        if ($request->has('beri_tahu_manajer_direktur') && $request->beri_tahu_manajer_direktur == 0) {
            // TODO: ganti email
            TransferEmailJob::dispatch('manager@example.com', ['direktur@example.com'], $details);
        }
        if ($request->has('beri_tahu_karyawan') && $request->beri_tahu_karyawan == 0) {
            TransferEmailJob::dispatch($users->data_karyawans->email, [], $details);
        }

        return response()->json(new TransferKaryawanResource(Response::HTTP_OK, "Berhasil memperbarui transfer karyawan {$users->nama}.", $transfer), Response::HTTP_OK);
    }

    public function exportTransferKaryawan(Request $request)
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new TransferExport(), 'transfer-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data transfer karyawan berhasil di download.'), Response::HTTP_OK);
    }

    private function apiBerkas(Request $request)
    {
    }
}
