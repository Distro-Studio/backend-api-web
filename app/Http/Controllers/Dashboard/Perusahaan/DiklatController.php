<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use App\Exports\Perusahaan\DiklatEksternalExport;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Berkas;
use App\Models\Diklat;
use App\Models\Notifikasi;
use Illuminate\Support\Str;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use App\Models\PesertaDiklat;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Perusahaan\DiklatInternalExport;
use App\Helpers\GenerateCertificateHelper;
use App\Http\Requests\StoreDiklatRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DiklatController extends Controller
{
    public function indexInternal(Request $request)
    {
        if (! Gate::allows('view diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10
        $diklat = Diklat::where('kategori_diklat_id', 1)->orderBy('created_at', 'desc');
        $filters = $request->all();

        // Filter periode tahun jika ada
        if ($request->has('periode_tahun')) {
            $periode_tahun = $filters['periode_tahun'];
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
                'kuota' => $diklat->kuota ?? null,
                'total_peserta' => $diklat->total_peserta,
                'tgl_mulai' => $diklat->tgl_mulai,
                'tgl_selesai' => $diklat->tgl_selesai,
                'jam_mulai' => $diklat->jam_mulai,
                'jam_selesai' => $diklat->jam_selesai,
                'durasi' => $diklat->durasi,
                'lokasi' => $diklat->lokasi,
                'list_peserta' => $pesertaList,
                'alasan' => $diklat->alasan ?? null,
                'skp' => $diklat->skp ?? null,
                'certificate_published' => $diklat->certificate_published,
                'certificate_verified_by' => $diklat->certificate_diklats,
                'created_at' => $diklat->created_at,
                'updated_at' => $diklat->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data diklat internal berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function indexEksternal(Request $request)
    {
        if (! Gate::allows('view diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10
        $diklat = Diklat::where('kategori_diklat_id', 2)->orderBy('created_at', 'desc');
        $filters = $request->all();

        // Filter periode tahun jika ada
        if ($request->has('periode_tahun')) {
            $periode_tahun = $filters['periode_tahun'];
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
            $dataDiklat = $diklat->with('kategori_diklats', 'status_diklats', 'peserta_diklat.users', 'berkas_dokumen_eksternals')->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataDiklat = $diklat->with('kategori_diklats', 'status_diklats', 'peserta_diklat.users', 'berkas_dokumen_eksternals')->paginate($limit);

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

            $path_berkas = env('STORAGE_SERVER_DOMAIN') . $diklat->berkas_dokumen_eksternals->path;

            return [
                'id' => $diklat->id,
                'nama_diklat' => $diklat->nama,
                'kategori_diklat' => $diklat->kategori_diklats,
                'path' => $path_berkas,
                'status_diklat' => $diklat->status_diklats,
                'deskripsi' => $diklat->deskripsi,
                'kuota' => $diklat->kuota ?? null,
                'tgl_mulai' => $diklat->tgl_mulai,
                'tgl_selesai' => $diklat->tgl_selesai,
                'jam_mulai' => $diklat->jam_mulai,
                'jam_selesai' => $diklat->jam_selesai,
                'durasi' => $diklat->durasi,
                'lokasi' => $diklat->lokasi,
                'list_peserta' => $pesertaList,
                'alasan' => $diklat->alasan ?? null,
                'skp' => $diklat->skp ?? null,
                'certificate_published' => $diklat->certificate_published,
                'certificate_verified_by' => $diklat->certificate_diklats,
                'created_at' => $diklat->created_at,
                'updated_at' => $diklat->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data diklat eksternal berhasil ditampilkan.',
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

        $now = Carbon::now('Asia/Jakarta');
        $hPlusOne = $now->addDay()->startOfDay();

        $tglMulai = Carbon::createFromFormat('d-m-Y', $data['tgl_mulai'], 'Asia/Jakarta')->startOfDay();
        if ($tglMulai->lessThan($hPlusOne)) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Tanggal mulai harus diisi mulai H+1 dari hari ini.'
            ], Response::HTTP_BAD_REQUEST);
        }

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
                // $gambarUrl = $dataupload['path'];

                $berkas = Berkas::create([
                    'user_id' => $authUser->id,
                    'file_id' => $dataupload['id_file']['id'],
                    'nama' => $random_filename,
                    'kategori_berkas_id' => 2, // umum
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

                $gambarId = $berkas->id;

                StorageServerHelper::logout();
            }

            $jamMulai = Carbon::createFromFormat('H:i:s', $data['jam_mulai'], 'Asia/Jakarta');
            $jamSelesai = Carbon::createFromFormat('H:i:s', $data['jam_selesai'], 'Asia/Jakarta');

            // Cek apakah jam selesai lebih kecil dari jam mulai (berarti selesai keesokan hari)
            if ($jamSelesai->lessThan($jamMulai)) {
                $jamSelesai->addDay(); // Tambahkan 1 hari ke jam selesai
            }

            // Hitung selisih jam (dalam detik)
            $selisihJam = $jamMulai->diffInSeconds($jamSelesai);

            // Hitung total hari (selisih tanggal)
            $tglMulai = Carbon::createFromFormat('d-m-Y', $data['tgl_mulai'], 'Asia/Jakarta');
            $tglSelesai = Carbon::createFromFormat('d-m-Y', $data['tgl_selesai'], 'Asia/Jakarta');
            $totalHari = $tglMulai->diffInDays($tglSelesai) + 1; // +1 untuk menghitung hari mulai

            $durasi = $selisihJam * $totalHari;

            if (Gate::allows('verifikasi2 diklat')) {
                $statusDiklatId = 4;
                $data['verifikator_1'] = Auth::id();
                $data['verifikator_2'] = Auth::id();
            } elseif (Gate::allows('verifikasi1 diklat')) {
                $statusDiklatId = 2;
                $data['verifikator_1'] = Auth::id();
            } else {
                $statusDiklatId = 1;
            }

            $diklat = Diklat::create([
                'gambar' => $gambarId,
                'nama' => $data['nama'],
                'kategori_diklat_id' => 1,
                'status_diklat_id' => $statusDiklatId,
                'deskripsi' => $data['deskripsi'],
                'kuota' => $data['kuota'],
                'tgl_mulai' => $data['tgl_mulai'],
                'tgl_selesai' => $data['tgl_selesai'],
                'jam_mulai' => $data['jam_mulai'],
                'jam_selesai' => $data['jam_selesai'],
                'durasi' => $durasi,
                'lokasi' => $data['lokasi'],
            ]);
            $this->createNotifikasiDiklat($diklat);

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Diklat '{$diklat->nama}' berhasil ditambahkan.",
                'data' => $diklat,
            ], Response::HTTP_CREATED);
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
            'gambar' => $diklat->berkas_gambars ?? null,
            // 'gambar' => $gambarUrl,
            'deskripsi' => $diklat->deskripsi,
            'kuota' => $diklat->kuota,
            'tgl_mulai' => $diklat->tgl_mulai,
            'tgl_selesai' => $diklat->tgl_selesai,
            'jam_mulai' => $diklat->jam_mulai,
            'jam_selesai' => $diklat->jam_selesai,
            'durasi' => $diklat->durasi,
            'lokasi' => $diklat->lokasi,
            // 'dokumen_eksternal' => $dokumenUrl,
            'dokumen_eksternal' => $diklat->berkas_dokumen_eksternals ?? null,
            'peserta_diklat' => $diklat->peserta_diklat->toArray(),
            'verifikator_1' => $diklat->verifikator_1_diklats ? [
                'id' => $diklat->verifikator_1_diklats->id,
                'nama' => $diklat->verifikator_1_diklats->nama,
                'email_verified_at' => $diklat->verifikator_1_diklats->email_verified_at,
                'data_karyawan_id' => $diklat->verifikator_1_diklats->data_karyawan_id,
                'foto_profil' => $diklat->verifikator_1_diklats->foto_profil,
                'data_completion_step' => $diklat->verifikator_1_diklats->data_completion_step,
                'status_aktif' => $diklat->verifikator_1_diklats->status_aktif,
                'created_at' => $diklat->verifikator_1_diklats->created_at,
                'updated_at' => $diklat->verifikator_1_diklats->updated_at
            ] : null,
            'verifikator_2' => $diklat->verifikator_2_diklats ? [
                'id' => $diklat->verifikator_2_diklats->id,
                'nama' => $diklat->verifikator_2_diklats->nama,
                'email_verified_at' => $diklat->verifikator_2_diklats->email_verified_at,
                'data_karyawan_id' => $diklat->verifikator_2_diklats->data_karyawan_id,
                'foto_profil' => $diklat->verifikator_2_diklats->foto_profil,
                'data_completion_step' => $diklat->verifikator_2_diklats->data_completion_step,
                'status_aktif' => $diklat->verifikator_2_diklats->status_aktif,
                'created_at' => $diklat->verifikator_2_diklats->created_at,
                'updated_at' => $diklat->verifikator_2_diklats->updated_at
            ] : null,
            'alasan' => $diklat->alasan ?? null,
            'created_at' => $diklat->created_at,
            'updated_at' => $diklat->updated_at,
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail diklat '{$diklat->nama}' berhasil ditampilkan.",
            'data' => $detailDiklat,
        ], Response::HTTP_OK);
    }

    public function exportDiklatInternal()
    {
        if (!Gate::allows('export diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $diklat_internal = Diklat::where('kategori_diklat_id', 1)->get();
        if ($diklat_internal->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data diklat internal yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new DiklatInternalExport(), 'perusahaan-diklat-internal.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportDiklatEksternal()
    {
        if (!Gate::allows('export diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $diklat_eksternal = Diklat::where('kategori_diklat_id', 2)->get();
        if ($diklat_eksternal->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data diklat eksternal yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new DiklatEksternalExport(), 'perusahaan-diklat-eksternal.xls');
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
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 2;
                $diklat->verifikator_1 = Auth::id();
                $diklat->alasan = null;
                $diklat->save();
                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 untuk Diklat '{$diklat->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' tidak dalam status untuk disetujui pada tahap 1."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 3;
                $diklat->verifikator_1 = Auth::id();
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
            if ($status_diklat_id == 2) {
                $diklat->status_diklat_id = 4;
                $diklat->verifikator_2 = Auth::id();
                $diklat->alasan = null;
                $diklat->save();

                // Update masa diklat karyawan
                $pesertaDiklat = PesertaDiklat::where('diklat_id', $diklatId)->pluck('peserta');
                if ($pesertaDiklat->isNotEmpty()) {
                    foreach ($pesertaDiklat as $userId) {
                        $dataKaryawan = DataKaryawan::where('user_id', $userId)->first();
                        if ($dataKaryawan) {
                            $dataKaryawan->masa_diklat = $diklat->durasi;
                            $dataKaryawan->save();
                        } else {
                            Log::error("Data karyawan dengan user_id {$userId} tidak ditemukan saat mencoba update masa diklat untuk diklat ID {$diklat->id}.");
                        }
                    }
                    Log::info("Proses update masa diklat selesai untuk diklat ID {$diklat->id} dengan jumlah peserta {$pesertaDiklat->count()}.");
                } else {
                    Log::info("Tidak ada peserta untuk diklat ID {$diklat->id} saat melakukan update masa diklat.");
                }

                $message = "Verifikasi tahap 2 untuk Diklat '{$diklat->nama}' telah disetujui.";

                return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
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

    public function generateCertificate($diklatId)
    {
        if (!Gate::allows('publikasi sertifikat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari diklat berdasarkan ID
        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        if ($diklat->status_diklat_id == 4) {
            // Pembuatan sertifikat untuk diklat internal
            if ($diklat->kategori_diklat_id == 1) {
                $pesertaDiklat = PesertaDiklat::where('diklat_id', $diklatId)->pluck('peserta');

                if ($pesertaDiklat->isNotEmpty()) {
                    foreach ($pesertaDiklat as $userId) {
                        $dataKaryawan = DataKaryawan::where('user_id', $userId)->first();
                        if ($dataKaryawan) {
                            $user = $dataKaryawan->users;
                            GenerateCertificateHelper::generateCertificate($diklat, $user);
                            Log::info("Sertifikat untuk Peserta Diklat Internal '{$diklat->nama}' dengan user_id {$userId} telah dibuat.");
                        }
                    }
                    $diklat->certificate_published = 1;
                    $diklat->certificate_verified_by = Auth::id();
                    $diklat->save();

                    $this->createNotifikasiSertifikat($diklat);
                    return response()->json(new WithoutDataResource(Response::HTTP_OK, "Sertifikat untuk Diklat '{$diklat->nama}' berhasil dibuat."), Response::HTTP_OK);
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada peserta untuk diklat '{$diklat->nama}'."), Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' bukan kategori diklat internal yang membutuhkan sertifikat dari RSKI."), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' belum mencapai status verifikasi tahap 2."), Response::HTTP_BAD_REQUEST);
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

    // Untuk verifikasi apakah karyawan benar" ikut diklat (dari absensi manual)
    public function fakeAssignDiklat($diklatId, $userId)
    {
        if (!Gate::allows('publikasi sertifikat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        if ($diklat->kategori_diklat_id != 1) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak dapat menghapus peserta dari diklat eksternal. Silakan lakukan penolakan verifikasi untuk memungkinkan pengajuan ulang.'), Response::HTTP_BAD_REQUEST);
        }

        if ($diklat->status_diklat_id == 4) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak dapat menghapus peserta dari diklat yang sudah disetujui.'), Response::HTTP_BAD_REQUEST);
        }

        $peserta_diklat = PesertaDiklat::where('diklat_id', $diklatId)->where('peserta', $userId)->first();
        if (!$peserta_diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data peserta diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $user = User::find($userId);
        $userName = $user ? $user->nama : $userId;

        // Delete karyawan
        $peserta_diklat->delete();

        // Hitung kembali jumlah peserta
        $jumlahPesertaTersisa = PesertaDiklat::where('diklat_id', $diklatId)->count();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Peserta diklat '{$userName}' berhasil dihapus dari diklat '{$diklat->nama}'."
        ], Response::HTTP_OK);
    }

    private function createNotifikasiDiklat($diklat)
    {
        $konversiNotif_jam_mulai = Carbon::parse(RandomHelper::convertToTimeString($diklat->jam_mulai))->format('H:i:s');
        $konversiNotif_tgl_mulai = Carbon::parse(RandomHelper::convertToDateString($diklat->tgl_mulai))->locale('id')->isoFormat('D MMMM YYYY');
        $message = "Diklat baru berjudul '{$diklat->nama}' akan dilaksanakan pada tanggal {$konversiNotif_tgl_mulai} di lokasi {$diklat->lokasi} pada jam {$konversiNotif_jam_mulai}.";

        // Ambil semua karyawan
        $allUsers = User::where('nama', '!=', 'Super Admin')->get();

        // Buat notifikasi untuk setiap karyawan
        foreach ($allUsers as $user) {
            Notifikasi::create([
                'kategori_notifikasi_id' => 4, // Sesuaikan dengan kategori notifikasi yang sesuai
                'user_id' => $user->id, // Penerima notifikasi
                'message' => $message,
                'is_read' => false,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }

    private function createNotifikasiSertifikat($diklat)
    {
        // Ambil peserta diklat
        $pesertaDiklat = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');

        if ($pesertaDiklat->isNotEmpty()) {
            foreach ($pesertaDiklat as $userId) {
                $dataKaryawan = DataKaryawan::where('user_id', $userId)->first();
                if ($dataKaryawan) {
                    $message = "Sertifikat anda untuk Diklat '{$diklat->nama}' telah dipublikasi dan tersedia untuk diunduh.";

                    Notifikasi::create([
                        'kategori_notifikasi_id' => 5,
                        'user_id' => $dataKaryawan->user_id,
                        'message' => $message,
                        'is_read' => false,
                        'created_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            }
        }
    }
}
