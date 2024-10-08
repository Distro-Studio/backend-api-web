<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use Illuminate\Http\Response;
use App\Models\JenisPenilaian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreJenisPenilaianRequest;
use App\Http\Requests\UpdateJenisPenilaianRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Penilaian\JenisPenilaianResource;

class JenisPenilaianController extends Controller
{
    public function getAllPenilaian()
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jenis_penilaian = JenisPenilaian::withoutTrashed()->get();

        $formattedData = $jenis_penilaian->map(function ($jenis_penilaian) {
            return [
                'id' => $jenis_penilaian->id,
                'nama' => $jenis_penilaian->nama,
                'status_karyawan' => $jenis_penilaian->status_karyawans,
                'jabatan_penilai' => $jenis_penilaian->jabatan_penilais,
                'jabatan_dinilai' => $jenis_penilaian->jabatan_dinilais,
                'created_at' => $jenis_penilaian->created_at,
                'updated_at' => $jenis_penilaian->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data jenis penilaian karyawan berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function index()
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $penilaian = JenisPenilaian::withTrashed()->orderBy('created_at', 'desc');

        $jenis_penilaian = $penilaian->get();
        if ($jenis_penilaian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jenis penilaian karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $jenis_penilaian->map(function ($jenis_penilaian) {
            return [
                'id' => $jenis_penilaian->id,
                'nama' => $jenis_penilaian->nama,
                'status_karyawan' => $jenis_penilaian->status_karyawans,
                'jabatan_penilai' => $jenis_penilaian->jabatan_penilais,
                'jabatan_dinilai' => $jenis_penilaian->jabatan_dinilais,
                'deleted_at' => $jenis_penilaian->deleted_at,
                'created_at' => $jenis_penilaian->created_at,
                'updated_at' => $jenis_penilaian->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data jenis penilaian karyawan berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreJenisPenilaianRequest $request)
    {
        if (!Gate::allows('create penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jenis_penilaian = JenisPenilaian::create($data);
        $successMessage = "Pengaturan jenis penilaian untuk status '{$jenis_penilaian->status_karyawans->label}' dan jabatan dinilai '{$jenis_penilaian->jabatan_dinilais->nama_jabatan}' berhasil ditambahkan.";

        return response()->json(new JenisPenilaianResource(Response::HTTP_OK, $successMessage, $jenis_penilaian), Response::HTTP_OK);
    }

    public function show($id)
    { 
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jenis_penilaian = JenisPenilaian::with('pertanyaans')->find($id);
        if (!$jenis_penilaian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jenis penilaian karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }
        $message = "Detail jenis penilaian karyawan untuk status '{$jenis_penilaian->status_karyawans->label}' dan jabatan dinilai '{$jenis_penilaian->jabatan_dinilais->nama_jabatan}' berhasil ditampilkan.";

        $formattedData = [
            'id' => $jenis_penilaian->id,
            'jenis_penilaian' => [
                'id' => $jenis_penilaian->id,
                'nama' => $jenis_penilaian->nama,
                'status_karyawan' => $jenis_penilaian->status_karyawans,
                'jabatan_penilai' => $jenis_penilaian->jabatan_penilais,
                'jabatan_dinilai' => $jenis_penilaian->jabatan_dinilais,
            ],
            'jumlah_pertanyaan' => $jenis_penilaian->pertanyaans->count(),
            'list_pertanyaan' => $jenis_penilaian->pertanyaans->map(function ($pertanyaan) {
                return [
                    'id' => $pertanyaan->id,
                    'pertanyaan' => $pertanyaan->pertanyaan,
                    'deleted_at' => $pertanyaan->deleted_at,
                    'created_at' => $pertanyaan->created_at,
                    'updated_at' => $pertanyaan->updated_at,
                ];
            })->toArray(),
            'deleted_at' => $jenis_penilaian->deleted_at,
            'created_at' => $jenis_penilaian->created_at,
            'updated_at' => $jenis_penilaian->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $message,
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function update(UpdateJenisPenilaianRequest $request, $id)
    {
        if (!Gate::allows('edit penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $jenis_penilaian = JenisPenilaian::find($id);
        if (!$jenis_penilaian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jenis penilaian karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $existingDataValidation = JenisPenilaian::where('nama', $data['nama'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama jenis penilaian tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $jenis_penilaian->update($data);
        $message = "Data jenis penilaian karyawan untuk status '{$jenis_penilaian->status_karyawans->label}' dan jabatan dinilai '{$jenis_penilaian->jabatan_dinilais->nama_jabatan}' berhasil diperbarui.";

        return response()->json(new JenisPenilaianResource(Response::HTTP_OK, $message, $jenis_penilaian), Response::HTTP_OK);
    }

    public function destroy(JenisPenilaian $jenis_penilaian)
    {
        if (!Gate::allows('delete penilaianKaryawan', $jenis_penilaian)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jenis_penilaian->delete();

        $successMessage = "Data jenis penilaian '{$jenis_penilaian->nama}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($jenis_penilaian)
    {
        $jenis_penilaian = JenisPenilaian::withTrashed()->find($jenis_penilaian);

        if (!Gate::allows('delete penilaianKaryawan', $jenis_penilaian)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jenis_penilaian->restore();

        if (is_null($jenis_penilaian->deleted_at)) {
            $successMessage = "Data jenis penilaian '{$jenis_penilaian->nama}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }
}
