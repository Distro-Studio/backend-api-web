<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use Exception;
use Carbon\Carbon;
use App\Models\Berkas;
use App\Models\Diklat;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\KategoriBerkas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\Perusahaan\DiklatExport;
use App\Http\Requests\StoreDiklatRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DiklatController extends Controller
{
    public function index(Request $request)
    {
        if (! Gate::allows('view diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $diklat = Diklat::query()->orderBy('created_at', 'desc');

        // Filter periode tahun jika ada
        if ($request->has('periode_tahun')) {
            $periode_tahun = $request->input('periode_tahun');
            if (is_array($periode_tahun)) {
                $diklat->whereIn(DB::raw('YEAR(created_at)'), $periode_tahun);
            } else {
                $diklat->whereYear('created_at', $periode_tahun);
            }
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $diklat->where(function ($query) use ($searchTerm) {
                $query->where('nama', 'like', $searchTerm)
                    ->orWhere('lokasi', 'like', $searchTerm)
                    ->orWhereHas('kategori_diklats', function ($query) use ($searchTerm) {
                        $query->where('label', 'like', $searchTerm);
                    })->orWhereHas('status_diklats', function ($query) use ($searchTerm) {
                        $query->where('label', 'like', $searchTerm);
                    });
            });
        }

        if ($limit == 0) {
            $dataDiklat = $diklat->with('kategori_diklats', 'status_diklats', 'peserta_diklat.users')->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataDiklat = $diklat->with('kategori_diklats', 'status_diklats', 'peserta_diklat.users')->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataDiklat->url(1),
                    'last' => $dataDiklat->url($dataDiklat->lastPage()),
                    'prev' => $dataDiklat->previousPageUrl(),
                    'next' => $dataDiklat->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataDiklat->currentPage(),
                    'last_page' => $dataDiklat->lastPage(),
                    'per_page' => $dataDiklat->perPage(),
                    'total' => $dataDiklat->total(),
                ]
            ];
        }

        if ($dataDiklat->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data untuk output
        $formattedData = $dataDiklat->map(function ($diklat) {
            $pesertaList = $diklat->peserta_diklat->map(function ($peserta) {
                return [
                    'user' => $peserta->users,
                ];
            });

            return [
                'id' => $diklat->id,
                'nama_diklat' => $diklat->nama,
                'kategori_diklat' => $diklat->kategori_diklats,
                'status_diklat' => $diklat->status_diklats,
                'deskripsi' => $diklat->deskripsi,
                'kuota' => $diklat->kuota,
                'tgl_mulai' => $diklat->tgl_mulai,
                'tgl_selesai' => $diklat->tgl_selesai,
                'jam_mulai' => $diklat->jam_mulai,
                'jam_selesai' => $diklat->jam_selesai,
                'durasi' => $diklat->durasi,
                'lokasi' => $diklat->lokasi,
                'list_peserta' => $pesertaList, // List of participants
                'created_at' => $diklat->created_at,
                'updated_at' => $diklat->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data diklat berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreDiklatRequest $request)
    {
        if (!Gate::allows('create diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        DB::beginTransaction();
        try {
            $gambarUrl = null;
            $berkas = null;

            if ($request->hasFile('dokumen')) {
                $authUser = Auth::user();

                // Login to the storage server
                StorageServerHelper::login();

                $file = $request->file('dokumen');

                // Upload file using helper
                $dataupload = StorageServerHelper::uploadToServer($request, 'Check in berkas - ' . $authUser->nama);
                $gambarUrl = $dataupload['path'];

                $kategoriBerkas = KategoriBerkas::where('label', 'System')->first();
                if (!$kategoriBerkas) {
                    throw new Exception('Kategori berkas tidak ditemukan.');
                }

                // Store in 'berkas' table
                $berkas = Berkas::create([
                    'user_id' => $authUser->id,
                    'file_id' => $dataupload['id_file']['id'],
                    'nama' => 'Berkas Diklat - ' . $authUser->nama,
                    'kategori_berkas_id' => $kategoriBerkas->id,
                    'path' => $dataupload['path'],
                    'tgl_upload' => now(),
                    'nama_file' => $dataupload['nama_file'],
                    'ext' => $dataupload['ext'],
                    'size' => $dataupload['size'],
                ]);
                if (!$berkas) {
                    throw new Exception('Berkas gagal di upload.');
                }

                StorageServerHelper::logout();
            }

            $jamMulai = Carbon::parse(RandomHelper::convertToTimeString($data['jam_mulai']));
            $jamSelesai = Carbon::parse(RandomHelper::convertToTimeString($data['jam_selesai']));
            $durasi = $jamMulai->diffInSeconds($jamSelesai);
            $diklat = Diklat::create([
                'gambar' => $gambarUrl,
                'nama' => $data['nama'],
                'kategori_diklat_id' => $data['kategori_diklat_id'],
                'status_diklat_id' => 1,
                'deskripsi' => $data['deskripsi'],
                'kuota' => $data['kuota'],
                'tgl_mulai' => $data['tgl_mulai'],
                'tgl_selesai' => $data['tgl_selesai'],
                'jam_mulai' => $data['jam_mulai'],
                'jam_selesai' => $data['jam_selesai'],
                'durasi' => $durasi,
                'lokasi' => $data['lokasi'],
            ]);

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Diklat '{$diklat->nama}' berhasil ditambahkan.",
                'data' => $diklat,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => "Terjadi kesalahan saat menyimpan data diklat, Error: {$e->getMessage()}"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportDiklat()
    {
        if (!Gate::allows('export diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataCuti = Diklat::all();
        if ($dataCuti->isEmpty()) {
            // Kembalikan respons JSON ketika tabel kosong
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data diklat yang tersedia untuk diekspor.'), Response::HTTP_OK);
        }

        try {
            return Excel::download(new DiklatExport(), 'perusahaan-diklat.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
