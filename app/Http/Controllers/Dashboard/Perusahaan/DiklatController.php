<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Berkas;
use App\Models\Diklat;
use App\Models\Notifikasi;
use Illuminate\Support\Str;
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

        if (isset($filters['status_diklat'])) {
            $statusDiklat = $filters['status_diklat'];
            $diklat->whereHas('status_diklats', function ($query) use ($statusDiklat) {
                if (is_array($statusDiklat)) {
                    $query->whereIn('id', $statusDiklat);
                } else {
                    $query->where('id', '=', $statusDiklat);
                }
            });
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
                $random_filename = Str::random(20);
                $dataupload = StorageServerHelper::uploadToServer($request, $random_filename);
                $gambarUrl = $dataupload['path'];

                $kategoriBerkas = KategoriBerkas::where('label', 'System')->first();
                if (!$kategoriBerkas) {
                    throw new Exception('Kategori berkas tidak ditemukan.');
                }

                // Store in 'berkas' table
                $berkas = Berkas::create([
                    'user_id' => $authUser->id,
                    'file_id' => $dataupload['id_file']['id'],
                    'nama' => $random_filename,
                    'kategori_berkas_id' => $kategoriBerkas->id,
                    'status_berkas_id' => 2,
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
                'kategori_diklat_id' => 1,
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

            // Buat dan simpan notifikasi untuk semua karyawan
            $this->createNotifikasiDiklat($diklat);

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

    public function show($diklatId)
    {
        // Check user permission to view diklat
        if (!Gate::allows('view diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Find diklat by ID
        $diklat = Diklat::with([
            'kategori_diklats',
            'status_diklats',
            'peserta_diklat' => function ($query) {
                $query->select('id', 'diklat_id', 'peserta'); // Select relevant fields
            },
        ])->find($diklatId);

        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Ambil path untuk gambar dan dokumen eksternal
        $baseUrl = env('STORAGE_SERVER_DOMAIN'); // Ganti dengan URL domain Anda

        $gambarUrl = null;
        if ($diklat->gambar) {
            $gambarBerkas = Berkas::where('id', $diklat->gambar)->first();
            if ($gambarBerkas) {
                $gambarExt = StorageServerHelper::getExtensionFromMimeType($gambarBerkas->ext);
                $gambarUrl = $baseUrl . $gambarBerkas->path . '.' . $gambarExt;
            }
        }

        $dokumenUrl = null;
        if ($diklat->dokumen_eksternal) {
            $dokumenBerkas = Berkas::where('id', $diklat->dokumen_eksternal)->first();
            if ($dokumenBerkas) {
                $dokumenExt = StorageServerHelper::getExtensionFromMimeType($dokumenBerkas->ext);
                $dokumenUrl = $baseUrl . $dokumenBerkas->path . '.' . $dokumenExt;
            }
        }

        // Format the data for the response
        $detailDiklat = [
            'id' => $diklat->id,
            'nama' => $diklat->nama,
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
            'gambar' => $gambarUrl,
            'dokumen_eksternal' => $dokumenUrl,
            'peserta_diklat' => $diklat->peserta_diklat->toArray(),
            'created_at' => $diklat->created_at,
            'updated_at' => $diklat->updated_at,
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail diklat '{$diklat->nama}' berhasil ditampilkan.",
            'data' => $detailDiklat,
        ], Response::HTTP_OK);
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

    public function verifikasiTahap1(Request $request, $diklatId)
    {
        if (!Gate::allows('verifikasi1 diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari diklat berdasarkan ID
        $diklat = Diklat::find($diklatId);

        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $status_diklat_id = $diklat->status_diklat_id;

        if ($request->has('verifikasi_pertama_disetujui') && $request->verifikasi_pertama_disetujui == 1) {
            // Jika status_diklat_id = 1 atau 3 (setelah ditolak), maka bisa disetujui
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 2; // Update status ke tahap 1 disetujui
                $diklat->verifikator_1 = Auth::id(); // Set verifikator tahap 1
                $diklat->alasan = null;
                $diklat->save();
                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 untuk Diklat '{$diklat->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' tidak dalam status untuk disetujui pada tahap 1."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
            // Jika status_diklat_id = 1, maka bisa ditolak
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 3; // Update status ke tahap 1 ditolak
                $diklat->verifikator_1 = Auth::id(); // Set verifikator tahap 1
                $diklat->alasan = $request->input('alasan', null);
                $diklat->save();
                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 untuk Diklat '{$diklat->nama}' telah ditolak."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' tidak dalam status untuk ditolak pada tahap 1."), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
        }
    }

    public function verifikasiTahap2(Request $request, $diklatId)
    {
        if (!Gate::allows('verifikasi2 diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari diklat berdasarkan ID
        $diklat = Diklat::find($diklatId);

        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $status_diklat_id = $diklat->status_diklat_id;

        if ($request->has('verifikasi_kedua_disetujui') && $request->verifikasi_kedua_disetujui == 1) {
            // Jika status_diklat_id = 2, maka bisa disetujui
            if ($status_diklat_id == 2) {
                $diklat->status_diklat_id = 4; // Update status ke tahap 2 disetujui
                $diklat->verifikator_2 = Auth::id(); // Set verifikator tahap 2
                $diklat->alasan = null;
                $diklat->save();
                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 untuk Diklat '{$diklat->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' tidak dalam status untuk disetujui pada tahap 2."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_kedua_ditolak') && $request->verifikasi_kedua_ditolak == 1) {
            // Jika status_diklat_id = 2, maka bisa ditolak
            if ($status_diklat_id == 2) {
                $diklat->status_diklat_id = 5;
                $diklat->verifikator_2 = Auth::id();
                $diklat->alasan = $request->input('alasan', null);
                $diklat->save();
                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 untuk Diklat '{$diklat->nama}' telah ditolak."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' tidak dalam status untuk ditolak pada tahap 2."), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
        }
    }

    // Untuk verifikasi diklat eksternal
    public function verifikasiDiklatExternal(Request $request, $diklatId)
    {
        if (!Gate::allows('verifikasi1 diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat eksternal tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $status_diklat_id = $diklat->status_diklat_id;
        if ($request->has('diklat_eksternal_disetujui') && $request->diklat_eksternal_disetujui == 1) {
            // Jika status_diklat_id = 1, maka bisa disetujui
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 2;
                $diklat->verifikator_1 = Auth::id();
                $diklat->durasi = $request->input('durasi');
                $diklat->save();
                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi untuk Diklat Eksternal '{$diklat->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat Eksternal '{$diklat->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('diklat_eksternal_ditolak') && $request->diklat_eksternal_ditolak == 1) {
            // Jika status_diklat_id = 2, maka bisa ditolak
            if ($status_diklat_id == 2) {
                $diklat->status_diklat_id = 5;
                $diklat->verifikator_2 = Auth::id();
                $diklat->alasan = $request->input('alasan', null);
                $diklat->save();
                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 untuk Diklat Eksternal '{$diklat->nama}' telah ditolak."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat Eksternal '{$diklat->nama}' tidak dalam status untuk ditolak pada tahap 2."), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
        }
    }

    private function createNotifikasiDiklat($diklat)
    {
        $konversiNotif_jam_mulai = Carbon::parse(RandomHelper::convertToTimeString($diklat->jam_mulai))->format('H:i:s');
        $konversiNotif_tgl_mulai = Carbon::parse(RandomHelper::convertToDateString($diklat->tgl_mulai))->locale('id')->isoFormat('D MMMM YYYY');
        $message = "Diklat baru berjudul {$diklat->nama} akan dilaksanakan pada tanggal {$konversiNotif_tgl_mulai} di lokasi {$diklat->lokasi} pada jam {$konversiNotif_jam_mulai}.";

        // Ambil semua karyawan
        $allUsers = User::where('nama', '!=', 'Super Admin')->get();

        // Buat notifikasi untuk setiap karyawan
        foreach ($allUsers as $user) {
            Notifikasi::create([
                'kategori_notifikasi_id' => 4, // Sesuaikan dengan kategori notifikasi yang sesuai
                'user_id' => $user->id, // Penerima notifikasi
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }
}
