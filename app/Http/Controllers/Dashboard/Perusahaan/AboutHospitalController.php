<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use App\Models\Berkas;
use Illuminate\Support\Str;
use App\Models\AboutHospital;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\UpdateAboutHospitalRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class AboutHospitalController extends Controller
{
    public function index($id)
    {
        try {
            if (!Gate::allows('view aboutHospital')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $hospital = AboutHospital::find($id);
            if (!$hospital) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data detail perusahaan RSKI tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $baseUrl = "https://192.168.0.20/RskiSistem24/file-storage/public";
            $formattedData = [
                'id' => $hospital->id,
                'konten' => $hospital->konten,
                'edited_by' => $hospital->user_edited ? [
                    'id' => $hospital->user_edited->id,
                    'nama' => $hospital->user_edited->nama,
                    'username' => $hospital->user_edited->username,
                    'email_verified_at' => $hospital->user_edited->email_verified_at,
                    'data_karyawan_id' => $hospital->user_edited->data_karyawan_id,
                    'foto_profil' => $hospital->user_edited->foto_profil,
                    'data_completion_step' => $hospital->user_edited->data_completion_step,
                    'status_aktif' => $hospital->user_edited->status_aktif,
                    'created_at' => $hospital->user_edited->created_at,
                    'updated_at' => $hospital->user_edited->updated_at
                ] : null,
                'gambar_about_1' => $hospital->gambar_1_dokumen ? [
                    'id' => $hospital->gambar_1_dokumen->id,
                    'user_id' => $hospital->gambar_1_dokumen->user_id,
                    'file_id' => $hospital->gambar_1_dokumen->file_id,
                    'nama' => $hospital->gambar_1_dokumen->nama,
                    'nama_file' => $hospital->gambar_1_dokumen->nama_file,
                    'path' => $baseUrl . $hospital->gambar_1_dokumen->path,
                    'ext' => $hospital->gambar_1_dokumen->ext,
                    'size' => $hospital->gambar_1_dokumen->size,
                ] : null,
                'gambar_about_2' => $hospital->gambar_2_dokumen ? [
                    'id' => $hospital->gambar_2_dokumen->id,
                    'user_id' => $hospital->gambar_2_dokumen->user_id,
                    'file_id' => $hospital->gambar_2_dokumen->file_id,
                    'nama' => $hospital->gambar_2_dokumen->nama,
                    'nama_file' => $hospital->gambar_2_dokumen->nama_file,
                    'path' => $baseUrl . $hospital->gambar_2_dokumen->path,
                    'ext' => $hospital->gambar_2_dokumen->ext,
                    'size' => $hospital->gambar_2_dokumen->size,
                ] : null,
                'gambar_about_3' => $hospital->gambar_3_dokumen ? [
                    'id' => $hospital->gambar_3_dokumen->id,
                    'user_id' => $hospital->gambar_3_dokumen->user_id,
                    'file_id' => $hospital->gambar_3_dokumen->file_id,
                    'nama' => $hospital->gambar_3_dokumen->nama,
                    'nama_file' => $hospital->gambar_3_dokumen->nama_file,
                    'path' => $baseUrl . $hospital->gambar_3_dokumen->path,
                    'ext' => $hospital->gambar_3_dokumen->ext,
                    'size' => $hospital->gambar_3_dokumen->size,
                ] : null,
                'created_at' => $hospital->created_at,
                'updated_at' => $hospital->updated_at,
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data detail perusahaan RSKI berhasil didapatkan.',
                'data' => $formattedData,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| About Hospitals | - Error pada fungsi index: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateAboutHospitalRequest $request, $id)
    {
        try {
            if (!Gate::allows('edit aboutHospital')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $loggedUser = Auth::user();

            $hospital = AboutHospital::find($id);
            if (!$hospital) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tentang rumah sakit tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $data = $request->validated();
            $berkasFields = ['about_hospital_1', 'about_hospital_2', 'about_hospital_3'];

            DB::beginTransaction();
            try {
                StorageServerHelper::login();
                $berkasIds = [$hospital->about_hospital_1, $hospital->about_hospital_2, $hospital->about_hospital_3];

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
                                'user_id' => $hospital->edited_by,
                                'nama' => $random_filename,
                                'file_id' => $dataupload['id_file']['id'],
                                'kategori_berkas_id' => 6,
                                'status_berkas_id' => 2,
                                'path' => '/' . $dataupload['path'],
                                'tgl_upload' => now(),
                                'nama_file' => $dataupload['nama_file'],
                                'ext' => $dataupload['ext'],
                                'size' => $dataupload['size'],
                            ]
                        );

                        $berkasIds[$index] = $berkas->id;
                        Log::info('Berkas ' . $field . ' berhasil diperbarui.');
                    } elseif (is_string($request->input($field))) {
                        unset($data[$field]);
                    }
                }

                StorageServerHelper::logout();

                $hospital->update([
                    'konten' => $data['konten'],
                    'edited_by' => $loggedUser->id,
                    'about_hospital_1' => $berkasIds[0],
                    'about_hospital_2' => $berkasIds[1],
                    'about_hospital_3' => $berkasIds[2],
                ]);

                DB::commit();

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => "Data tentang rumah sakit berhasil diperbarui oleh '{$loggedUser->nama}'.",
                    'data' => $hospital,
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('| About Hospitals | - Error function update: ' . $e->getMessage());
                return response()->json([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('| About Hospitals | - Error pada function update: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
