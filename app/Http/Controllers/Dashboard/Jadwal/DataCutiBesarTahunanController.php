<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use App\Exports\Jadwal\CutiNew\CutiBesarTahunanExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DataCutiBesarTahunanController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard.jadwal.data_cuti_besar_tahunan.index');
    }

    public function exportCutiBesarTahunan(Request $request)
    {
        try {
            if (!Gate::allows('export cutiKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $dataCuti = Cuti::all();
            if ($dataCuti->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data cuti karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            // Mendapatkan filter rentang tanggal
            $tgl_mulai = $request->input('tgl_mulai');
            $tgl_selesai = $request->input('tgl_selesai');
            $tipe_cuti = $request->input('tipe_cuti', []);
            if (empty($tgl_mulai) || empty($tgl_selesai)) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Periode tanggal mulai dan tanggal selesai tidak boleh kosong.'), Response::HTTP_BAD_REQUEST);
            }

            try {
                $startDate = Carbon::createFromFormat('d-m-Y', $tgl_mulai)->startOfDay();
                $endDate = Carbon::createFromFormat('d-m-Y', $tgl_selesai)->endOfDay();
            } catch (\Exception $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal yang dimasukkan tidak valid.'), Response::HTTP_BAD_REQUEST);
            }

            try {
                return Excel::download(new CutiBesarTahunanExport($request->all(), $startDate, $endDate, $tipe_cuti), 'cuti-karyawan.xls');
                // return Excel::download(new CutiJadwalExport($request->all()), 'cuti-karyawan.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Cuti Besar Tahunan | - Error function export: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
