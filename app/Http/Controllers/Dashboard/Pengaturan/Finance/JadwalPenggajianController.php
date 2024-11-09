<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use Illuminate\Http\Response;
use App\Models\JadwalPenggajian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreJadwalPenggajianRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Carbon\Carbon;

class JadwalPenggajianController extends Controller
{
    public function getJadwalPenggajian($id)
    {
        if (!Gate::allows('view jadwalGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jadwalGaji = JadwalPenggajian::find($id);

        if (!$jadwalGaji) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lokasi kantor tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $jadwalGajiDetail = [
            'id' => $jadwalGaji->id,
            'tgl_mulai' => $jadwalGaji->tgl_mulai,
            'updated_at' => $jadwalGaji->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Detail jadwal gaji berhasil ditampilkan.',
            'data' => $jadwalGajiDetail
        ], Response::HTTP_OK);
    }

    public function createJadwalPenggajian(StoreJadwalPenggajianRequest $request)
    {
        if (!Gate::allows('edit jadwalGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $tgl_mulai = (int)$data['tgl_mulai'];
        $currentDate = Carbon::today('Asia/Jakarta');
        $jadwalPenggajian = Carbon::create($currentDate->year, $currentDate->month, $tgl_mulai);
        // dd('Tgl sekarang: ' . $currentDate . ' | Tgl penggajian: ' . $jadwalPenggajian);

        if ($jadwalPenggajian->lessThan($currentDate)) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Tanggal mulai penggajian tidak valid. Harus berupa hari ini atau tanggal mendatang.'), Response::HTTP_NOT_ACCEPTABLE);
        }

        $jadwalPenggajian = JadwalPenggajian::updateOrCreate(
            ['id' => 1],
            ['tgl_mulai' => $tgl_mulai]
        );

        $message = $jadwalPenggajian->wasRecentlyCreated
            ? "Jadwal penggajian karyawan berhasil diatur pada tanggal {$jadwalPenggajian->tgl_mulai}."
            : "Jadwal penggajian karyawan berhasil diperbarui pada tanggal {$jadwalPenggajian->tgl_mulai}.";

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $message,
            'data' => $jadwalPenggajian
        ], Response::HTTP_OK);
    }
}
