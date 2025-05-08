<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\ManagemenWaktu;

use App\Models\Shift;
use App\Models\DataKaryawan;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Http\Requests\Excel_Import\ImportShiftRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Imports\Jadwal\PengaturanShiftImport;

class ShiftController extends Controller
{
    public function getAllShift()
    {
        if (!Gate::allows('view shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift = Shift::withoutTrashed()->with('unit_kerjas')->get();
        $formattedData = $shift->map(function ($shifts) {
            return [
                'id' => $shifts->id,
                'unit_kerja' => $shifts->unit_kerjas,
                'jam_from' => $shifts->jam_from,
                'jam_to' => $shifts->jam_to,
                'deleted_at' => $shifts->deleted_at,
                'created_at' => $shifts->created_at,
                'updated_at' => $shifts->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all shift for dropdown',
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function getAllShiftUnitKerja($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view shift')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data_karyawan = DataKaryawan::find($data_karyawan_id);
            if (!$data_karyawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $unitKerjaId = $data_karyawan->unit_kerjas->id ?? null;
            if (!$unitKerjaId) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Unit kerja tidak ditemukan untuk karyawan ini.'), Response::HTTP_NOT_FOUND);
            }

            $shifts = Shift::withoutTrashed()->where('unit_kerja_id', $unitKerjaId)->get();
            if ($shifts->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada jadwal shift yang ditemukan untuk unit kerja ini.'), Response::HTTP_NOT_FOUND);
            }

            $formattedShifts = $shifts->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'nama' => $shift->nama,
                    'unit_kerja' => $shift->unit_kerjas,
                    'jam_from' => $shift->jam_from,
                    'jam_to' => $shift->jam_to,
                    'created_at' => $shift->created_at,
                    'updated_at' => $shift->updated_at,
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data jadwal shift untuk unit kerja '{$data_karyawan->unit_kerjas->nama_unit}' berhasil ditampilkan.",
                'data' => $formattedShifts,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Shifts | - Error function getAllShiftUnitKerja: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index()
    {
        if (!Gate::allows('view shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift = Shift::withTrashed()->orderBy('created_at', 'desc');

        $dataShift = $shift->get();
        if ($dataShift->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data shift berhasil ditampilkan.";
        $formattedData = $this->formatData($dataShift);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreShiftRequest $request)
    {
        if (!Gate::allows('create shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $existingShift = Shift::where('nama', $data['nama'])
            ->where('unit_kerja_id', $data['unit_kerja_id'])
            ->first();
        if ($existingShift) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Shift dengan nama '{$data['nama']}' untuk unit kerja '{$existingShift->unit_kerjas->nama_unit}' sudah tersedia."), Response::HTTP_BAD_REQUEST);
        }

        $shift = Shift::create($data);
        $successMessage = "Data shift '{$shift->nama}' untuk unit kerja '{$shift->unit_kerjas->nama_unit}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Shift $shift)
    {
        if (!Gate::allows('view shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$shift) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data shift tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data shift '{$shift->nama}' untuk unit kerja '{$shift->unit_kerjas->nama_unit}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateShiftRequest $request)
    {
        $shift = Shift::withTrashed()->find($id);

        if (!Gate::allows('edit shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $shift->update($data);
        $updatedShift = $shift->fresh();
        $successMessage = "Data shift '{$updatedShift->nama}' untuk unit kerja '{$updatedShift->unit_kerjas->nama_unit}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$shift]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Shift $shift)
    {
        if (!Gate::allows('delete shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift->delete();

        $successMessage = "Data shift '{$shift->nama}' untuk unit kerja '{$shift->unit_kerjas->nama_unit}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $shift = Shift::withTrashed()->find($id);

        if (!Gate::allows('delete shift', $shift)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $shift->restore();

        if (is_null($shift->deleted_at)) {
            $successMessage = "Data shift '{$shift->nama}' untuk unit kerja '{$shift->unit_kerjas->nama_unit}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    public function importShift(ImportShiftRequest $request)
    {
        if (!Gate::allows('create shift')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        Excel::import(new PengaturanShiftImport, $data['shift_file']);

        $successMessage = 'Import data shift berhasil.';
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($shift) {
            return [
                'id' => $shift->id,
                'nama' => $shift->nama,
                'unit_kerja' => $shift->unit_kerjas,
                'jam_from' => $shift->jam_from,
                'jam_to' => $shift->jam_to,
                'deleted_at' => $shift->deleted_at,
                'created_at' => $shift->created_at,
                'updated_at' => $shift->updated_at
            ];
        });
    }
}
