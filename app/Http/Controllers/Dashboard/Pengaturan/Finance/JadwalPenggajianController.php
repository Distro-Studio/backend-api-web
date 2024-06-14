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
        if (!Gate::allows('create jadwalGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        
        $data = $request->validated();

        $existingData = JadwalPenggajian::where('tanggal', $data['tanggal'])->first();
        if ($existingData) {
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tanggal penggajian tersebut sudah pernah dibuat sebelumnya.'));
        }

        $jadwalGaji = JadwalPenggajian::create($data);
        $message = "Jadwal penggajian karyawan berhasil di atur pada '{$jadwalGaji->tanggal}'.";
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $message,
            'data' => $jadwalGaji
        ], Response::HTTP_OK);
    }

    public function resetJadwalPenggajian(JadwalPenggajian $jadwalPenggajian, UpdateJadwalPenggajianRequest $request)
    {
        if (!Gate::allows('reset jadwalGaji')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        
        $data = $request->validated();

        $jadwalPenggajian->update($data);
        $message = "Jadwal penggajian karyawan berhasil diperbarui pada '{$jadwalPenggajian->tanggal}'";
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $message,
            'data' => $jadwalPenggajian
        ], Response::HTTP_OK);
    }
}
