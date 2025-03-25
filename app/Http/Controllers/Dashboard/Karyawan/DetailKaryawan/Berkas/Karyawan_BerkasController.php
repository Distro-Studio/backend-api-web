<?php

namespace App\Http\Controllers\Dashboard\Karyawan\DetailKaryawan\Berkas;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Berkas;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Models\DataKaryawan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Karyawan_BerkasController extends Controller
{
    // berkas section
    public function getDataDokumen($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Ambil data berkas berdasarkan data_karyawan_id
            $berkas = Berkas::where('user_id', function ($query) use ($data_karyawan_id) {
                $query->select('id')->from('users')->where('data_karyawan_id', $data_karyawan_id);
            })->where('kategori_berkas_id', '!=', 3) // get berkas selain 'System'
                ->get();

            if ($berkas->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data dokumen karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Ambil data user dari berkas yang pertama
            $user = $berkas->first()->users;
            $baseUrl = "https://192.168.0.20/RskiSistem24/file-storage/public";

            // Format data berkas
            $formattedData = $berkas->map(function ($item) use ($baseUrl) {
                $fileExt = $item->ext ? StorageServerHelper::getExtensionFromMimeType($item->ext) : null;
                // $fileUrl = $baseUrl . $item->path . ($fileExt ? '.' . $fileExt : '');
                $fileUrl = $baseUrl . $item->path;

                return [
                    'id' => $item->id,
                    'file_id' => $item->file_id,
                    'nama' => $item->nama,
                    'kategori_dokumen' => $item->kategori_berkas,
                    'path' => $fileUrl,
                    'tgl_upload' => $item->tgl_upload,
                    'nama_file' => $item->nama_file,
                    'ext' => $item->ext,
                    'size' => $item->size,
                    'status_berkas' => $item->status_berkas,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail dokumen karyawan '{$user->nama}' berhasil ditampilkan.",
                'data' => [
                    'id' => $user->data_karyawan_id,
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
                        'updated_at' => $user->updated_at,
                    ],
                    'jumlah_dokumen' => $berkas->count(),
                    'status_berkas' => [
                        'terakhir_diperbarui' => $user->status_aktif == 1
                            ? null
                            : $berkas->sortByDesc('updated_at')->first()->updated_at
                    ],
                    'data_dokumen' => $formattedData,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataDokumen: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // public function verifikasiBerkas(Request $request, $data_karyawan_id)
    // {
    //     try {
    //         if (!Gate::allows('verifikasi1 berkas')) {
    //             return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //         }

    //         // Cari user berdasarkan data_karyawan_id
    //         $user = User::where('data_karyawan_id', $data_karyawan_id)->first();

    //         if (!$user) {
    //             return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    //         }

    //         // Ambil semua berkas terkait dengan user ini yang belum diverifikasi
    //         $berkasList = $user->berkas->where('status_berkas_id', 1);

    //         if ($berkasList->isEmpty()) {
    //             return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada berkas yang perlu diverifikasi untuk karyawan '{$user->nama}'."), Response::HTTP_NOT_FOUND);
    //         }

    //         foreach ($berkasList as $berkas) {
    //             $status_berkas_id = $berkas->status_berkas_id;

    //             // Logika verifikasi disetujui tahap 1
    //             if ($request->has('verifikasi_disetujui') && $request->verifikasi_disetujui == 1) {
    //                 if ($status_berkas_id == 1) {
    //                     $berkas->status_berkas_id = 2; // Update status ke disetujui
    //                     $berkas->verifikator_1 = Auth::id();
    //                     $berkas->save();

    //                     // Kirim notifikasi bahwa berkas telah diverifikasi
    //                     $this->createNotifikasiBerkas($berkas, 'disetujui');
    //                 } else {
    //                     return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Berkas '{$berkas->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
    //                 }
    //             } else if ($request->has('verifikasi_ditolak') && $request->verifikasi_ditolak == 1) {
    //                 if ($status_berkas_id == 1) {
    //                     $berkas->status_berkas_id = 3; // Update status ke ditolak
    //                     $berkas->verifikator_1 = Auth::id();
    //                     $berkas->alasan = $request->input('alasan');
    //                     $berkas->save();

    //                     // Kirim notifikasi bahwa berkas telah ditolak
    //                     $this->createNotifikasiBerkas($berkas, 'ditolak');
    //                 } else {
    //                     return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Berkas '{$berkas->nama}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
    //                 }
    //             } else {
    //                 return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
    //             }
    //         }

    //         return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi berkas untuk karyawan '{$user->nama}' berhasil dilakukan."), Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         Log::error('| Karyawan | - Error function verifikasiBerkas: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
    //             'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    public function createPersonalFile(Request $request, $data_karyawan_id)
    {
        try {
            if (!Gate::allows('edit dataKaryawan')) {
                if (!Gate::allows('edit dataKaryawan')) {
                    return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
                }
            }

            // buat validasi dokumen
            $validator = Validator::make($request->all(), [
                'label' => 'required|string',
                'dokumen' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            ], [
                'label.required' => 'Nama dokumen harus diisi.',
                'dokumen.required' => 'Dokumen harus diisi.',
                'dokumen.file' => 'Dokumen harus berupa file.',
                'dokumen.mimes' => 'Dokumen harus berupa file PDF, JPG, JPEG, atau PNG.',
                'dokumen.max' => 'Ukuran dokumen maksimal 10 MB.',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => implode(' ', $validator->errors()->all()),
                ], Response::HTTP_BAD_REQUEST);
            }

            $karyawan = DataKaryawan::where('id', '!=', 1)->find($data_karyawan_id);
            if (!$karyawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            if ($request->hasFile('dokumen')) {
                StorageServerHelper::login();

                $file = $request->file('dokumen');
                $random_filename = Str::random(20);

                DB::beginTransaction();

                $dataupload = StorageServerHelper::uploadToServer($request, $random_filename);

                if (!$dataupload || !isset($dataupload['path'], $dataupload['id_file']['id'])) {
                    DB::rollBack(); // ← GAGAL, ROLLBACK
                    throw new \Exception('Gagal upload file ke server dokumen.');
                }

                $data['dokumen'] = $dataupload['path'];

                $berkas = Berkas::create([
                    'user_id' => $karyawan->user_id,
                    'file_id' => $dataupload['id_file']['id'],
                    'nama' => $request->input('label'),
                    'kategori_berkas_id' => 2, // umum
                    'status_berkas_id' => 2,
                    'path' => $dataupload['path'],
                    'tgl_upload' => now(),
                    'nama_file' => $dataupload['nama_file'],
                    'ext' => $dataupload['ext'],
                    'size' => $dataupload['size'],
                ]);

                if (!$berkas) {
                    DB::rollBack(); // ← JIKA GAGAL SIMPAN
                    throw new \Exception('Berkas gagal disimpan ke database.');
                }

                DB::commit(); // ← SUKSES, COMMIT

                Log::info('Berkas ' . $karyawan->users->nama . ' berhasil di upload.');

                StorageServerHelper::logout();
            }

            return response()->json(new WithoutDataResource(Response::HTTP_CREATED, 'Berkas dari karyawan ' . $karyawan->users->nama . ' berhasil diupload.'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Log::error('| Karyawan | - Error function store: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Bulk verification berkas
    public function verifikasiBerkas(Request $request, $data_karyawan_id)
    {
        try {
            if (!Gate::allows('edit dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $berkasIds = $request->input('berkas_id', []);
            if (empty($berkasIds)) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak ada berkas yang dipilih.'), Response::HTTP_BAD_REQUEST);
            }

            // Cari user berdasarkan data_karyawan_id
            $user = User::where('data_karyawan_id', $data_karyawan_id)->first();
            if (!$user) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            // Ambil semua berkas terkait dengan user ini yang belum diverifikasi
            $berkasList = Berkas::where('user_id', $user->id)
                ->whereIn('id', $berkasIds)
                ->where('status_berkas_id', 1)
                ->get();
            if ($berkasList->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada berkas yang perlu diverifikasi untuk karyawan '{$user->nama}'."), Response::HTTP_NOT_FOUND);
            }

            foreach ($berkasList as $berkas) {
                $status_berkas_id = $berkas->status_berkas_id;

                // Logika verifikasi disetujui tahap 1
                if ($request->has('verifikasi_disetujui') && $request->verifikasi_disetujui == 1) {
                    if ($status_berkas_id == 1) {
                        $berkas->status_berkas_id = 2; // Update status ke disetujui
                        $berkas->verifikator_1 = Auth::id();
                        $berkas->save();

                        // Kirim notifikasi bahwa berkas telah diverifikasi
                        $this->createNotifikasiBerkas($berkas, 'disetujui');
                    } else {
                        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Berkas '{$berkas->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
                    }
                } else if ($request->has('verifikasi_ditolak') && $request->verifikasi_ditolak == 1) {
                    if ($status_berkas_id == 1) {
                        $berkas->status_berkas_id = 3; // Update status ke ditolak
                        $berkas->verifikator_1 = Auth::id();
                        $berkas->alasan = $request->input('alasan');
                        $berkas->save();

                        // Kirim notifikasi bahwa berkas telah ditolak
                        $this->createNotifikasiBerkas($berkas, 'ditolak');
                    } else {
                        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Berkas '{$berkas->nama}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
                }
            }

            return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi berkas untuk karyawan '{$user->nama}' berhasil dilakukan."), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function verifikasiBerkas: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiBerkas($berkas, $status)
    {
        try {
            $user = $berkas->users;

            if ($status == 'disetujui') {
                $message = "Berkas {$berkas->nama} dari '{$user->nama}' telah diverifikasi dan disetujui.";
                $messageSuperAdmin = "Notifikasi untuk Super Admin: Berkas {$berkas->nama} dari '{$user->nama}' telah diverifikasi dan disetujui.";
            } elseif ($status == 'ditolak') {
                $message = "Berkas {$berkas->nama} dari '{$user->nama}' telah ditolak. Alasan: {$berkas->alasan}.";
                $messageSuperAdmin = "Notifikasi untuk Super Admin: Berkas {$berkas->nama} dari '{$user->nama}' telah ditolak. Alasan: {$berkas->alasan}.";
            }

            $userIds = [$user->id, 1];
            foreach ($userIds as $userId) {
                $messageToSend = $userId === 1 ? $messageSuperAdmin : $message;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 6,
                    'user_id' => $userId,
                    'message' => $messageToSend,
                    'is_read' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function createNotifikasiBerkas: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
