<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Berkas;
use App\Models\Diklat;
use App\Models\Notifikasi;
use Illuminate\Support\Str;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Models\PesertaDiklat;
use Illuminate\Http\Response;
use App\Models\ModulVerifikasi;
use App\Models\RelasiVerifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreDiklatRequest;
use App\Helpers\GenerateCertificateHelper;
use App\Exports\Perusahaan\DiklatInternalExport;
use App\Exports\Perusahaan\DiklatEksternalExport;
use App\Http\Requests\StoreDiklatExternalRequest;
use App\Http\Requests\UpdateDiklatExternalRequest;
use App\Http\Requests\UpdateDiklatRequest;
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
        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = $dataDiklat->map(function ($diklat) use ($baseUrl) {
            $pesertaList = $diklat->peserta_diklat->map(function ($peserta) {
                return [
                    'user' => $peserta->users,
                ];
            });

            $userId = $diklat->peserta_diklat->pluck('users.id')->first() ?? null;
            $relasiVerifikasi = $userId ? RelasiVerifikasi::whereJsonContains('user_diverifikasi', (int) $userId)
                ->where('modul_verifikasi', 5)
                ->get() : collect();

            // Dapatkan max order dari modul_verifikasi untuk diklat internal (modul_verifikasi = 4)
            $modulVerifikasi = ModulVerifikasi::where('id', 5)->first();
            $maxOrder = $modulVerifikasi ? $modulVerifikasi->max_order : 0;

            // Lakukan loop sebanyak max order
            $formattedRelasiVerifikasi = [];
            for ($i = 1; $i <= $maxOrder; $i++) {
                $verifikasiForOrder = $relasiVerifikasi->firstWhere('order', $i);
                $formattedRelasiVerifikasi[] = $verifikasiForOrder ? [
                    'id' => $verifikasiForOrder->id,
                    'nama' => $verifikasiForOrder->nama,
                    'verifikator' => [
                        'id' => $verifikasiForOrder->users->id,
                        'nama' => $verifikasiForOrder->users->nama,
                        'username' => $verifikasiForOrder->users->username,
                        'email_verified_at' => $verifikasiForOrder->users->email_verified_at,
                        'data_karyawan_id' => $verifikasiForOrder->users->data_karyawan_id,
                        'foto_profil' => $verifikasiForOrder->users->foto_profiles ? [
                            'id' => $verifikasiForOrder->users->foto_profiles->id,
                            'user_id' => $verifikasiForOrder->users->foto_profiles->user_id,
                            'file_id' => $verifikasiForOrder->users->foto_profiles->file_id,
                            'nama' => $verifikasiForOrder->users->foto_profiles->nama,
                            'nama_file' => $verifikasiForOrder->users->foto_profiles->nama_file,
                            'path' => $baseUrl . $verifikasiForOrder->users->foto_profiles->path,
                            'ext' => $verifikasiForOrder->users->foto_profiles->ext,
                            'size' => $verifikasiForOrder->users->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $verifikasiForOrder->users->data_completion_step,
                        'status_aktif' => $verifikasiForOrder->users->status_aktif,
                        'created_at' => $verifikasiForOrder->users->created_at,
                        'updated_at' => $verifikasiForOrder->users->updated_at
                    ],
                    'order' => $verifikasiForOrder->order,
                    'user_diverifikasi' => $verifikasiForOrder->user_diverifikasi,
                    'modul_verifikasi' => $verifikasiForOrder->modul_verifikasi,
                    'created_at' => $verifikasiForOrder->created_at,
                    'updated_at' => $verifikasiForOrder->updated_at
                ] : [
                    'id' => null,
                    'nama' => null,
                    'verifikator' => null,
                    'order' => $i,
                    'user_diverifikasi' => null,
                    'modul_verifikasi' => null,
                    'created_at' => null,
                    'updated_at' => null
                ];
            }

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
                'dokumen_diklat_1' => $diklat->berkas_internal_1 ?? null,
                'dokumen_diklat_2' => $diklat->berkas_internal_2 ?? null,
                'dokumen_diklat_3' => $diklat->berkas_internal_3 ?? null,
                'dokumen_diklat_4' => $diklat->berkas_internal_4 ?? null,
                'dokumen_diklat_5' => $diklat->berkas_internal_5 ?? null,
                'certificate_published' => $diklat->certificate_published,
                'certificate_verified_by' => $diklat->certificate_diklats,
                'relasi_verifikasi' => $formattedRelasiVerifikasi,
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
        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = $dataDiklat->map(function ($diklat) use ($baseUrl) {
            $pesertaList = $diklat->peserta_diklat->map(function ($peserta) {
                return [
                    'user' => $peserta->users,
                ];
            });

            $path_berkas = env('STORAGE_SERVER_DOMAIN') . $diklat->berkas_dokumen_eksternals->path;

            $userId = $diklat->peserta_diklat->pluck('users.id')->first() ?? null;
            $relasiVerifikasi = $userId ? RelasiVerifikasi::whereJsonContains('user_diverifikasi', (int) $userId)
                ->where('modul_verifikasi', 6)
                ->get() : collect();

            // Dapatkan max order dari modul_verifikasi untuk diklat eksternal (modul_verifikasi = 6)
            $modulVerifikasi = ModulVerifikasi::where('id', 6)->first();
            $maxOrder = $modulVerifikasi ? $modulVerifikasi->max_order : 0;

            // Lakukan loop sebanyak max order
            $formattedRelasiVerifikasi = [];
            for ($i = 1; $i <= $maxOrder; $i++) {
                $verifikasiForOrder = $relasiVerifikasi->firstWhere('order', $i);
                $formattedRelasiVerifikasi[] = $verifikasiForOrder ? [
                    'id' => $verifikasiForOrder->id,
                    'nama' => $verifikasiForOrder->nama,
                    'verifikator' => [
                        'id' => $verifikasiForOrder->users->id,
                        'nama' => $verifikasiForOrder->users->nama,
                        'username' => $verifikasiForOrder->users->username,
                        'email_verified_at' => $verifikasiForOrder->users->email_verified_at,
                        'data_karyawan_id' => $verifikasiForOrder->users->data_karyawan_id,
                        'foto_profil' => $verifikasiForOrder->users->foto_profiles ? [
                            'id' => $verifikasiForOrder->users->foto_profiles->id,
                            'user_id' => $verifikasiForOrder->users->foto_profiles->user_id,
                            'file_id' => $verifikasiForOrder->users->foto_profiles->file_id,
                            'nama' => $verifikasiForOrder->users->foto_profiles->nama,
                            'nama_file' => $verifikasiForOrder->users->foto_profiles->nama_file,
                            'path' => $baseUrl . $verifikasiForOrder->users->foto_profiles->path,
                            'ext' => $verifikasiForOrder->users->foto_profiles->ext,
                            'size' => $verifikasiForOrder->users->foto_profiles->size,
                        ] : null,
                        'data_completion_step' => $verifikasiForOrder->users->data_completion_step,
                        'status_aktif' => $verifikasiForOrder->users->status_aktif,
                        'created_at' => $verifikasiForOrder->users->created_at,
                        'updated_at' => $verifikasiForOrder->users->updated_at
                    ],
                    'order' => $verifikasiForOrder->order,
                    'user_diverifikasi' => $verifikasiForOrder->user_diverifikasi,
                    'modul_verifikasi' => $verifikasiForOrder->modul_verifikasi,
                    'created_at' => $verifikasiForOrder->created_at,
                    'updated_at' => $verifikasiForOrder->updated_at
                ] : [
                    'id' => null,
                    'nama' => null,
                    'verifikator' => null,
                    'order' => $i,
                    'user_diverifikasi' => null,
                    'modul_verifikasi' => null,
                    'created_at' => null,
                    'updated_at' => null
                ];
            }

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
                'relasi_verifikasi' => $formattedRelasiVerifikasi,
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

        DB::beginTransaction();
        try {
            StorageServerHelper::login();
            $berkasIds = [
                'dokumen' => null,
                'dokumen_diklat_1' => null,
                'dokumen_diklat_2' => null,
                'dokumen_diklat_3' => null,
                'dokumen_diklat_4' => null,
                'dokumen_diklat_5' => null,
            ];

            // Upload dokumen_diklat_1 s.d. dokumen_diklat_5 (sebagian boleh kosong)
            $authUser = Auth::user();
            foreach ($berkasIds as $field => $value) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $random_filename = Str::random(20);
                    $dataupload = StorageServerHelper::multipleUploadToServer($file, $random_filename);

                    // Simpan data berkas ke tabel berkas
                    $berkas = Berkas::create([
                        'user_id' => $authUser->id,
                        'file_id' => $dataupload['id_file']['id'],
                        'nama' => $random_filename,
                        'kategori_berkas_id' => 2,
                        'status_berkas_id' => 2,
                        'path' => $dataupload['path'],
                        'tgl_upload' => now('Asia/Jakarta'),
                        'nama_file' => $dataupload['nama_file'],
                        'ext' => $dataupload['ext'],
                        'size' => $dataupload['size'],
                    ]);

                    // Simpan ID berkas ke urutan yang sesuai
                    $berkasIds[$field] = $berkas->id;
                    Log::info("Berkas dokumen pada kolom {$field} berhasil diupload.");
                }
            }

            StorageServerHelper::logout();

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

            $diklat = Diklat::create([
                'gambar' => $berkasIds['dokumen'],
                'dokumen_diklat_1' => $berkasIds['dokumen_diklat_1'],
                'dokumen_diklat_2' => $berkasIds['dokumen_diklat_2'],
                'dokumen_diklat_3' => $berkasIds['dokumen_diklat_3'],
                'dokumen_diklat_4' => $berkasIds['dokumen_diklat_4'],
                'dokumen_diklat_5' => $berkasIds['dokumen_diklat_5'],
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
                'lokasi' => $data['lokasi']
            ]);

            $userIds = $data['user_id'] ?? null;

            // Menyimpan peserta diklat dan menentukan is_whitelist
            if ($userIds) {
                foreach ($userIds as $userId) {
                    DB::table('peserta_diklats')->insert([
                        'diklat_id' => $diklat->id,
                        'peserta' => $userId,
                    ]);
                }

                // Update kolom is_whitelist pada diklat jika ada user
                $diklat->update(['is_whitelist' => 1]);

                // Update total peserta berdasarkan jumlah user_id
                $diklat->update(['total_peserta' => count($userIds)]);
            } else {
                // Jika user_id null, kuota tetap diambil dari payload
                $diklat->update(['total_peserta' => 0]);
            }

            // Mengupdate kuota sesuai dengan total peserta
            $diklat->update(['kuota' => $diklat->total_peserta]);

            $this->createNotifikasiDiklat($diklat, $userIds);

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

    public function storeExternal(StoreDiklatExternalRequest $request)
    {
        // $loggedUser = Auth::user();
        // if (!$loggedUser->hasRole('Super Admin')) {
        //     return response()->json([
        //         'status' => Response::HTTP_FORBIDDEN,
        //         'message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.'
        //     ], Response::HTTP_FORBIDDEN);
        // }

        if (!Gate::allows('create diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $verifikatorId = Auth::id();
        $data = $request->validated();

        DB::beginTransaction();
        try {
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

            $diklat = Diklat::create([
                'dokumen_eksternal' => $gambarId,
                'nama' => $data['nama'],
                'kategori_diklat_id' => 2,
                'status_diklat_id' => 4,
                'deskripsi' => $data['deskripsi'],
                'kuota' => 1,
                'total_peserta' => 1,
                'tgl_mulai' => $data['tgl_mulai'],
                'tgl_selesai' => $data['tgl_selesai'],
                'jam_mulai' => $data['jam_mulai'],
                'jam_selesai' => $data['jam_selesai'],
                'durasi' => $durasi,
                'lokasi' => $data['lokasi'],
                'verifikator_1' => $verifikatorId,
                'verifikator_2' => $verifikatorId
            ]);

            PesertaDiklat::create([
                'diklat_id' => $diklat->id,
                'peserta' => $data['user_id'],
            ]);

            // Update masa_diklat karyawan
            $this->increaseMasaDiklat($data['user_id'], $durasi);

            $user = User::find($data['user_id']);
            if (!$user) {
                throw new Exception('Karyawan tidak ditemukan.');
            }

            $this->createNotifikasiDiklatExternal($diklat, $data['user_id']);
            DB::commit();

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Diklat Eksternal '{$diklat->nama}' dari karyawan '{$user->nama}' berhasil ditambahkan.",
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
        $baseUrl = env('STORAGE_SERVER_DOMAIN');
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
                'foto_profil' => $diklat->verifikator_1_diklats->foto_profiles ? [
                    'id' => $diklat->verifikator_1_diklats->foto_profiles->id,
                    'user_id' => $diklat->verifikator_1_diklats->foto_profiles->user_id,
                    'file_id' => $diklat->verifikator_1_diklats->foto_profiles->file_id,
                    'nama' => $diklat->verifikator_1_diklats->foto_profiles->nama,
                    'nama_file' => $diklat->verifikator_1_diklats->foto_profiles->nama_file,
                    'path' => $baseUrl . $diklat->verifikator_1_diklats->foto_profiles->path,
                    'ext' => $diklat->verifikator_1_diklats->foto_profiles->ext,
                    'size' => $diklat->verifikator_1_diklats->foto_profiles->size,
                ] : null,
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
                'foto_profil' => $diklat->verifikator_2_diklats->foto_profiles ? [
                    'id' => $diklat->verifikator_2_diklats->foto_profiles->id,
                    'user_id' => $diklat->verifikator_2_diklats->foto_profiles->user_id,
                    'file_id' => $diklat->verifikator_2_diklats->foto_profiles->file_id,
                    'nama' => $diklat->verifikator_2_diklats->foto_profiles->nama,
                    'nama_file' => $diklat->verifikator_2_diklats->foto_profiles->nama_file,
                    'path' => $baseUrl . $diklat->verifikator_2_diklats->foto_profiles->path,
                    'ext' => $diklat->verifikator_2_diklats->foto_profiles->ext,
                    'size' => $diklat->verifikator_2_diklats->foto_profiles->size,
                ] : null,
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

    public function updateInternal(UpdateDiklatRequest $request, $diklatId)
    {
        if (!Gate::allows('edit diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $diklat = Diklat::findOrFail($diklatId);
        if ($diklat->status_diklat_id !== 1) {
            return response()->json(new WithoutDataResource(
                Response::HTTP_FORBIDDEN,
                "Hanya diklat yang memiliki status 'Menunggu Verifikasi' yang dapat dilakukan perubahan."
            ), Response::HTTP_FORBIDDEN);
        }

        DB::beginTransaction();
        try {
            StorageServerHelper::login();
            $berkasIds = [
                'dokumen' => null,
                'dokumen_diklat_1' => null,
                'dokumen_diklat_2' => null,
                'dokumen_diklat_3' => null,
                'dokumen_diklat_4' => null,
                'dokumen_diklat_5' => null,
            ];

            // Upload dokumen_diklat_1 s.d. dokumen_diklat_5 (sebagian boleh kosong)
            $authUser = Auth::user();
            foreach ($berkasIds as $field => $value) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $random_filename = Str::random(20);
                    $dataupload = StorageServerHelper::multipleUploadToServer($file, $random_filename);

                    // Simpan data berkas ke tabel berkas
                    $berkas = Berkas::create([
                        'user_id' => $authUser->id,
                        'file_id' => $dataupload['id_file']['id'],
                        'nama' => $random_filename,
                        'kategori_berkas_id' => 2,
                        'status_berkas_id' => 2,
                        'path' => $dataupload['path'],
                        'tgl_upload' => now('Asia/Jakarta'),
                        'nama_file' => $dataupload['nama_file'],
                        'ext' => $dataupload['ext'],
                        'size' => $dataupload['size'],
                    ]);

                    // Simpan ID berkas ke urutan yang sesuai
                    $berkasIds[$field] = $berkas->id;
                    Log::info("Berkas dokumen pada kolom {$field} berhasil diupload.");
                }
            }

            StorageServerHelper::logout();

            $diklat->update([
                'gambar' => $berkasIds['dokumen'] ?? $diklat->gambar,
                'dokumen_diklat_1' => $berkasIds['dokumen_diklat_1'] ?? $diklat->dokumen_diklat_1,
                'dokumen_diklat_2' => $berkasIds['dokumen_diklat_2'] ?? $diklat->dokumen_diklat_2,
                'dokumen_diklat_3' => $berkasIds['dokumen_diklat_3'] ?? $diklat->dokumen_diklat_3,
                'dokumen_diklat_4' => $berkasIds['dokumen_diklat_4'] ?? $diklat->dokumen_diklat_4,
                'dokumen_diklat_5' => $berkasIds['dokumen_diklat_5'] ?? $diklat->dokumen_diklat_5,
                'nama' => $data['nama'],
                'deskripsi' => $data['deskripsi'],
                'kuota' => $data['kuota'],
                'skp' => $data['skp'],
                'lokasi' => $data['lokasi']
            ]);

            $userIds = $data['user_id'] ?? [];
            $existingUserIds = DB::table('peserta_diklats')
                ->where('diklat_id', $diklat->id)
                ->pluck('peserta')
                ->toArray();

            // Bandingkan array user_id lama dan baru (tanpa memperhatikan urutan)
            $hasUserChanged = array_diff($userIds, $existingUserIds) || array_diff($existingUserIds, $userIds);
            if ($hasUserChanged) {
                DB::table('peserta_diklats')->where('diklat_id', $diklat->id)->delete();

                // Menyimpan peserta diklat dan menentukan is_whitelist
                if ($userIds) {
                    foreach ($userIds as $userId) {
                        DB::table('peserta_diklats')->insert([
                            'diklat_id' => $diklat->id,
                            'peserta' => $userId,
                        ]);
                    }

                    $diklat->update([
                        'is_whitelist' => 1,
                        'total_peserta' => count($userIds),
                        'kuota' => count($userIds),
                    ]);
                } else {
                    $diklat->update([
                        'is_whitelist' => 0,
                        'total_peserta' => 0,
                        'kuota' => $data['kuota'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Diklat Internal '{$diklat->nama}' berhasil diperbarui.",
                'data' => $diklat,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Diklat Internal | - Error function updateInternal: ' . $e->getMessage());
            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateExternal(UpdateDiklatExternalRequest $request, $diklatId)
    {
        if (!Gate::allows('edit diklat')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $verifikatorId = Auth::id();
        $berkas = null;

        $diklat = Diklat::findOrFail($diklatId);
        // if ($diklat->status_diklat_id !== 1) {
        //     return response()->json(new WithoutDataResource(
        //         Response::HTTP_FORBIDDEN,
        //         "Hanya diklat yang memiliki status 'Menunggu Verifikasi' yang dapat dilakukan perubahan."
        //     ), Response::HTTP_FORBIDDEN);
        // }

        DB::beginTransaction();
        try {
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

            $durasi = $diklat->durasi;

            $diklat->update([
                'dokumen_eksternal' => $gambarId ?? $diklat->dokumen_eksternal,
                'nama'              => $data['nama'],
                'deskripsi'         => $data['deskripsi'],
                'lokasi'            => $data['lokasi'],
                'skp'               => $data['skp'],
                'verifikator_1'     => $verifikatorId,
                'verifikator_2'     => $verifikatorId,
            ]);

            // Perbarui peserta jika berbeda dari sebelumnya
            $existing = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta')->first();
            $newUserId = $data['user_id'];
            if ($existing !== $newUserId) {
                // ⬇️ Kurangi durasi dari peserta sebelumnya
                $this->decreaseDiklatDurationForRemovedUsers($diklat->id, [$newUserId], $durasi);

                PesertaDiklat::where('diklat_id', $diklat->id)->delete();

                PesertaDiklat::create([
                    'diklat_id' => $diklat->id,
                    'peserta'   => $data['user_id'],
                ]);

                // Update masa_diklat karyawan
                $this->increaseMasaDiklat($data['user_id'], $durasi);
            }

            $user = User::find($data['user_id']);

            DB::commit();

            return response()->json([
                'status'  => Response::HTTP_OK,
                'message' => "Diklat Eksternal '{$diklat->nama}' dari karyawan '{$user->nama}' berhasil diperbarui."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Diklat Internal | - Error function updateExternal: ' . $e->getMessage());
            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
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
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifikasiTahap1(Request $request, $diklatId)
    {
        // 1. Dapatkan ID user yang login
        $verifikatorId = Auth::id();

        // 2. Cari diklat berdasarkan ID
        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // 3. Jika pengguna bukan Super Admin, lakukan pengecekan relasi verifikasi
        if (!Auth::user()->hasRole('Super Admin')) {
            // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
            $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                ->where('modul_verifikasi', 5) // 5 adalah modul diklat
                ->where('order', 1)
                ->first();

            if (!$relasiVerifikasi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => "Anda tidak memiliki hak akses untuk verifikasi diklat internal tahap 1 dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                    'relasi_verifikasi' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            // 4. Dapatkan peserta diklat yang diverifikasi
            $userIdPeserta = $diklat->peserta_diklat->pluck('users.id')->first();

            // 5. Cocokkan user_id peserta dengan user_diverifikasi pada tabel relasi_verifikasis
            $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
            if (!is_array($userDiverifikasi)) {
                Log::warning('Kesalahan format data user diverifikasi pada verif 1 diklat internal');
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (!in_array($userIdPeserta, $userDiverifikasi)) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi diklat internal ini karena peserta tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
            }

            // 6. Validasi nilai kolom order dan status_diklat_id
            if ($relasiVerifikasi->order != 1) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Diklat internal ini tidak dalam status untuk disetujui pada tahap 1.'), Response::HTTP_BAD_REQUEST);
            }
        }

        $status_diklat_id = $diklat->status_diklat_id;

        if ($request->has('verifikasi_pertama_disetujui') && $request->verifikasi_pertama_disetujui == 1) {
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 2;
                $diklat->verifikator_1 = Auth::id();
                $diklat->alasan = null;
                $diklat->save();

                $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                $users = User::whereIn('id', $totalPeserta)->get();
                foreach ($users as $user) {
                    $this->createNotifikasiDiklatInternal_Verif1($diklat, $user, 'Disetujui');
                }

                // Kirim juga ke user_id = 1 (Super Admin)
                $superAdmin = User::find(1);
                $this->createNotifikasiDiklatInternal_Verif1($diklat, $superAdmin, 'Disetujui', true);

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 1 untuk Diklat '{$diklat->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' tidak dalam status untuk disetujui pada tahap 1."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 3;
                $diklat->verifikator_1 = Auth::id();
                $diklat->alasan = $request->input('alasan');
                $diklat->save();

                $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                $users = User::whereIn('id', $totalPeserta)->get();
                foreach ($users as $user) {
                    $this->createNotifikasiDiklatInternal_Verif1($diklat, $user, 'Ditolak');
                }

                // Kirim juga ke user_id = 1 (Super Admin)
                $superAdmin = User::find(1);
                $this->createNotifikasiDiklatInternal_Verif1($diklat, $superAdmin, 'Ditolak', true);

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
        // 1. Dapatkan ID user yang login
        $verifikatorId = Auth::id();

        // 2. Cari diklat berdasarkan ID
        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // 3. Jika pengguna bukan Super Admin, lakukan pengecekan relasi verifikasi
        if (!Auth::user()->hasRole('Super Admin')) {
            // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
            $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                ->where('modul_verifikasi', 5) // 5 adalah modul diklat
                ->where('order', 2)
                ->first();

            if (!$relasiVerifikasi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => "Anda tidak memiliki hak akses untuk verifikasi diklat internal tahap 2 dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                    'relasi_verifikasi' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            // Dapatkan peserta diklat yang diverifikasi
            $userIdPeserta = $diklat->peserta_diklat->pluck('users.id')->first();

            // Cocokkan user_id peserta dengan user_diverifikasi pada tabel relasi_verifikasis
            $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
            if (!is_array($userDiverifikasi)) {
                Log::warning('Kesalahan format data user diverifikasi pada verif 2 diklat internal');
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (!in_array($userIdPeserta, $userDiverifikasi)) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi diklat internal ini karena peserta tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
            }

            // Validasi nilai kolom order dan status_diklat_id
            if ($relasiVerifikasi->order != 2) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Diklat internal ini tidak dalam status untuk disetujui pada tahap 2.'), Response::HTTP_BAD_REQUEST);
            }
        }

        $status_diklat_id = $diklat->status_diklat_id;

        if ($request->has('verifikasi_kedua_disetujui') && $request->verifikasi_kedua_disetujui == 1) {
            if ($status_diklat_id == 2) {
                $diklat->status_diklat_id = 4;
                $diklat->verifikator_2 = $verifikatorId;
                $diklat->alasan = null;
                $diklat->save();

                // Update masa diklat karyawan
                // $pesertaDiklat = PesertaDiklat::where('diklat_id', $diklatId)->pluck('peserta');
                // if ($pesertaDiklat->isNotEmpty()) {
                //     foreach ($pesertaDiklat as $userId) {
                //         $dataKaryawan = DataKaryawan::where('user_id', $userId)->first();
                //         if ($dataKaryawan) {
                //             // Ambil daftar diklat ID untuk user
                //             $diklatIds = PesertaDiklat::where('peserta', $userId)->pluck('diklat_id');
                //             if ($diklatIds->isNotEmpty()) { // Pastikan diklatIds tidak kosong
                //                 Log::info("| Diklat | - User ID {$userId} memiliki {$diklatIds->count()} diklat.");

                //                 // Hitung total durasi
                //                 $totalDurasi = Diklat::whereIn('id', $diklatIds)
                //                     ->where('status_diklat_id', 4) // Hanya diklat dengan status 'Disetujui'
                //                     ->sum('durasi');
                //                 Log::info("| Diklat | - Total masa diklat untuk user ID {$userId} adalah {$totalDurasi} jam.");

                //                 // Update masa_diklat
                //                 $dataKaryawan->masa_diklat = $totalDurasi;
                //                 $dataKaryawan->save();
                //             } else {
                //                 Log::info("| Diklat | - User ID {$userId} tidak memiliki diklat terkait.");
                //             }
                //         } else {
                //             Log::error("Data karyawan dengan user_id {$userId} tidak ditemukan saat mencoba update masa diklat untuk diklat ID {$diklat->id}.");
                //         }
                //     }
                //     Log::info("Proses update masa diklat selesai untuk diklat ID {$diklat->id} dengan jumlah peserta {$pesertaDiklat->count()}.");
                // } else {
                //     Log::info("Tidak ada peserta untuk diklat ID {$diklat->id} saat melakukan update masa diklat.");
                // }

                $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                $users = User::whereIn('id', $totalPeserta)->get();
                foreach ($users as $user) {
                    $this->createNotifikasiDiklatInternal_Verif2($diklat, $user, 'Disetujui');
                }

                // Kirim juga ke user_id = 1 (Super Admin)
                $superAdmin = User::find(1);
                $this->createNotifikasiDiklatInternal_Verif2($diklat, $superAdmin, 'Disetujui', true);

                $message = "Verifikasi tahap 2 untuk Diklat '{$diklat->nama}' telah disetujui.";

                return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat '{$diklat->nama}' tidak dalam status untuk disetujui pada tahap 2."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_kedua_ditolak') && $request->verifikasi_kedua_ditolak == 1) {
            // Jika status_diklat_id = 2, maka bisa ditolak
            if ($status_diklat_id == 2) {
                $diklat->status_diklat_id = 5;
                $diklat->verifikator_2 = $verifikatorId;
                $diklat->alasan = $request->input('alasan');
                $diklat->save();

                $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                $users = User::whereIn('id', $totalPeserta)->get();
                foreach ($users as $user) {
                    $this->createNotifikasiDiklatInternal_Verif1($diklat, $user, 'Ditolak');
                }

                // Kirim juga ke user_id = 1 (Super Admin)
                $superAdmin = User::find(1);
                $this->createNotifikasiDiklatInternal_Verif1($diklat, $superAdmin, 'Ditolak', true);

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
        // 1. Dapatkan ID user yang login
        $verifikatorId = Auth::id();

        // 2. Cari diklat berdasarkan ID
        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // 3. Jika pengguna bukan Super Admin, lakukan pengecekan relasi verifikasi
        if (!Auth::user()->hasRole('Super Admin')) {
            // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
            $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                ->where('modul_verifikasi', 5) // 5 adalah modul diklat
                ->where('order', 3)
                ->first();
            if (!$relasiVerifikasi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => "Anda tidak memiliki hak akses untuk verifikasi diklat internal tahap publikasi sertifikat dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                    'relasi_verifikasi' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            // Pastikan order verifikasi adalah 3 untuk pembuatan sertifikat
            if ($relasiVerifikasi->order != 3) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Diklat internal ini tidak dalam status untuk dilakukan pembuatan sertifikat.'), Response::HTTP_BAD_REQUEST);
            }

            // Dapatkan peserta diklat yang diverifikasi
            $userIdPeserta = $diklat->peserta_diklat->pluck('users.id')->first();

            // Cocokkan user_id peserta dengan user_diverifikasi pada tabel relasi_verifikasis
            $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
            if (!is_array($userDiverifikasi)) {
                Log::warning('Kesalahan format data user diverifikasi pada generate sertifikat diklat internal.');
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if (!in_array($userIdPeserta, $userDiverifikasi)) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat membuat sertifikat diklat internal ini karena peserta tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
            }
        }

        if ($diklat->status_diklat_id != 4) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Anda tidak dapat membuat sertifikat '{$diklat->nama}' karena diklat belum mencapai status verifikasi tahap 2."), Response::HTTP_BAD_REQUEST);
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

                            // Refactored: update masa_diklat saat generate sertifikat
                            $diklatIds = PesertaDiklat::where('peserta', $userId)->pluck('diklat_id');
                            if ($diklatIds->isNotEmpty()) {
                                $totalDurasi = Diklat::whereIn('id', $diklatIds)
                                    ->where('status_diklat_id', 4)
                                    ->sum('durasi');

                                $dataKaryawan->masa_diklat = $totalDurasi;
                                $dataKaryawan->save();

                                Log::info("Masa diklat user_id {$userId} diupdate menjadi {$totalDurasi} jam.");
                            }
                        }
                    }
                    $diklat->certificate_published = 1;
                    $diklat->certificate_verified_by = Auth::id();
                    $diklat->save();

                    $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                    $users = User::whereIn('id', $totalPeserta)->get();
                    foreach ($users as $user) {
                        $this->createNotifikasiSertifikat($diklat, $user, 'Disetujui', false);
                    }

                    // Kirim juga ke user_id = 1 (Super Admin)
                    $superAdmin = User::find(1);
                    $this->createNotifikasiSertifikat($diklat, $superAdmin, 'Disetujui', true);
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
    public function verifikasiDiklatExternal_t1(Request $request, $diklatId)
    {
        // 1. Dapatkan ID user yang login
        $verifikatorId = Auth::id();

        // 2. Cari diklat berdasarkan ID
        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // 3. Jika user bukan Super Admin, lakukan pengecekan relasi verifikasi
        if (!Auth::user()->hasRole('Super Admin')) {
            // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
            $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                ->where('modul_verifikasi', 6) // 6 adalah modul diklat eksternal
                ->where('order', 1)
                ->first();

            if (!$relasiVerifikasi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => "Anda tidak memiliki hak akses untuk verifikasi diklat eksternal dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                    'relasi_verifikasi' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            // Dapatkan peserta diklat yang diverifikasi
            $userIdPeserta = $diklat->peserta_diklat->pluck('users.id')->first();

            // Cocokkan user_id peserta dengan user_diverifikasi pada tabel relasi_verifikasis
            $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
            if (!is_array($userDiverifikasi)) {
                Log::warning('Kesalahan format data user diverifikasi pada verif 1 diklat eksternal.');
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (!in_array($userIdPeserta, $userDiverifikasi)) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi diklat eksternal ini karena peserta tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
            }

            // Validasi nilai kolom order dan status_diklat_id
            if ($relasiVerifikasi->order != 1) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Diklat eksternal ini tidak dalam status untuk disetujui pada tahap 1.'), Response::HTTP_BAD_REQUEST);
            }
        }

        $status_diklat_id = $diklat->status_diklat_id;

        if ($request->has('verifikasi_pertama_disetujui') && $request->verifikasi_pertama_disetujui == 1) {
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 2;
                $diklat->verifikator_1 = Auth::id();
                // $diklat->durasi = $request->input('durasi');
                $diklat->save();

                $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                $users = User::whereIn('id', $totalPeserta)->get();
                foreach ($users as $user) {
                    $this->createNotifikasiDiklatEksternal_Verif1($diklat, $user, 'Disetujui');
                }

                // Kirim juga ke user_id = 1 (Super Admin)
                $superAdmin = User::find(1);
                $this->createNotifikasiDiklatEksternal_Verif1($diklat, $superAdmin, 'Disetujui', true);

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi untuk Diklat Eksternal tahap 1 '{$diklat->nama}' telah disetujui."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat Eksternal tahap 1 '{$diklat->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_pertama_ditolak') && $request->verifikasi_pertama_ditolak == 1) {
            if ($status_diklat_id == 1) {
                $diklat->status_diklat_id = 3;
                $diklat->verifikator_2 = Auth::id();
                $diklat->alasan = $request->input('alasan');
                $diklat->save();

                // Ambil total peserta dan user yang terkait
                $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                $users = User::whereIn('id', $totalPeserta)->get();

                // Buat notifikasi untuk setiap peserta
                foreach ($users as $user) {
                    $this->createNotifikasiDiklatEksternal_Verif1($diklat, $user, 'Ditolak');
                }

                // Kirim juga ke user_id = 1 (Super Admin)
                $superAdmin = User::find(1);
                $this->createNotifikasiDiklatEksternal_Verif1($diklat, $superAdmin, 'Ditolak', true);

                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 untuk Diklat Eksternal '{$diklat->nama}' telah ditolak."), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat Eksternal '{$diklat->nama}' tidak dalam status untuk ditolak pada tahap 2."), Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
        }
    }

    public function verifikasiDiklatExternal_t2(Request $request, $diklatId)
    {
        // 1. Dapatkan ID user yang login
        $verifikatorId = Auth::id();

        // 2. Cari diklat berdasarkan ID
        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // 3. Jika user bukan Super Admin, lakukan pengecekan relasi verifikasi
        if (!Auth::user()->hasRole('Super Admin')) {
            // Dapatkan relasi_verifikasis, pastikan verifikator memiliki ID user yang sama
            $relasiVerifikasi = RelasiVerifikasi::where('verifikator', $verifikatorId)
                ->where('modul_verifikasi', 6) // 6 adalah modul diklat eksternal
                ->where('order', 2)
                ->first();

            if (!$relasiVerifikasi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => "Anda tidak memiliki hak akses untuk verifikasi diklat eksternal tahap 2 dengan modul '{$relasiVerifikasi->modul_verifikasis->label}'.",
                    'relasi_verifikasi' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            // Dapatkan peserta diklat yang diverifikasi
            $userIdPeserta = $diklat->peserta_diklat->pluck('users.id')->first();

            // Cocokkan user_id peserta dengan user_diverifikasi pada tabel relasi_verifikasis
            $userDiverifikasi = $relasiVerifikasi->user_diverifikasi;
            if (!is_array($userDiverifikasi)) {
                Log::warning('Kesalahan format data user diverifikasi pada verif 1 diklat eksternal.');
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Kesalahan format data user diverifikasi.'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (!in_array($userIdPeserta, $userDiverifikasi)) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memverifikasi diklat eksternal ini karena peserta tidak ada dalam daftar verifikasi Anda.'), Response::HTTP_FORBIDDEN);
            }

            // Validasi nilai kolom order dan status_diklat_id
            if ($relasiVerifikasi->order != 2) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Diklat eksternal ini tidak dalam status untuk disetujui pada tahap 1.'), Response::HTTP_BAD_REQUEST);
            }
        }

        $status_diklat_id = $diklat->status_diklat_id;

        if ($request->has('verifikasi_kedua_disetujui') && $request->verifikasi_kedua_disetujui == 1) {
            if ($status_diklat_id == 2) {
                $diklat->status_diklat_id = 4;
                $diklat->verifikator_2 = $verifikatorId;
                $diklat->alasan = null;
                $diklat->save();

                // Update masa diklat karyawan
                $pesertaDiklat = PesertaDiklat::where('diklat_id', $diklatId)->pluck('peserta');
                if ($pesertaDiklat->isNotEmpty()) {
                    foreach ($pesertaDiklat as $userId) {
                        $dataKaryawan = DataKaryawan::where('user_id', $userId)->first();
                        if ($dataKaryawan) {
                            // Ambil daftar diklat ID untuk user
                            $diklatIds = PesertaDiklat::where('peserta', $userId)->pluck('diklat_id');
                            if ($diklatIds->isNotEmpty()) {
                                Log::info("| Diklat | - User ID {$userId} memiliki {$diklatIds->count()} diklat.");

                                // Hitung total durasi
                                $totalDurasi = Diklat::whereIn('id', $diklatIds)
                                    ->where('status_diklat_id', 4)
                                    ->sum('durasi');
                                Log::info("| Diklat | - Total masa diklat untuk user ID {$userId} adalah {$totalDurasi} jam.");

                                $dataKaryawan->masa_diklat = $totalDurasi;
                                $dataKaryawan->save();
                            } else {
                                Log::info("| Diklat | - User ID {$userId} tidak memiliki diklat terkait.");
                            }
                        } else {
                            Log::error("Data karyawan dengan user_id {$userId} tidak ditemukan saat mencoba update masa diklat untuk diklat ID {$diklat->id}.");
                        }
                    }
                    Log::info("Proses update masa diklat selesai untuk diklat ID {$diklat->id} dengan jumlah peserta {$pesertaDiklat->count()}.");
                } else {
                    Log::info("Tidak ada peserta untuk diklat ID {$diklat->id} saat melakukan update masa diklat.");
                }

                $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                $users = User::whereIn('id', $totalPeserta)->get();
                foreach ($users as $user) {
                    $this->createNotifikasiDiklatEksternal_Verif2($diklat, $user, 'Disetujui');
                }

                // Kirim juga ke user_id = 1 (Super Admin)
                $superAdmin = User::find(1);
                $this->createNotifikasiDiklatEksternal_Verif2($diklat, $superAdmin, 'Disetujui', true);

                $message = "Verifikasi tahap 2 Diklat Eksternal '{$diklat->nama}' telah disetujui.";

                return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Diklat Eksternal '{$diklat->nama}' tidak dalam status untuk disetujui pada tahap 2."), Response::HTTP_BAD_REQUEST);
            }
        } elseif ($request->has('verifikasi_kedua_ditolak') && $request->verifikasi_kedua_ditolak == 1) {
            // Jika status_diklat_id = 2, maka bisa ditolak
            if ($status_diklat_id == 2) {
                $diklat->status_diklat_id = 5;
                $diklat->verifikator_2 = $verifikatorId;
                $diklat->alasan = $request->input('alasan');
                $diklat->save();

                $totalPeserta = PesertaDiklat::where('diklat_id', $diklat->id)->pluck('peserta');
                $users = User::whereIn('id', $totalPeserta)->get();
                foreach ($users as $user) {
                    $this->createNotifikasiDiklatEksternal_Verif2($diklat, $user, 'Ditolak');
                }

                // Kirim juga ke user_id = 1 (Super Admin)
                $superAdmin = User::find(1);
                $this->createNotifikasiDiklatEksternal_Verif2($diklat, $superAdmin, 'Ditolak', true);


                return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi tahap 2 Diklat Eksternal '{$diklat->nama}' telah ditolak."), Response::HTTP_OK);
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
        $SuperAdmin = Auth::user();

        // Cek apakah user memiliki role 'Super Admin'
        if (!$SuperAdmin->hasRole('Super Admin')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $diklat = Diklat::find($diklatId);
        if (!$diklat) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        if ($diklat->kategori_diklat_id != 1) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak dapat menghapus peserta dari diklat eksternal. Silakan lakukan penolakan verifikasi untuk memungkinkan pengajuan ulang.'), Response::HTTP_BAD_REQUEST);
        }

        // Mendapatkan peserta yang terdaftar di diklat tersebut
        $peserta_diklat = PesertaDiklat::where('diklat_id', $diklatId)->pluck('peserta')->toArray();
        if (!in_array($userId, $peserta_diklat)) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data peserta diklat tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Get masa diklat peserta yang dihapus
        $durasi = $diklat->durasi;

        // Mengirim array userId selain yang dihapus
        $remainingUserIds = array_diff($peserta_diklat, [$userId]);

        // Log untuk verifikasi user yang akan dikurangi masa diklatnya
        Log::info('User ID yang tidak akan dikurangi masa diklat: ' . implode(', ', $remainingUserIds));

        // Mengurangi masa diklat peserta yang tetap ada (bukan yang dihapus)
        $this->decreaseDiklatDurationForRemovedUsers($diklatId, $remainingUserIds, $durasi);

        // Hapus peserta dari diklat
        $peserta_diklat = PesertaDiklat::where('diklat_id', $diklatId)->where('peserta', $userId);
        $user = User::find($userId);
        $userName = $user ? $user->nama : $userId;

        // Menghapus peserta dari diklat
        $peserta_diklat->delete();

        Log::info("Peserta diklat '{$userName}' berhasil dihapus dari diklat internal '{$diklat->nama}'.");

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Peserta diklat '{$userName}' berhasil dihapus dari diklat internal '{$diklat->nama}'."
        ], Response::HTTP_OK);
    }

    // Untuk assign karyawan yang lupa join via online
    public function assignDiklat(Request $request, $diklatId)
    {
        try {
            $SuperAdmin = Auth::user();

            if (!$SuperAdmin->hasRole('Super Admin')) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_FORBIDDEN,
                    'Anda tidak memiliki hak akses untuk melakukan proses ini.'
                ), Response::HTTP_FORBIDDEN);
            }

            $userIds = $request->user_id;

            if (!is_array($userIds) || count($userIds) === 0) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_BAD_REQUEST,
                    'User ID harus berupa array dan tidak boleh kosong.'
                ), Response::HTTP_BAD_REQUEST);
            }

            // Validasi: cek apakah semua user_id ada di database
            $validUserIds = User::whereIn('id', $userIds)->pluck('id')->toArray();
            $invalidUserIds = array_diff($userIds, $validUserIds);
            if (count($invalidUserIds) > 0) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_BAD_REQUEST,
                    'Ada user ID yang tidak ditemukan, pastikan user ID yang dimasukkan valid.'
                ), Response::HTTP_BAD_REQUEST);
            }

            // Validasi: pastikan belum ada user yang terdaftar di diklat
            $alreadyExistIds = PesertaDiklat::where('diklat_id', $diklatId)
                ->whereIn('peserta', $userIds)
                ->pluck('peserta')
                ->toArray();
            if (count($alreadyExistIds) > 0) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_CONFLICT,
                    'Ada user ID yang sudah terdaftar di diklat tersebut, pastikan user ID yang dimasukkan valid.'
                ), Response::HTTP_CONFLICT);
            }

            $diklat = Diklat::find($diklatId);
            if (!$diklat) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_NOT_FOUND,
                    'Data diklat tidak ditemukan.'
                ), Response::HTTP_NOT_FOUND);
            }

            if ($diklat->kategori_diklat_id != 1) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_BAD_REQUEST,
                    'Peserta hanya dapat ditambahkan ke diklat internal.'
                ), Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();

            $addedNames = [];
            $now = now('Asia/Jakarta');

            foreach ($validUserIds as $userId) {
                $exists = PesertaDiklat::where('diklat_id', $diklatId)
                    ->where('peserta', $userId)
                    ->exists();

                if ($exists) continue;

                PesertaDiklat::create([
                    'diklat_id'  => $diklatId,
                    'peserta'    => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $user = User::find($userId);
                $addedNames[] = $user->nama;

                // Tambahkan masa diklat untuk peserta yang baru ditambahkan
                $this->increaseMasaDiklat($userId, $diklat->durasi);
            }

            $jumlahPesertaBaru = count($addedNames);
            $diklat->total_peserta = PesertaDiklat::where('diklat_id', $diklatId)->count();

            if (!is_null($diklat->kuota)) {
                $diklat->kuota += $jumlahPesertaBaru;
            }

            $diklat->save();
            DB::commit();

            return response()->json([
                'status'  => Response::HTTP_OK,
                'message' => "Sebanyak {$jumlahPesertaBaru} peserta berhasil ditambahkan ke Diklat Internal '{$diklat->nama}'."
                // 'message' => "Sebanyak {$jumlahPesertaBaru} peserta berhasil ditambahkan ke diklat '{$diklat->nama}': " . implode(', ', $addedNames)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('| Diklat Internal | - Error function assignDiklat: ' . $e->getMessage());
            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Mengurangi masa diklat peserta saat edit diklat external jika peserta diganti
    private function decreaseDiklatDurationForRemovedUsers(int $diklatId, array $newUserIds, int $durasi): void
    {
        $oldUserIds = DB::table('peserta_diklats')
            ->where('diklat_id', $diklatId)
            ->pluck('peserta')
            ->toArray();

        // Log Old User IDs
        Log::info('Old User IDs: ' . implode(', ', $oldUserIds));
        Log::info('New User IDs: ' . implode(', ', $newUserIds));

        $removedUserIds = array_diff($oldUserIds, $newUserIds);

        // Log Removed User IDs
        Log::info('Removed User IDs: ' . implode(', ', $removedUserIds));

        if (empty($removedUserIds)) return;

        foreach ($removedUserIds as $userId) {
            // Ambil masa_diklat saat ini
            $current = DB::table('data_karyawans')
                ->where('user_id', $userId)
                ->value('masa_diklat');

            if ($current === null) continue;

            $newDuration = $current - $durasi;

            if ($newDuration <= 0) {
                DB::table('data_karyawans')
                    ->where('user_id', $userId)
                    ->update(['masa_diklat' => null]);
            } else {
                DB::table('data_karyawans')
                    ->where('user_id', $userId)
                    ->update(['masa_diklat' => $newDuration]);
            }
        }
    }

    // Menambahkan masa diklat
    private function increaseMasaDiklat(int $userId, int $durasi): void
    {
        $current = DB::table('data_karyawans')
            ->where('user_id', $userId)
            ->value('masa_diklat');

        if ($current === null) {
            DB::table('data_karyawans')
                ->where('user_id', $userId)
                ->update(['masa_diklat' => $durasi]);
        } else {
            DB::table('data_karyawans')
                ->where('user_id', $userId)
                ->update(['masa_diklat' => $current + $durasi]);
        }
    }

    private function createNotifikasiDiklat($diklat, $userIds = null)
    {
        try {
            $users = is_array($userIds) && count($userIds) > 0
                ? User::whereIn('id', $userIds)->get()
                : User::all();

            foreach ($users as $user) {
                $message = "Diklat Internal baru '{$diklat->nama}' telah ditambahkan pada tanggal mulai {$diklat->tgl_mulai}.";
                Notifikasi::create([
                    'kategori_notifikasi_id' => 13,
                    'user_id' => $user->id,
                    'message' => $message,
                    'is_read' => false,
                    'is_verifikasi' => true,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }

            Log::info('Notifikasi diklat internal telah berhasil dikirimkan ke pengguna yang bersangkutan.');
        } catch (\Exception $e) {
            Log::error('| Notifikasi Diklat | - Error saat mengirim notifikasi: ' . $e->getMessage());
        }
    }

    private function createNotifikasiDiklatExternal($diklat, $userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('Karyawan tidak ditemukan.');
            }

            $message = "Diklat External '{$diklat->nama}' telah ditambahkan pada tanggal {$diklat->tgl_mulai}.";
            $messageSuperAdmin = "Notifikasi untuk Super Admin: Diklat External '{$diklat->nama}' telah ditambahkan untuk karyawan '{$user->nama}'.";

            $userIds = [$userId, 1];

            foreach ($userIds as $recipientId) {
                $messageToSend = $recipientId === 1 ? $messageSuperAdmin : $message;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 14,
                    'user_id' => $recipientId,
                    'message' => $messageToSend,
                    'is_read' => false,
                    'is_verifikasi' => true,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }

            Log::info("Notifikasi diklat external telah berhasil dikirimkan ke pengguna '{$user->nama}'.");
        } catch (\Exception $e) {
            Log::error('| Notifikasi Diklat | - Error saat mengirim notifikasi: ' . $e->getMessage());
        }
    }

    private function createNotifikasiDiklatInternal_Verif1($diklat, $user, $status, $isSuperAdmin = false)
    {
        try {
            $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
            $message = $isSuperAdmin
                ? "Notifikasi untuk Super Admin: Verifikasi tahap 1 untuk Diklat Internal '{$diklat->nama}' dari '{$user->nama}' telah {$statusText}."
                : "'{$user->nama}', Verifikasi tahap 1 untuk Diklat Internal '{$diklat->nama}' telah {$statusText}.";

            // Buat notifikasi untuk user atau Super Admin
            Notifikasi::create([
                'kategori_notifikasi_id' => 13,
                'user_id' => $user->id,
                'message' => $message,
                'is_read' => false,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Log::info('Notifikasi untuk peserta diklat ' . $user->nama . ' berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('| Diklat Eksternal | - Error saat membuat notifikasi tahap 1: ' . $e->getMessage());
        }
    }

    private function createNotifikasiDiklatInternal_Verif2($diklat, $user, $status, $isSuperAdmin = false)
    {
        try {
            $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
            $message = $isSuperAdmin
                ? "Notifikasi untuk Super Admin: Verifikasi tahap 2 untuk Diklat Internal '{$diklat->nama}' dari '{$user->nama}' telah {$statusText}."
                : "'{$user->nama}', Verifikasi tahap 2 untuk Diklat Internal '{$diklat->nama}' telah {$statusText}.";

            // Buat notifikasi untuk user atau Super Admin
            Notifikasi::create([
                'kategori_notifikasi_id' => 13,
                'user_id' => $user->id,
                'message' => $message,
                'is_read' => false,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Log::info('Notifikasi untuk peserta diklat ' . $user->nama . ' berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('| Diklat Eksternal | - Error saat membuat notifikasi tahap 1: ' . $e->getMessage());
        }
    }

    private function createNotifikasiDiklatEksternal_Verif1($diklat, $user, $status, $isSuperAdmin = false)
    {
        try {
            $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
            $message = $isSuperAdmin
                ? "Notifikasi untuk Super Admin: Verifikasi tahap 1 untuk Diklat Eksternal '{$diklat->nama}' dari '{$user->nama}' telah {$statusText}."
                : "'{$diklat->nama}', Verifikasi tahap 1 untuk Diklat Eksternal '{$diklat->nama}' telah {$statusText}.";

            // Buat notifikasi untuk user atau Super Admin
            Notifikasi::create([
                'kategori_notifikasi_id' => 14,
                'user_id' => $user->id,
                'message' => $message,
                'is_read' => false,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Log::info('Notifikasi untuk peserta diklat ' . $user->nama . ' berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('| Diklat Eksternal | - Error saat membuat notifikasi tahap 1: ' . $e->getMessage());
        }
    }

    private function createNotifikasiDiklatEksternal_Verif2($diklat, $user, $status, $isSuperAdmin = false)
    {
        try {
            $statusText = $status === 'Disetujui' ? 'Disetujui' : 'Ditolak';
            $message = $isSuperAdmin
                ? "Notifikasi untuk Super Admin: Verifikasi tahap 2 untuk Diklat Eksternal '{$diklat->nama}' dari '{$user->nama}' telah {$statusText}."
                : "'{$diklat->nama}', Verifikasi tahap 2 untuk Diklat Eksternal '{$diklat->nama}' telah {$statusText}.";

            // Buat notifikasi untuk user atau Super Admin
            Notifikasi::create([
                'kategori_notifikasi_id' => 14,
                'user_id' => $user->id,
                'message' => $message,
                'is_read' => false,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Log::info('Notifikasi untuk peserta diklat ' . $user->nama . ' berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('| Diklat Eksternal | - Error saat membuat notifikasi tahap 1: ' . $e->getMessage());
        }
    }

    private function createNotifikasiSertifikat($diklat, $user, $isSuperAdmin = false)
    {
        try {
            $message = $isSuperAdmin
                ? "Notifikasi untuk Super Admin: Sertifikat karyawan '{$user->nama}' untuk Diklat '{$diklat->nama}' telah berhasil dibuat dan dipublikasikan."
                : "'{$user->nama}', Sertifikat untuk Diklat '{$diklat->nama}' telah berhasil dibuat dan dipublikasikan.";

            // Buat notifikasi untuk user atau Super Admin
            Notifikasi::create([
                'kategori_notifikasi_id' => 4,
                'user_id' => $user->id,
                'message' => $message,
                'is_read' => false,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Log::info('Notifikasi untuk peserta diklat ' . $user->nama . ' berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('| Generate Sertifikat | - Error saat membuat notifikasi: ' . $e->getMessage());
        }
    }
}
