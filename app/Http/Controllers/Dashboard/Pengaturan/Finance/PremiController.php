<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use App\Models\Premi;
use Illuminate\Http\Request;
use App\Models\PengurangGaji;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StorePremiRequest;
use App\Http\Requests\UpdatePremiRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PremiController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi = Premi::withTrashed()->orderBy('created_at', 'desc');

        $dataPremi = $premi->get();
        if ($dataPremi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data premi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $this->formatData($dataPremi);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data premi berhasil ditampilkan.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StorePremiRequest $request)
    {
        if (!Gate::allows('create premi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $premi = Premi::create($data);
        $successMessage = "Data premi '{$premi->nama_premi}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$premi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Premi $premi)
    {
        if (!Gate::allows('view premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$premi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data premi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $this->formatData(collect([$premi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data premi '{$premi->nama_premi}' berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdatePremiRequest $request)
    {
        $premi = Premi::withTrashed()->find($id);

        if (!Gate::allows('edit premi', $premi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $premi->update($data);
        $updatedPremi = $premi->fresh();
        $successMessage = "Data premi '{$updatedPremi->nama_premi}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$premi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Premi $premi)
    {
        try {
            if (!Gate::allows('delete premi', $premi)) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $pengurangGajis = PengurangGaji::where('premi_id', $premi->id)->get();
            $jumlahKaryawan = $pengurangGajis->count();
            if ($jumlahKaryawan > 0) {
                foreach ($pengurangGajis as $pengurangGaji) {
                    $pengurangGaji->delete();
                }
            }

            $premi->delete();

            $successMessage = "Potongan '{$premi->nama_premi}' berhasil dihapus. Beberapa potongan pada karyawan ({$jumlahKaryawan} karyawan) terkait juga ikut terhapus.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Premi | - Error function destroy: ' . $e->getMessage());
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti atau hubungi SIM RS.'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore($id)
    {
        try {
            $premi = Premi::withTrashed()->find($id);

            if (!Gate::allows('delete premi', $premi)) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $pengurangGajis = PengurangGaji::withTrashed()->where('premi_id', $premi->id)->get();
            $jumlahKaryawan = $pengurangGajis->count();
            if ($jumlahKaryawan > 0) {
                foreach ($pengurangGajis as $pengurangGaji) {
                    $pengurangGaji->restore();
                }
            }

            $premi->restore();

            $successMessage = "Potongan '{$premi->nama_premi}' berhasil dipulihkan. Beberapa potongan pada karyawan terkait ({$jumlahKaryawan} karyawan) juga ikut dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Premi | - Error function restore: ' . $e->getMessage());
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti atau hubungi SIM RS.'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($premi) {
            return [
                'id' => $premi->id,
                'nama_premi' => $premi->nama_premi,
                'kategori_potongan' => $premi->kategori_potongans,
                'jenis_premi' => $premi->jenis_premi,
                'besaran_premi' => $premi->besaran_premi,
                'minimal_rate' => $premi->minimal_rate,
                'maksimal_rate' => $premi->maksimal_rate,
                'deleted_at' => $premi->deleted_at,
                'created_at' => $premi->created_at,
                'updated_at' => $premi->updated_at
            ];
        });
    }
}
