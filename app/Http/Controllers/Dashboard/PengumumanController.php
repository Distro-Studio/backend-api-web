<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Pengumuman;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StorePengumumanRequest;
use App\Http\Requests\UpdatePengumumanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PengumumanController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        if ($pengumuman->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data pengumuman tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $pengumuman->map(function ($pengumuman) {
            return [
                'id' => $pengumuman->id,
                'judul' => $pengumuman->judul,
                'konten' => $pengumuman->konten,
                'is_read' => $pengumuman->is_read,
                'tgl_berakhir' => $pengumuman->tgl_berakhir,
                'created_at' => $pengumuman->created_at,
                'updated_at' => $pengumuman->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data pengumuman ditemukan untuk hari ini.',
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function store(StorePengumumanRequest $request)
    {
        if (!Gate::allows('create pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            // Konversi tgl_berakhir ke format Y-m-d
            $tanggalBerakhir = Carbon::parse($request->tgl_berakhir)->format('Y-m-d');

            $pengumuman = Pengumuman::create([
                'judul' => $request->judul,
                'konten' => $request->konten,
                'tgl_berakhir' => $tanggalBerakhir,
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Pengumuman '{$pengumuman->judul}' berhasil dibuat.",
                'data' => $pengumuman
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan pengumuman: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        if (!Gate::allows('view pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengumuman tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = [
            'id' => $pengumuman->id,
            'judul' => $pengumuman->judul,
            'konten' => $pengumuman->konten,
            'is_read' => $pengumuman->is_read,
            'tgl_berakhir' => $pengumuman->tgl_berakhir,
            'created_at' => $pengumuman->created_at,
            'updated_at' => $pengumuman->updated_at,
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail pengumuman '{$pengumuman->judul}' berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function update(UpdatePengumumanRequest $request, $id)
    {
        if (!Gate::allows('edit pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengumuman tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validated();
        $pengumuman->update($validatedData);

        $formattedData = [
            'id' => $pengumuman->id,
            'judul' => $pengumuman->judul,
            'konten' => $pengumuman->konten,
            'is_read' => $pengumuman->is_read,
            'tgl_berakhir' => $pengumuman->tgl_berakhir,
            'created_at' => $pengumuman->created_at,
            'updated_at' => $pengumuman->updated_at,
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Pengumuman '{$pengumuman->judul}' berhasil diperbarui.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function destroy(Pengumuman $pengumuman)
    {
        if (!Gate::allows('delete pengumuman', $pengumuman)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pengumuman->delete();

        $successMessage = "Data pengumuman '{$pengumuman->judul}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }
}
