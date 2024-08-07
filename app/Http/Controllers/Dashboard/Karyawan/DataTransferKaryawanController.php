<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Berkas;
use App\Models\TrackRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\KategoriBerkas;
use App\Models\TransferKaryawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Karyawan\TransferExport;
use App\Helpers\RandomHelper;
use App\Models\KategoriTransferKaryawan;
use App\Jobs\EmailNotification\TransferEmailJob;
use App\Http\Requests\StoreTransferKaryawanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Karyawan\TransferKaryawanResource;
use Illuminate\Support\Facades\Auth;

class DataTransferKaryawanController extends Controller
{
    public function getAllKategoriTransfer()
    {
        if (!Gate::allows('view dataKaryawan')) {
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
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $transfer = TransferKaryawan::query();

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
            if (is_array($masaKerja)) {
                $transfer->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $transfer->whereHas('users.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
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
                $convertedDates = array_map([RandomHelper::class, 'convertToDateString'], $tglMasuk);
                $transfer->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $transfer->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
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
            $transfer->whereHas('users.data_karyawans.pendidikan_terakhir', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
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
            $role = $user->roles->first();
            return [
                'id' => $transfer->id,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email_verified_at' => $user->email_verified_at,
                    'data_karyawan_id' => $user->data_karyawan_id,
                    'foto_profil' => $user->foto_profil,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'deskripsi' => $role->deskripsi,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at
                ], // role_id
                'nik' => $data_karyawan->nik,
                'kategori_transfer' => $transfer->kategori_transfer_karyawans,
                'tgl_pengajuan' => $transfer->created_at,
                'tgl_mulai' => $transfer->tgl_mulai,
                'unit_kerja_asal' => $transfer->unit_kerja_asals,
                'unit_kerja_tujuan' => $transfer->unit_kerja_tujuans,
                'jabatan_asal' => $transfer->jabatan_asals,
                'jabatan_tujuan' => $transfer->jabatan_tujuans,
                'alasan' => $transfer->alasan,
                'dokumen' => $transfer->dokumen,
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
        if (!Gate::allows('create dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        // with server
        DB::beginTransaction();
        try {
            // if ($request->hasFile('dokumen')) {
            //     // Fetch user information
            //     $user = User::find($data['user_id']);
            //     if (!$user) {
            //         throw new Exception('Pengguna tidak ditemukan.');
            //     }

            //     // Upload file using helper
            //     $dataupload = StorageSeverHelper::uploadToServer($request, 'Check in - ' . Auth::user()->nama);
            //     // $dataupload = StorageSeverHelper::uploadToServer($request);
            //     $data['dokumen'] = $dataupload['path'];

            //     // Fetch kategori_berkas for 'System'
            //     $kategoriBerkas = KategoriBerkas::where('label', 'System')->first();
            //     if (!$kategoriBerkas) {
            //         throw new Exception('Kategori berkas tidak ditemukan.');
            //     }

            //     // Store in 'berkas' table on your server
            //     $berkas = Berkas::create([
            //         'user_id' => $data['user_id'],
            //         'file_id' => $dataupload['id_file']['id'],
            //         'nama' => 'Berkas Transfer - ' . $user->nama,
            //         'kategori_berkas_id' => $kategoriBerkas->id,
            //         'path' => $dataupload['path'],
            //         'tgl_upload' => now(),
            //         'nama_file' => $dataupload['nama_file'],
            //         'ext' => $dataupload['ext'],
            //         'size' => $dataupload['size'],
            //     ]);
            //     Log::info('Berkas Transfer ' . $user->nama . ' berhasil di upload');

            //     if (!$berkas) {
            //         throw new Exception('Berkas gagal di upload');
            //     }
            // }
            if ($request->hasFile('dokumen')) {
                // Ambil data user dan relasinya
                $user = User::with('data_karyawans.unit_kerjas', 'data_karyawans.jabatans')->find($data['user_id']);
                if (!$user) {
                    throw new Exception('Pengguna tidak ditemukan.');
                }

                // Ambil unit kerja dan jabatan asal dari user yang dipilih
                $data['unit_kerja_asal'] = $user->data_karyawans->unit_kerjas->id;
                $data['jabatan_asal'] = $user->data_karyawans->jabatans->id;

                if (is_null($data['unit_kerja_asal']) || is_null($data['jabatan_asal'])) {
                    throw new Exception('Unit kerja atau jabatan asal tidak ditemukan untuk pengguna ini.');
                }

                // Upload file using helper
                StorageServerHelper::login();

                $file = $request->file('dokumen');
                $dataupload = StorageServerHelper::uploadToServer($request, 'Check in berkas - ' . $user->nama);
                $data['dokumen'] = $dataupload['path'];

                // Fetch kategori_berkas for 'System'
                $kategoriBerkas = KategoriBerkas::where('label', 'System')->first();
                if (!$kategoriBerkas) {
                    throw new Exception('Kategori berkas tidak ditemukan.');
                }

                // Store in 'berkas' table on your server
                $berkas = Berkas::create([
                    'user_id' => $data['user_id'],
                    'file_id' => $dataupload['id_file']['id'],
                    'nama' => 'Berkas Transfer - ' . $user->nama,
                    'kategori_berkas_id' => $kategoriBerkas->id,
                    'path' => $dataupload['path'],
                    'tgl_upload' => now(),
                    'nama_file' => $dataupload['nama_file'],
                    'ext' => $dataupload['ext'],
                    'size' => $dataupload['size'],
                ]);
                Log::info('Berkas Transfer ' . $user->nama . ' berhasil di upload');

                if (!$berkas) {
                    throw new Exception('Berkas gagal di upload');
                }

                StorageServerHelper::logout();
            }

            $transfer = TransferKaryawan::create($data);

            $users = $transfer->users;
            $unit_kerja_asals = $transfer->unit_kerja_asals->nama_unit;
            $unit_kerja_tujuans = $transfer->unit_kerja_tujuans->nama_unit;
            $jabatan_asals = $transfer->jabatan_asals->nama_jabatan;
            $jabatan_tujuans = $transfer->jabatan_tujuans->nama_jabatan;
            $alasan = $transfer->alasan;
            $tgl_mulai = $transfer->tgl_mulai;

            // create track record
            TrackRecord::create([
                'user_id' => $users->id,
                'tgl_masuk' => $users->data_karyawans->tgl_masuk,
                'tgl_keluar' => $users->data_karyawans->tgl_keluar,
                'kategori_record_id' => $data['kategori_transfer_id'],
            ]);

            $details = [
                'nama' => $users->nama,
                'email' => $users->data_karyawans->email,
                'unit_kerja_asals' => $unit_kerja_asals,
                'unit_kerja_tujuans' => $unit_kerja_tujuans,
                'jabatan_asals' => $jabatan_asals,
                'jabatan_tujuans' => $jabatan_tujuans,
                'alasan' => $alasan,
                'tgl_mulai' => RandomHelper::convertToDateTimeString($tgl_mulai),
            ];

            if ($request->has('beri_tahu_manajer_direktur') && $request->beri_tahu_manajer_direktur == 1) {
                TransferEmailJob::dispatch('manager@example.com', ['direktur@example.com'], $details);
            }
            if ($request->has('beri_tahu_karyawan') && $request->beri_tahu_karyawan == 1) {
                TransferEmailJob::dispatch($users->data_karyawans->email, [], $details);
            }

            DB::commit();

            return response()->json(new TransferKaryawanResource(Response::HTTP_OK, "Berhasil melakukan transfer karyawan {$users->nama}.", $transfer), Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error: ' . $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }

    public function exportTransferKaryawan()
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new TransferExport(), 'karyawan-data-transfer.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data transfer karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
