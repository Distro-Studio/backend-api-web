<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Exception;
use App\Models\User;
use App\Models\Berkas;
use App\Models\TrackRecord;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\TransferKaryawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Karyawan\TransferExport;
use App\Models\KategoriTransferKaryawan;
use App\Jobs\EmailNotification\TransferEmailJob;
use App\Http\Requests\StoreTransferKaryawanRequest;
use App\Http\Requests\UpdateTransferKaryawanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Karyawan\TransferKaryawanResource;
use Carbon\Carbon;

class DataTransferKaryawanController extends Controller
{
    public function getAllKategoriTransfer()
    {
        if (!Gate::allows('view transferKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $tipe_transfer = KategoriTransferKaryawan::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all tipe transfer for dropdown',
            'data' => $tipe_transfer
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('view transferKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $transfer = TransferKaryawan::query()->orderBy('created_at', 'desc');

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $transfer->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $transfer->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $transfer->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
                if (is_array($statusKaryawan)) {
                    $query->whereIn('id', $statusKaryawan);
                } else {
                    $query->where('id', '=', $statusKaryawan);
                }
            });
        }

        if (isset($filters['masa_kerja'])) {
            $masaKerja = $filters['masa_kerja'];
            $currentDate = Carbon::now('Asia/Jakarta');
            if (is_array($masaKerja)) {
                $transfer->whereHas('users.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $transfer->whereHas('users.data_karyawans', function ($query) use ($bulan, $currentDate) {
                    $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $transfer->whereHas('users', function ($query) use ($statusAktif) {
                if (is_array($statusAktif)) {
                    $query->whereIn('status_aktif', $statusAktif);
                } else {
                    $query->where('status_aktif', '=', $statusAktif);
                }
            });
        }

        if (isset($filters['tgl_masuk'])) {
            $tglMasuk = $filters['tgl_masuk'];
            if (is_array($tglMasuk)) {
                $transfer->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->whereIn('tgl_masuk', $tglMasuk);
                });
            } else {
                $transfer->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->where('tgl_masuk', $tglMasuk);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $transfer->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
                if (is_array($namaAgama)) {
                    $query->whereIn('id', $namaAgama);
                } else {
                    $query->where('id', '=', $namaAgama);
                }
            });
        }

