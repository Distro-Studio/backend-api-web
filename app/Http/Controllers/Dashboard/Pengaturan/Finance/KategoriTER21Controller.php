<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriTERRequest;
use App\Http\Requests\UpdateKategoriTERRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\KategoriTer;

class KategoriTER21Controller extends Controller
{
    public function index()
    {
        if (!Gate::allows('view ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kategori_ter = KategoriTer::withTrashed()->orderBy('created_at', 'desc');

        // Search
        // if ($request->has('search')) {
        //     $kategori_ter = $kategori_ter->where(function ($query) use ($request) {
        //         $searchTerm = '%' . $request->search . '%';
        //         $query->orWhere('nama_kategori_ter', 'like', $searchTerm);
        //     });
        // }

        $dataKategoriTER = $kategori_ter->get();
        if ($dataKategoriTER->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kategori TER tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data kategori TER berhasil ditampilkan.";
        $formattedData = $this->formatData($dataKategoriTER);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreKategoriTERRequest $request)
    {
        if (!Gate::allows('create ter21')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $kategori_ter = KategoriTer::create($data);
        $successMessage = "Data kategori TER '{$kategori_ter->nama_kategori_ter}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$kategori_ter]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(KategoriTer $kategori_ter)
    {
        if (!Gate::allows('view ter21', $kategori_ter)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$kategori_ter) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data kategori TER tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $successMessage = "Data kategori TER '{$kategori_ter->nama_kategori_ter}' berhasil ditampilkan.";
        $formattedData = $this->formatData(collect([$kategori_ter]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($id, UpdateKategoriTERRequest $request)
    {
        $kategori_ter = KategoriTer::withTrashed()->find($id);

        if (!Gate::allows('edit ter21', $kategori_ter)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi unique
        $existingDataValidation = KategoriTer::where('nama_kategori_ter', $data['nama_kategori_ter'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama kategori TER tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $kategori_ter->update($data);
        $updatedKategori = $kategori_ter->fresh();
        $successMessage = "Data kategori TER '{$updatedKategori->nama_kategori_ter}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$kategori_ter]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(KategoriTer $kategori_ter)
    {
        if (!Gate::allows('delete ter21', $kategori_ter)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kategori_ter->delete();

        $successMessage = "Data kategori TER '{$kategori_ter->nama_kategori_ter}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $kategori_ter = KategoriTer::withTrashed()->find($id);

        if (!Gate::allows('delete ter21', $kategori_ter)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kategori_ter->restore();

        if (is_null($kategori_ter->deleted_at)) {
            $successMessage = "Data kategori TER '{$kategori_ter->nama_kategori_ter}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $successMessage), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($kategori_ter) {
            return [
                'id' => $kategori_ter->id,
                'nama_kategori_ter' => $kategori_ter->nama_kategori_ter,
                'deleted_at' => $kategori_ter->deleted_at,
                'created_at' => $kategori_ter->created_at,
                'updated_at' => $kategori_ter->updated_at
            ];
        });
    }
}
