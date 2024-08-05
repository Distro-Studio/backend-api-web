<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use Carbon\Carbon;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreHariLiburRequest;
use App\Http\Requests\UpdateHariLiburRequest;
use App\Http\Requests\Excel_Import\ImportHariLiburRequest;
use App\Exports\Pengaturan\Managemen_Waktu\HariLiburExport;
use App\Imports\Pengaturan\Managemen_Waktu\HariLiburImport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class HariLiburController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllHariLibur()
    {
        if (!Gate::allows('view hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $hari_libur = HariLibur::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all hari libur for dropdown',
            'data' => $hari_libur
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Menghapus secara soft delete hari libur yang telah terlewat
        HariLibur::where('tanggal', '<', Carbon::today())->delete();

        $hari_libur = HariLibur::withTrashed();

        // Filter
        if ($request->has('delete_data')) {
            $softDeleteFilters = $request->delete_data;
            $hari_libur->when(in_array('dihapus', $softDeleteFilters) && !in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->onlyTrashed();
            })->when(!in_array('dihapus', $softDeleteFilters) && in_array('belum_dihapus', $softDeleteFilters), function ($query) {
                return $query->withoutTrashed();
            });
        }

        // Search
        if ($request->has('search')) {
            $hari_libur = $hari_libur->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $query->orWhere('nama', 'like', $searchTerm);
            });
        }

        $dataHariLibur = $hari_libur->get();
        if ($dataHariLibur->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hari libur tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data hari libur berhasil ditampilkan.";
        $formattedData = $this->formatData($dataHariLibur);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreHariLiburRequest $request)
    {
        if (!Gate::allows('create hariLibur')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $hari_libur = HariLibur::create($data);
        $successMessage = "Data hari libur '{$hari_libur->nama}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$hari_libur]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(HariLibur $hari_libur)
    {
        if (!Gate::allows('view hariLibur', $hari_libur)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$hari_libur) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data hari libur tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data hari libur '{$hari_libur->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$hari_libur]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateHariLiburRequest $request)
    {
        $hari_libur = HariLibur::withTrashed()->find($id);

        if (!Gate::allows('edit hariLibur', $hari_libur)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = HariLibur::where('nama', $data['nama'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama hari libur tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $hari_libur->update($data);
        $updatedHariLibur = $hari_libur->fresh();
        $successMessage = "Data hari libur '{$updatedHariLibur->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$hari_libur]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(HariLibur $hari_libur)
    {
        if (!Gate::allows('delete hariLibur', $hari_libur)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $hari_libur->delete();

        $successMessage = "Data hari libur {$hari_libur->nama} berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $hari_libur = HariLibur::withTrashed()->find($id);

        if (!Gate::allows('delete hariLibur', $hari_libur)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $hari_libur->restore();

        if (is_null($hari_libur->deleted_at)) {
            $successMessage = "Data hari libur '{$hari_libur->nama}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    public function getNasionalHariLibur()
    {
        $tahunSekarang = now()->format('Y');
        $apiUrl = "https://api-harilibur.vercel.app/api?year={$tahunSekarang}";

        try {
            $response = Http::get($apiUrl);

            if ($response->successful()) {
                $holidayData = collect($response->json());

                $nationalHolidays = $holidayData->filter(function ($holiday) {
                    return $holiday['is_national_holiday'] === true;
                })->map(function ($holiday) {
                    return [
                        'nama' => $holiday['holiday_name'],
                        'tanggal' => $holiday['holiday_date'],
                    ];
                })->values();

                $successMessage = "Data Hari Libur Nasional tahun {$tahunSekarang}.";

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'success' => $successMessage,
                    'data' => $nationalHolidays,
                ], Response::HTTP_OK);
            } else {
                throw new \Exception('Failed to retrieve holiday data');
            }
        } catch (\Exception $th) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'error' => 'Maaf sepertinya server sedang sibuk',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($hariLibur) {
            return [
                'id' => $hariLibur->id,
                'nama' => $hariLibur->nama,
                'tanggal' => $hariLibur->tanggal,
                'deleted_at' => $hariLibur->deleted_at,
                'created_at' => $hariLibur->created_at,
                'updated_at' => $hariLibur->updated_at
            ];
        });
    }
}
