<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\JadwalPenggajian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use function PHPUnit\Framework\isEmpty;

use App\Http\Requests\StoreJadwalPenggajianRequest;
use App\Http\Requests\UpdateJadwalPenggajianRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

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
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Detail jadwal gaji berhasil ditampilkan.',
            'data' => $jadwalGajiDetail
        ], Response::HTTP_OK);
    }

    public function createJadwalPenggajian(StoreJadwalPenggajianRequest $request)
    {
        if (!Gate::allows('create jadwalGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Create or Update the first jadwal penggajian
        $jadwalPenggajian = JadwalPenggajian::updateOrCreate(
            ['id' => 1],
            ['tgl_mulai' => $data['tgl_mulai']]
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
