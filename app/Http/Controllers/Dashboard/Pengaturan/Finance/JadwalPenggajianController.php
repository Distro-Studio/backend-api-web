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
    public function createJadwalPenggajian(StoreJadwalPenggajianRequest $request)
    {
        if (!Gate::allows('create jadwalGaji') && !Gate::allows('reset jadwalGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Create or Update the first jadwal penggajian
        $jadwalPenggajian = JadwalPenggajian::updateOrCreate(
            ['id' => 1],
            ['tanggal' => $data['tanggal']]
        );

        $message = $jadwalPenggajian->wasRecentlyCreated
            ? "Jadwal penggajian karyawan berhasil diatur pada tanggal {$jadwalPenggajian->tanggal}."
            : "Jadwal penggajian karyawan berhasil diperbarui pada tanggal {$jadwalPenggajian->tanggal}.";

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $message,
            'data' => $jadwalPenggajian
        ], Response::HTTP_OK);
    }
}
