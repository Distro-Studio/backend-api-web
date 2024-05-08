<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use Carbon\Carbon;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreHariLiburRequest;
use App\Http\Requests\UpdateHariLiburRequest;
use App\Imports\Pengaturan\Managemen_Waktu\ShiftImport;
use App\Exports\Pengaturan\Managemen_Waktu\HariLiburExport;
use App\Imports\Pengaturan\Managemen_Waktu\HariLiburImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Pengaturan_Managemen_Waktu\HariLiburResource;
use Exception;

class HariLiburController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllHariLibur()
    {
        if (!Gate::allows('view.hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $hari_libur = HariLibur::get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Hari Libur for dropdown',
            'data' => $hari_libur
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        // $this->middleware(RoleMiddleware::class, ['roles' => ['Super Admin']]);
        if (!Gate::allows('view.hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $hari_libur = HariLibur::query();

        // Filter
        if ($request->has('nama')) {
            $hari_libur = $hari_libur->where('nama', $request->nama);
        }

        if ($request->has('tanggal')) {
            $hari_libur = $hari_libur->where('tanggal', $request->tanggal);
        }

        // Search
        if ($request->has('search')) {
            $hari_libur = $hari_libur->where('nama', 'like', '%' . $request->search . '%');
        }

        // Sort
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort);
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $hari_libur = $hari_libur->orderBy($sortField, $sortOrder);
            }
        }

        $dataHariLibur = $hari_libur->paginate(10);

        if ($dataHariLibur->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Hari Libur tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new HariLiburResource(Response::HTTP_OK, 'Data Hari Libur berhasil ditampilkan.', $dataHariLibur), Response::HTTP_OK);
    }

    public function store(StoreHariLiburRequest $request)
    {
        if (!Gate::allows('create.hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $hari_libur = HariLibur::create($data);
        $successMessage = "Data Hari Libur berhasil dibuat.";
        return response()->json(new HariLiburResource(Response::HTTP_OK, $successMessage, $hari_libur), Response::HTTP_OK);
    }

    public function update(HariLibur $hari_libur, UpdateHariLiburRequest $request)
    {
        if (!Gate::allows('edit.hariLibur', $hari_libur)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $hari_libur->update($data);
        $updatedHariLibur = $hari_libur->fresh();
        $successMessage = "Data Hari Libur '{$updatedHariLibur->nama}' berhasil diubah.";
        return response()->json(new HariLiburResource(Response::HTTP_OK, $successMessage, $hari_libur), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete.hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKompetensi = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:hari_liburs,id'
        ]);

        if ($dataKompetensi->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataKompetensi->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        HariLibur::destroy($ids);

        $deletedCount = HariLibur::whereIn('id', $ids)->delete();
        // $message = sprintf('Deleted %d Jabatan%s', $deletedCount, $deletedCount > 1 ? 's' : '');

        $message = 'Data Hari Libur berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportHariLibur()
    {
        if (!Gate::allows('export.hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        $HariLibur = HariLibur::all();
        return Excel::download(new HariLiburExport, 'hari-liburs.xlsx');
    }

    public function importHariLibur(Request $request)
    {
        if (!Gate::allows('import.hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            Excel::import(new HariLiburImport, $request->file('hari_libur_file'));
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        // More informative success message
        $message = 'Data Hari Libur berhasil di import kedalam table.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function getNasionalHariLibur()
    {
        $tahunSekarang = now()->format('Y');
        $apiHariLibur = Http::get("https://api-harilibur.vercel.app/api?year={$tahunSekarang}");

        try {
            $apiHariLibur = Http::get($apiHariLibur);
            if ($apiHariLibur->successful()) {
                $holidayData = collect($apiHariLibur->json());

                $nationalHolidays = $holidayData->filter(function ($holiday) {
                    return $holiday['is_national_holiday'] === true;
                })->map(function ($holiday) {
                    return [
                        'nama' => $holiday['holiday_name'],
                        'tanggal' => $holiday['holiday_date'],
                    ];
                })->toArray();

                $successMessage = "Data Hari Libur Nasional tahun {$tahunSekarang}.";

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'success' => $successMessage,
                    'data' => $nationalHolidays,
                ], Response::HTTP_OK);
            }
            throw new Exception('Failed to retrieve holiday data');
        } catch (\Exception $th) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Maaf sepertinya server sedang sibuk'), Response::HTTP_BAD_REQUEST);
        }
    }
}
