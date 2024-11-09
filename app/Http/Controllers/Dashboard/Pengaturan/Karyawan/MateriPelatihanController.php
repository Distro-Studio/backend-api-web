<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Karyawan;

use App\Models\Berkas;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use App\Models\MateriPelatihan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreMateriPelatihanRequest;
use App\Http\Requests\UpdateMateriPelatihanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Carbon\Carbon;

class MateriPelatihanController extends Controller
{
    public function index()
    {
        try {
            if (!Gate::allows('view pelatihanKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Ambil data pelatihan dengan dokumen terkait
            $data_pelatihan = MateriPelatihan::orderBy('created_at', 'desc')->get();
            if ($data_pelatihan->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data materi pelatihan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = $data_pelatihan->map(function ($pelatihan) use ($baseUrl) {
                return [
                    'id' => $pelatihan->id,
                    'judul' => $pelatihan->judul,
                    'deskripsi' => $pelatihan->deskripsi,
                    'user' =>  [
                        'id' => $pelatihan->created_users->id,
                        'nama' => $pelatihan->created_users->nama,
                        'username' => $pelatihan->created_users->username,
                        'email_verified_at' => $pelatihan->created_users->email_verified_at,
                        'data_karyawan_id' => $pelatihan->created_users->data_karyawan_id,
                        'foto_profil' => $pelatihan->created_users->foto_profil,
                        'data_completion_step' => $pelatihan->created_users->data_completion_step,
                        'status_aktif' => $pelatihan->created_users->status_aktif,
                        'created_at' => $pelatihan->created_users->created_at,
                        'updated_at' => $pelatihan->created_users->updated_at,
                    ],
                    'dokumen_materi_1' => $pelatihan->materi_1_berkas ? [
                        'id' => $pelatihan->materi_1_berkas->id,
                        'user_id' => $pelatihan->materi_1_berkas->user_id,
                        'file_id' => $pelatihan->materi_1_berkas->file_id,
                        'nama' => $pelatihan->materi_1_berkas->nama,
                        'nama_file' => $pelatihan->materi_1_berkas->nama_file,
                        'path' => $baseUrl . $pelatihan->materi_1_berkas->path,
                        'ext' => $pelatihan->materi_1_berkas->ext,
                        'size' => $pelatihan->materi_1_berkas->size,
                    ] : null,
                    'dokumen_materi_2' => $pelatihan->materi_2_berkas ? [
                        'id' => $pelatihan->materi_2_berkas->id,
                        'user_id' => $pelatihan->materi_2_berkas->user_id,
                        'file_id' => $pelatihan->materi_2_berkas->file_id,
                        'nama' => $pelatihan->materi_2_berkas->nama,
                        'nama_file' => $pelatihan->materi_2_berkas->nama_file,
                        'path' => $baseUrl . $pelatihan->materi_2_berkas->path,
                        'ext' => $pelatihan->materi_2_berkas->ext,
                        'size' => $pelatihan->materi_2_berkas->size,
                    ] : null,
                    'dokumen_materi_3' => $pelatihan->materi_3_berkas ? [
                        'id' => $pelatihan->materi_3_berkas->id,
                        'user_id' => $pelatihan->materi_3_berkas->user_id,
                        'file_id' => $pelatihan->materi_3_berkas->file_id,
                        'nama' => $pelatihan->materi_3_berkas->nama,
                        'nama_file' => $pelatihan->materi_3_berkas->nama_file,
                        'path' => $baseUrl . $pelatihan->materi_3_berkas->path,
                        'ext' => $pelatihan->materi_3_berkas->ext,
                        'size' => $pelatihan->materi_3_berkas->size,
                    ] : null,
                    'created_at' => $pelatihan->created_at,
                    'updated_at' => $pelatihan->updated_at,
                ];
            });

            $successMessage = "Data materi pelatihan berhasil ditampilkan.";
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => $successMessage,
                'data' => $formattedData,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Materi Pelatihan | - Error pada function index: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreMateriPelatihanRequest $request)
    {
        try {
            if (!Gate::allows('create pelatihanKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();

            DB::beginTransaction();
            try {
                StorageServerHelper::login();
                $berkasIds = [
                    'dokumen_materi_1' => null,
                    'dokumen_materi_2' => null,
                    'dokumen_materi_3' => null,
                ];

                // Proses penyimpanan berkas satu per satu, sesuai dengan field yang diterima (dokumen_materi_1, dokumen_materi_2, dokumen_materi_3)
                foreach ($berkasIds as $field => $value) {
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);
                        $random_filename = Str::random(20);
                        $dataupload = StorageServerHelper::multipleUploadToServer($file, $random_filename);

                        // Simpan data berkas ke tabel berkas
                        $berkas = Berkas::create([
                            'user_id' => $data['user_id'],
                            'file_id' => $dataupload['id_file']['id'],
                            'nama' => $random_filename,
                            'kategori_berkas_id' => 5,
                            'status_berkas_id' => 2,
                            'path' => $dataupload['path'],
                            'tgl_upload' => now(),
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

                // Simpan materi pelatihan beserta dokumen yang ter-upload
                $materiPelatihan = MateriPelatihan::create([
                    'judul' => $data['judul'],
                    'deskripsi' => $data['deskripsi'],
                    'pj_materi' => $data['user_id'],
                    'dokumen_materi_1' => $berkasIds['dokumen_materi_1'],
                    'dokumen_materi_2' => $berkasIds['dokumen_materi_2'],
                    'dokumen_materi_3' => $berkasIds['dokumen_materi_3'],
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);

                DB::commit();

                return response()->json([
                    'status' => Response::HTTP_CREATED,
                    'message' => "Materi pelatihan '{$materiPelatihan->judul}' berhasil dibuat.",
                    'data' => $materiPelatihan,
                ], Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('| Materi Pelatihan | - Error function store: ' . $e->getMessage());
                return response()->json([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Materi Pelatihan | - Error pada function store: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            if (!Gate::allows('view pelatihanKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data_pelatihan = MateriPelatihan::find($id);
            if (!$data_pelatihan) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data materi pelatihan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedData = [
                'id' => $data_pelatihan->id,
                'judul' => $data_pelatihan->judul,
                'deskripsi' => $data_pelatihan->deskripsi,
                'user' => [
                    'id' => $data_pelatihan->created_users->id,
                    'nama' => $data_pelatihan->created_users->nama,
                    'username' => $data_pelatihan->created_users->username,
                    'email_verified_at' => $data_pelatihan->created_users->email_verified_at,
                    'data_karyawan_id' => $data_pelatihan->created_users->data_karyawan_id,
                    'foto_profil' => $data_pelatihan->created_users->foto_profil,
                    'data_completion_step' => $data_pelatihan->created_users->data_completion_step,
                    'status_aktif' => $data_pelatihan->created_users->status_aktif,
                    'created_at' => $data_pelatihan->created_users->created_at,
                    'updated_at' => $data_pelatihan->created_users->updated_at,
                ],
                'dokumen_materi_1' => $data_pelatihan->materi_1_berkas ? [
                    'id' => $data_pelatihan->materi_1_berkas->id,
                    'user_id' => $data_pelatihan->materi_1_berkas->user_id,
                    'file_id' => $data_pelatihan->materi_1_berkas->file_id,
                    'nama' => $data_pelatihan->materi_1_berkas->nama,
                    'nama_file' => $data_pelatihan->materi_1_berkas->nama_file,
                    'path' => $baseUrl . $data_pelatihan->materi_1_berkas->path,
                    'ext' => $data_pelatihan->materi_1_berkas->ext,
                    'size' => $data_pelatihan->materi_1_berkas->size,
                ] : null,
                'dokumen_materi_2' => $data_pelatihan->materi_2_berkas ? [
                    'id' => $data_pelatihan->materi_2_berkas->id,
                    'user_id' => $data_pelatihan->materi_2_berkas->user_id,
                    'file_id' => $data_pelatihan->materi_2_berkas->file_id,
                    'nama' => $data_pelatihan->materi_2_berkas->nama,
                    'nama_file' => $data_pelatihan->materi_2_berkas->nama_file,
                    'path' => $baseUrl . $data_pelatihan->materi_2_berkas->path,
                    'ext' => $data_pelatihan->materi_2_berkas->ext,
                    'size' => $data_pelatihan->materi_2_berkas->size,
                ] : null,
                'dokumen_materi_3' => $data_pelatihan->materi_3_berkas ? [
                    'id' => $data_pelatihan->materi_3_berkas->id,
                    'user_id' => $data_pelatihan->materi_3_berkas->user_id,
                    'file_id' => $data_pelatihan->materi_3_berkas->file_id,
                    'nama' => $data_pelatihan->materi_3_berkas->nama,
                    'nama_file' => $data_pelatihan->materi_3_berkas->nama_file,
                    'path' => $baseUrl . $data_pelatihan->materi_3_berkas->path,
                    'ext' => $data_pelatihan->materi_3_berkas->ext,
                    'size' => $data_pelatihan->materi_3_berkas->size,
                ] : null,
                'created_at' => $data_pelatihan->created_at,
                'updated_at' => $data_pelatihan->updated_at
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data materi pelatihan '{$data_pelatihan->judul}' berhasil didapatkan.",
                'data' => $formattedData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Materi Pelatihan | - Error pada function show: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateMateriPelatihanRequest $request, $id)
    {
        try {
            if (!Gate::allows('edit pelatihanKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data_pelatihan = MateriPelatihan::find($id);
            if (!$data_pelatihan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data materi pelatihan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $data = $request->validated();
            $berkasFields = ['dokumen_materi_1', 'dokumen_materi_2', 'dokumen_materi_3'];

            DB::beginTransaction();
            try {
                StorageServerHelper::login();
                $berkasIds = [$data_pelatihan->dokumen_materi_1, $data_pelatihan->dokumen_materi_2, $data_pelatihan->dokumen_materi_3];

                // Iterasi untuk setiap field dokumen yang ada
                foreach ($berkasFields as $index => $field) {
                    if ($request->hasFile($field)) {
                        $berkasLama = Berkas::find($berkasIds[$index]);
                        if ($berkasLama) {
                            StorageServerHelper::deleteFromServer($berkasLama->file_id);
                            $berkasLama->delete();
                        }

                        $file = $request->file($field);
                        $random_filename = Str::random(20);
                        $dataupload = StorageServerHelper::multipleUploadToServer($file, $random_filename);

                        $berkas = Berkas::create(
                            [
                                'user_id' => $data_pelatihan->pj_materi,
                                'nama' => $random_filename,
                                'file_id' => $dataupload['id_file']['id'],
                                'kategori_berkas_id' => 5,
                                'status_berkas_id' => 2,
                                'path' => $dataupload['path'],
                                'tgl_upload' => now(),
                                'nama_file' => $dataupload['nama_file'],
                                'ext' => $dataupload['ext'],
                                'size' => $dataupload['size'],
                            ]
                        );

                        $berkasIds[$index] = $berkas->id;
                        Log::info('Berkas ' . $field . ' berhasil diupload.');
                    } 
                    // else if (is_string($request->input($field))) {
                    //     unset($data[$field]);
                    // }
                }

                StorageServerHelper::logout();

                $data_pelatihan->update([
                    'judul' => $data['judul'],
                    'deskripsi' => $data['deskripsi'],
                    'dokumen_materi_1' => $berkasIds[0],
                    'dokumen_materi_2' => $berkasIds[1],
                    'dokumen_materi_3' => $berkasIds[2],
                ]);

                DB::commit();

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => "Materi pelatihan '{$data_pelatihan->judul}' berhasil diperbarui.",
                    'data' => $data_pelatihan,
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('| Materi Pelatihan | - Error function update: ' . $e->getMessage());
                return response()->json([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage() . ', Line:' . $e->getLine(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| Materi Pelatihan | - Error pada function update: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Gate::allows('delete pelatihanKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data_pelatihan = MateriPelatihan::find($id);
            if (!$data_pelatihan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data materi pelatihan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            DB::beginTransaction();
            try {
                StorageServerHelper::login();

                $dokumenIds = [
                    $data_pelatihan->dokumen_materi_1,
                    $data_pelatihan->dokumen_materi_2,
                    $data_pelatihan->dokumen_materi_3,
                ];

                $berkas = Berkas::whereIn('id', $dokumenIds)->get();
                // dd($berkas);
                if ($berkas->isNotEmpty()) {
                    foreach ($berkas as $berkasItem) {
                        StorageServerHelper::deleteFromServer($berkasItem->file_id);
                        $berkasItem->delete();
                        Log::info('Berkas ID ' . $berkasItem->id . ' berhasil dihapus.');
                    }
                }
                $data_pelatihan->delete();

                DB::commit();

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => "Materi pelatihan '{$data_pelatihan->judul}' berhasil dihapus.",
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('| Materi Pelatihan | - Error pada function destroy: ' . $e->getMessage());
                return response()->json([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Terjadi kesalahan saat menghapus data. Silakan coba lagi nanti.' . $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            } finally {
                StorageServerHelper::logout();
            }
        } catch (\Exception $e) {
            Log::error('| Materi Pelatihan | - Error pada function destroy: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
