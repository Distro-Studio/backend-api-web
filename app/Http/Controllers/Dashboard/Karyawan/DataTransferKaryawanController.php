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

        // Filter
        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $transfer->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        if ($request->has('status_karyawan')) {
            if (is_array($request->status_karyawan)) {
                $transfer->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($request) {
                    $query->whereIn('label', $request->status_karyawan);
                });
            } else {
                $transfer->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($request) {
                    $query->where('label', $request->status_karyawan);
                });
            }
        }

        if ($request->has('masa_kerja')) {
            $masa_kerja = $request->masa_kerja;
            $transfer->whereHas('users.data_karyawans', function ($query) use ($masa_kerja) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$masa_kerja]);
            });
        }

        if ($request->has('status_aktif')) {
            $statusAktif = $request->status_aktif;
            $transfer->whereHas('users', function ($query) use ($statusAktif) {
                $query->where('status_aktif', $statusAktif);
            });
        }

        if ($request->has('tgl_masuk')) {
            $tglMasuk = $request->tgl_masuk;
            $tglMasuk = Carbon::parse($tglMasuk)->format('Y-m-d');
            $transfer->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                $query->where('tgl_masuk', $tglMasuk);
            });
        }

        // Search
        if ($request->has('search')) {
            $transfer = $transfer->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })
                    ->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                        $query->where('nik', 'like', $searchTerm);
                    })
                    ->orWhereHas('kategori_transfer_karyawans', function ($query) use ($searchTerm) {
                        $query->where('label', 'like', $searchTerm);
                    })
                    ->orWhereHas('unit_kerja_asals', function ($query) use ($searchTerm) {
                        $query->where('nama_unit', 'like', $searchTerm);
                    })
                    ->orWhereHas('unit_kerja_tujuans', function ($query) use ($searchTerm) {
                        $query->where('nama_unit', 'like', $searchTerm);
                    })
                    ->orWhereHas('jabatan_asals', function ($query) use ($searchTerm) {
                        $query->where('nama_jabatan', 'like', $searchTerm);
                    })
                    ->orWhereHas('jabatan_tujuans', function ($query) use ($searchTerm) {
                        $query->where('nama_jabatan', 'like', $searchTerm);
                    })
                    ->orWhere('alasan', 'like', $searchTerm);
            });
        }

        // Pastikan limit adalah integer
        $limit = is_numeric($limit) ? (int)$limit : 10;
        $dataTransfer = $transfer->paginate($limit);
        if ($dataTransfer->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data transfer karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataTransfer->items();
        $formattedData = array_map(function ($transfer) {
            $user = $transfer->users;
            $data_karyawan = $user->data_karyawans;
            return [
                'id' => $transfer->id,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'foto_profil' => $user->foto_profil,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'role' => $user->roles,
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
        }, $formattedData);

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
                // Fetch user information
                $user = User::find($data['user_id']);
                if (!$user) {
                    throw new Exception('Pengguna tidak ditemukan.');
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
                'tgl_mulai' => $tgl_mulai
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