        if (isset($filters['jenis_kelamin'])) {
            $jenisKelamin = $filters['jenis_kelamin'];
            if (is_array($jenisKelamin)) {
                $transfer->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $transfer->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $transfer->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
                }
            });
        }

        if (isset($filters['jenis_karyawan'])) {
            $jenisKaryawan = $filters['jenis_karyawan'];
            if (is_array($jenisKaryawan)) {
                $transfer->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $transfer->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        if (isset($filters['jenis_kompetensi'])) {
            $jenisKaryawan = $filters['jenis_kompetensi'];
            if (is_array($jenisKaryawan)) {
                $transfer->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_kompetensi', $jk);
                        }
                    });
                });
            } else {
                $transfer->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_kompetensi', $jenisKaryawan);
                });
            }
        }

        if (isset($filters['kategori_transfer'])) {
            $namaTransferKategori = $filters['kategori_transfer'];
            $transfer->whereHas('kategori_transfer_karyawans', function ($query) use ($namaTransferKategori) {
                if (is_array($namaTransferKategori)) {
                    $query->whereIn('id', $namaTransferKategori);
                } else {
                    $query->where('id', '=', $namaTransferKategori);
                }
            });
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $transfer->where(function ($query) use ($searchTerm) {
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($limit == 0) {
            $dataTransfer = $transfer->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataTransfer = $transfer->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataTransfer->url(1),
                    'last' => $dataTransfer->url($dataTransfer->lastPage()),
                    'prev' => $dataTransfer->previousPageUrl(),
                    'next' => $dataTransfer->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataTransfer->currentPage(),
                    'last_page' => $dataTransfer->lastPage(),
                    'per_page' => $dataTransfer->perPage(),
                    'total' => $dataTransfer->total(),
                ]
            ];
        }

        if ($dataTransfer->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data transfer karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataTransfer->map(function ($transfer) {
            $user = $transfer->users;
            $data_karyawan = $user->data_karyawans;
            // $role = $user->roles->first();
            return [
                'id' => $transfer->id,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email_verified_at' => $user->email_verified_at,
                    'data_karyawan_id' => $user->data_karyawan_id,
                    'foto_profil' => $user->foto_profil,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'nik' => $data_karyawan->nik,
                'kategori_transfer' => $transfer->kategori_transfer_karyawans,
                'tgl_pengajuan' => $transfer->created_at,
                'tgl_mulai' => $transfer->tgl_mulai,
                'unit_kerja_asal' => $transfer->unit_kerja_asals,
                'unit_kerja_tujuan' => $transfer->unit_kerja_tujuans,
                'jabatan_asal' => $transfer->jabatan_asals,
                'jabatan_tujuan' => $transfer->jabatan_tujuans,
                'kelompok_gaji_asal' => $transfer->kelompok_gaji_asals,
                'kelompok_gaji_tujuan' => $transfer->kelompok_gaji_tujuans,
                'role_asal' => $transfer->role_asals,
                'role_tujuan' => $transfer->role_tujuans,
                'alasan' => $transfer->alasan,
                'dokumen' => env('STORAGE_SERVER_DOMAIN') . $transfer->dokumen,
                'created_at' => $transfer->created_at,
                'updated_at' => $transfer->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data transfer karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreTransferKaryawanRequest $request)
    {
        if (!Gate::allows('create transferKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // Validasi tanggal mulai tidak boleh hari ini, H+1, atau hari yang sudah terlewat
        $tgl_mulai = Carbon::createFromFormat('d-m-Y', $data['tgl_mulai'])->startOfDay();
        $today = Carbon::today('Asia/Jakarta');

        if ($tgl_mulai->lte($today->addDay(2))) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal mulai hanya diperbolehkan untuk 2 hari kedepan.'), Response::HTTP_BAD_REQUEST);
        }

        // with server
        DB::beginTransaction();
        try {
            // Ambil data user dan relasinya
            $user = User::with('data_karyawans.unit_kerjas', 'data_karyawans.jabatans', 'data_karyawans.kelompok_gajis', 'roles')->find($data['user_id']);
            if (!$user) {
                throw new Exception('Pengguna tidak ditemukan.');
            }

            // Ambil unit kerja dan jabatan asal dari user yang dipilih
            $data['unit_kerja_asal'] = $user->data_karyawans->unit_kerjas->id ?? $data['unit_kerja_asal'];
            $data['jabatan_asal'] = $user->data_karyawans->jabatans->id ?? $data['jabatan_asal'];
            $data['kelompok_gaji_asal'] = $user->data_karyawans->kelompok_gajis->id ?? $data['kelompok_gaji_asal'];
            $data['role_asal'] = $user->roles->first()->id ?? $data['role_asal'];

            if (is_null($data['unit_kerja_asal']) || is_null($data['jabatan_asal'])) {
                throw new Exception('Unit kerja atau jabatan asal tidak ditemukan untuk pengguna ini.');
            }

            if ($request->hasFile('dokumen')) {
                StorageServerHelper::login();

                $file = $request->file('dokumen');
                $random_filename = Str::random(20);
                $dataupload = StorageServerHelper::uploadToServer($request, $random_filename);
                $data['dokumen'] = $dataupload['path'];

                $berkas = Berkas::create([
                    'user_id' => $data['user_id'],
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
                Log::info('Berkas Transfer ' . $user->nama . ' berhasil di upload.');
                StorageServerHelper::logout();

                if (!$berkas) {
                    throw new Exception('Berkas gagal di upload');
                }
            }

            $transfer = TransferKaryawan::create($data);

            $users = $transfer->users;
            $unit_kerja_asals = $transfer->unit_kerja_asals->nama_unit ?? $user->data_karyawans->unit_kerjas->nama_unit;
            $unit_kerja_tujuans = $transfer->unit_kerja_tujuans->nama_unit ?? $unit_kerja_asals;
            $jabatan_asals = $transfer->jabatan_asals->nama_jabatan ?? $user->data_karyawans->jabatans->nama_jabatan;
            $jabatan_tujuans = $transfer->jabatan_tujuans->nama_jabatan ?? $jabatan_asals;
            $kelompok_gaji_asals = $transfer->kelompok_gaji_asals->nama_kelompok ?? $user->data_karyawans->kelompok_gajis->nama_kelompok;
            $kelompok_gaji_tujuans = $transfer->kelompok_gaji_tujuans->nama_kelompok ?? $kelompok_gaji_asals;
            $role_tujuans = $users->role_tujuans->name ?? $user->roles->first()->name;
            $alasan = $transfer->alasan;
            $tgl_mulai = $transfer->tgl_mulai;

            // Create track record based on kategori_transfer_id
            $kategori_record_id = ($data['kategori_transfer_id'] == 1) ? 3 : 2;
            TrackRecord::create([
                'user_id' => $users->id,
                'tgl_masuk' => $users->data_karyawans->tgl_masuk,
                'tgl_keluar' => $users->data_karyawans->tgl_keluar,
                'kategori_record_id' => $kategori_record_id,
            ]);

            $details = [
                'nama' => $users->nama,
                'email' => $users->data_karyawans->email,
                'unit_kerja_asals' => $unit_kerja_asals,
                'unit_kerja_tujuans' => $unit_kerja_tujuans,
                'jabatan_asals' => $jabatan_asals,
                'jabatan_tujuans' => $jabatan_tujuans,
                'kelompok_gaji_asals' => $kelompok_gaji_asals,
                'kelompok_gaji_tujuans' => $kelompok_gaji_tujuans,
                'role_tujuans' => $role_tujuans,
                'alasan' => $alasan,
                'tgl_mulai' => RandomHelper::convertToDateTimeString($tgl_mulai),
            ];

            // if ($request->has('beri_tahu_manajer_direktur') && $request->beri_tahu_manajer_direktur == 1) {
            //     TransferEmailJob::dispatch('manager@example.com', ['direktur@example.com'], $details);
            // }
            if ($request->has('beri_tahu_karyawan') && $request->beri_tahu_karyawan == 1) {
                TransferEmailJob::dispatch($users->data_karyawans->email, [], $details);
            }

            DB::commit();

            return response()->json(new TransferKaryawanResource(Response::HTTP_OK, "Berhasil melakukan transfer karyawan '{$users->nama}'.", $transfer), Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error: ' . $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(UpdateTransferKaryawanRequest $request, $id)
    {
        if (!Gate::allows('edit transferKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Langkah 1: Dapatkan data transfer berdasarkan id
        $transfer = TransferKaryawan::find($id);
        if (!$transfer) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data transfer tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Langkah 2: Ambil kolom tgl_mulai
        $tgl_mulai_db = Carbon::createFromFormat('d-m-Y', $transfer->tgl_mulai)->startOfDay();
        $today = Carbon::today('Asia/Jakarta');
        if ($tgl_mulai_db->lt($today)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Transfer karyawan tidak dapat diperbarui, karena tanggal mulai sudah terlewat atau hari ini.'), Response::HTTP_BAD_REQUEST);
        }

        $data = $request->validated();

        if (isset($data['tgl_mulai'])) {
            $tgl_mulai_input = Carbon::createFromFormat('d-m-Y', $data['tgl_mulai'])->startOfDay();
            if ($tgl_mulai_input->lt($today)) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Transfer karyawan tidak dapat diperbarui, karena tanggal mulai baru tidak valid (sudah terlewat).'), Response::HTTP_BAD_REQUEST);
            } else if ($tgl_mulai_input->lte($today->addDay(2))) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tanggal mulai hanya diperbolehkan untuk 2 hari kedepan.'), Response::HTTP_BAD_REQUEST);
            }
        }

        DB::beginTransaction();
        try {
            $user = User::with('data_karyawans.unit_kerjas', 'data_karyawans.jabatans', 'data_karyawans.kelompok_gajis', 'roles')->find($transfer->user_id);
            if (!$user) {
                throw new Exception('Pengguna tidak ditemukan.');
            }

            // Ambil unit kerja dan jabatan asal dari user yang dipilih
            $data['unit_kerja_asal'] = $user->data_karyawans->unit_kerjas->id ?? $data['unit_kerja_asal'];
            $data['jabatan_asal'] = $user->data_karyawans->jabatans->id ?? $data['jabatan_asal'];
            $data['kelompok_gaji_asal'] = $user->data_karyawans->kelompok_gajis->id ?? $data['kelompok_gaji_asal'];
            $data['role_asal'] = $user->roles->first()->id ?? $data['role_asal'];

            if (is_null($data['unit_kerja_asal']) || is_null($data['jabatan_asal'])) {
                throw new Exception('Unit kerja atau jabatan asal tidak ditemukan untuk pengguna ini.');
            }

            if ($request->hasFile('dokumen')) {
                // Upload file using helper
                StorageServerHelper::login();

                $file = $request->file('dokumen');
                $random_filename = Str::random(20);
                $dataupload = StorageServerHelper::uploadToServer($request, $random_filename);
                $data['dokumen'] = $dataupload['path'];

                $berkas = Berkas::updateOrCreate(
                    [
                        'user_id' => $transfer->user_id,
                        'file_id' => $dataupload['id_file']['id'],
                        'nama' => $random_filename,
                        'kategori_berkas_id' => 2, // umum
                        'status_berkas_id' => 2,
                        'path' => $dataupload['path'],
                        'tgl_upload' => now(),
                        'nama_file' => $dataupload['nama_file'],
                        'ext' => $dataupload['ext'],
                        'size' => $dataupload['size'],
                    ]
                );
                Log::info('Berkas Transfer ' . $user->nama . ' berhasil di diperbarui.');

                StorageServerHelper::logout();

                if (!$berkas) {
                    throw new Exception('Berkas gagal di diperbarui');
                }
            } else if (is_string($request->input('dokumen'))) {
                unset($data['dokumen']);
            }

            $transfer->update($data);

            $users = $transfer->users;
            $unit_kerja_asals = $transfer->unit_kerja_asals->nama_unit ?? $user->data_karyawans->unit_kerjas->nama_unit;
            $unit_kerja_tujuans = $transfer->unit_kerja_tujuans->nama_unit ?? $unit_kerja_asals;
            $jabatan_asals = $transfer->jabatan_asals->nama_jabatan ?? $user->data_karyawans->jabatans->nama_jabatan;
            $jabatan_tujuans = $transfer->jabatan_tujuans->nama_jabatan ?? $jabatan_asals;
            $kelompok_gaji_asals = $transfer->kelompok_gaji_asals->nama_kelompok ?? $user->data_karyawans->kelompok_gajis->nama_kelompok;
            $kelompok_gaji_tujuans = $transfer->kelompok_gaji_tujuans->nama_kelompok ?? $kelompok_gaji_asals;
            $role_tujuans = $users->role_tujuans->name ?? $user->roles->first()->name;
            $alasan = $transfer->alasan;
            $tgl_mulai = $transfer->tgl_mulai;

            // $kategori_record_id = ($data['kategori_transfer_id'] == 1) ? 3 : 2;
            // TrackRecord::create([
            //     'user_id' => $transfer->user_id,
            //     'tgl_masuk' => $users->data_karyawans->tgl_masuk,
            //     'tgl_keluar' => $users->data_karyawans->tgl_keluar,
            //     'kategori_record_id' => $kategori_record_id,
            // ]);

            $details = [
                'nama' => $user->nama,
                'email' => $user->data_karyawans->email,
                'unit_kerja_asals' => $unit_kerja_asals,
                'unit_kerja_tujuans' => $unit_kerja_tujuans,
                'jabatan_asals' => $jabatan_asals,
                'jabatan_tujuans' => $jabatan_tujuans,
                'kelompok_gaji_asals' => $kelompok_gaji_asals,
                'kelompok_gaji_tujuans' => $kelompok_gaji_tujuans,
                'role_tujuans' => $role_tujuans,
                'alasan' => $alasan,
                'tgl_mulai' => $tgl_mulai,
            ];

            // if ($request->has('beri_tahu_manajer_direktur') && $request->beri_tahu_manajer_direktur == 1) {
            //     TransferEmailJob::dispatch('manager@example.com', ['direktur@example.com'], $details);
            // }
            if ($request->has('beri_tahu_karyawan') && $request->beri_tahu_karyawan == 1) {
                $karyawanEmail = $users->data_karyawans->email;
                if (!is_null($karyawanEmail)) {
                    TransferEmailJob::dispatch($karyawanEmail, [], $details);
                    Log::info("Email pemberitahuan dikirim ke karyawan dengan email: {$karyawanEmail}");
                } else {
                    Log::warning("Email pemberitahuan tidak dikirim karena email karyawan null untuk user_id: {$users->id}");
                }
            }

            DB::commit();

            return response()->json(new TransferKaryawanResource(Response::HTTP_OK, "Berhasil memperbarui transfer karyawan '{$users->nama}'.", $transfer), Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error: ' . $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportTransferKaryawan()
    {
        if (!Gate::allows('export transferKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataTransfer = TransferKaryawan::all();
        if ($dataTransfer->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data transfer karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new TransferExport(), 'transfer-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // private function updateTukarRelasiTransfer($request, $transferKaryawanId)
    // {
    //     $data = $request->validated();

    //     $relasi_verifikasi_id = $request->input('master_relasi_id');
    //     $verification = RelasiVerifikasi::whereNull('deleted_at')->find($relasi_verifikasi_id); // pastikan relasi verifikasi belum dihapus
    //     if (!$verification) {
    //         return [
    //             'status' => Response::HTTP_NOT_FOUND,
    //             'message' => 'Data hak verifikasi terkait tidak ditemukan atau sedang dihapus.',
    //         ];
    //     }

    //     $user_diverifikasi = array_map('intval', $request->input('user_diverifikasi', []));
    //     $users = User::whereIn('id', $user_diverifikasi)->get();
    //     $foundUserIds = $users->pluck('id')->toArray();
    //     $invalidUserIds = array_diff($user_diverifikasi, $foundUserIds);
    //     if (!empty($invalidUserIds)) {
    //         DB::rollBack();
    //         Log::error('User ID ' . implode(', ', $invalidUserIds) . ' tidak ditemukan atau tidak valid saat create master verifikasi.');
    //         return response()->json([
    //             'status' => Response::HTTP_BAD_REQUEST,
    //             'message' => 'Tidak dapat melanjutkan proses. Terdapat karyawan yang tidak valid.',
    //         ], Response::HTTP_BAD_REQUEST);
    //     }

    //     $modulVerifikasi = ModulVerifikasi::find($data['modul_verifikasi']);
    //     if (!$modulVerifikasi) {
    //         return response()->json([
    //             'status' => Response::HTTP_NOT_FOUND,
    //             'message' => 'Modul verifikasi tidak ditemukan.',
    //         ], Response::HTTP_NOT_FOUND);
    //     }
    //     if ($data['order'] > $modulVerifikasi->max_order) {
    //         return response()->json([
    //             'status' => Response::HTTP_BAD_REQUEST,
    //             'message' => "Order yang diisi tidak boleh lebih dari {$modulVerifikasi->max_order}.",
    //         ], Response::HTTP_BAD_REQUEST);
    //     }

    //     $existingVerification = RelasiVerifikasi::where('verifikator', $data['verifikator'])
    //         ->where('order', $data['order'])
    //         ->where('modul_verifikasi', $data['modul_verifikasi'])
    //         ->whereNull('deleted_at')
    //         ->first();
    //     if ($existingVerification) {
    //         $existingVerifikator = User::find($data['verifikator']);
    //         $labelModulVerifikasi = $modulVerifikasi->label;
    //         return response()->json([
    //             'status' => Response::HTTP_CONFLICT,
    //             'message' => "Kombinasi verifikator, order, dan modul verifikasi sudah ada. Verifikator '{$existingVerifikator->nama}' dengan level verifikasi '{$data['order']}' dan modul verifikasi '{$labelModulVerifikasi}' sudah terdaftar.",
    //         ], Response::HTTP_CONFLICT);
    //     }

    //     $verification = TransferRelasiVerifikasi::create([
    //         'transfer_karyawan_id' => $transferKaryawanId,
    //         'master_relasi_id' => $relasi_verifikasi_id,
    //         'nama' => $data['nama'],
    //         'verifikator' => $data['verifikator'],
    //         'modul_verifikasi' => $data['modul_verifikasi'],
    //         'order' => $data['order'],
    //         'user_diverifikasi' => $user_diverifikasi,
    //         'updated_at' => now('Asia/Jakarta'),
    //     ]);
    //     $formattedData = $this->formatData(collect([$verification]))->first();

    //     return [
    //         'status' => Response::HTTP_CREATED,
    //         'message' => "Data master verifikasi '{$verification->nama}' berhasil dibuat.",
    //         'data' => $formattedData,
    //     ];
    // }

    // protected function formatData(Collection $collection)
    // {
    //     return $collection->transform(function ($verification) {
    //         $userIds = $verification->user_diverifikasi;
    //         $diverifiedUsers = User::whereIn('id', $userIds)->get();
    //         $formattedDiverifiedUsers = $diverifiedUsers->map(function ($user) {
    //             return [
    //                 'id' => $user->id,
    //                 'nama' => $user->nama,
    //                 'username' => $user->username,
    //                 'email_verified_at' => $user->email_verified_at,
    //                 'data_karyawan_id' => $user->data_karyawan_id,
    //                 'foto_profil' => $user->foto_profil,
    //                 'data_completion_step' => $user->data_completion_step,
    //                 'status_aktif' => $user->status_aktif,
    //                 'created_at' => $user->created_at,
    //                 'updated_at' => $user->updated_at
    //             ];
    //         });
    //         return [
    //             'id' => $verification->id,
    //             'name' => $verification->nama,
    //             'verifikator' => [
    //                 'id' => $verification->users->id,
    //                 'nama' => $verification->users->nama,
    //                 'username' => $verification->users->username,
    //                 'email_verified_at' => $verification->users->email_verified_at,
    //                 'data_karyawan_id' => $verification->users->data_karyawan_id,
    //                 'foto_profil' => $verification->users->foto_profil,
    //                 'data_completion_step' => $verification->users->data_completion_step,
    //                 'status_aktif' => $verification->users->status_aktif,
    //                 'created_at' => $verification->users->created_at,
    //                 'updated_at' => $verification->users->updated_at
    //             ],
    //             'modul_verifikasi' => $verification->modul_verifikasis,
    //             'order' => $verification->order,
    //             'user_diverifikasi' => $formattedDiverifiedUsers,
    //             'created_at' => $verification->created_at,
    //             'updated_at' => $verification->updated_at,
    //             'deleted_at' => $verification->deleted_at
    //         ];
    //     });
    // }
}
