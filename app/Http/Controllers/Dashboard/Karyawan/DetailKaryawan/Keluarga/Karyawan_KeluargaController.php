<?php

namespace App\Http\Controllers\Dashboard\Karyawan\DetailKaryawan\Keluarga;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Notifikasi;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreDataKeluargaRequest;
use App\Http\Requests\UpdateDataKeluargaReqeust;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class Karyawan_KeluargaController extends Controller
{
    public function getDataKeluarga($data_karyawan_id)
    {
        try {
            if (!Gate::allows('view dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Ambil data keluarga berdasarkan data_karyawan_id
            $keluarga = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)
                ->with('data_karyawans.users')
                ->get();
            if ($keluarga->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data keluarga karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Ambil data karyawan dan user dari data keluarga
            $dataKaryawan = $keluarga->first()->data_karyawans;
            $user = $dataKaryawan->users;

            $alasanDitolak = null;
            $status_keluarga = 'Diverifikasi';
            if ($keluarga->contains('status_keluarga_id', 1)) {
                $status_keluarga = 'Menunggu';
            } elseif ($keluarga->contains('status_keluarga_id', 3)) {
                $status_keluarga = 'Ditolak';
                $alasanDitolak = $keluarga->where('status_keluarga_id', 3)->sortByDesc('updated_at')->first()->alasan;
            }

            // Format data keluarga
            $formattedData = $keluarga->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_keluarga' => $item->nama_keluarga,
                    'hubungan' => $item->hubungan,
                    'pendidikan_terakhir' => $item->kategori_pendidikans,
                    'status_hidup' => $item->status_hidup,
                    'pekerjaan' => $item->pekerjaan,
                    'no_hp' => $item->no_hp,
                    'email' => $item->email,
                    'status_keluarga' => $item->status_keluargas,
                    'is_bpjs' => $item->is_bpjs,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail keluarga karyawan '{$user->nama}' berhasil ditampilkan.",
                'data' => [
                    'id' => $dataKaryawan->id,
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
                    'jumlah_keluarga' => $keluarga->count(),
                    'status_keluarga' => [
                        'status' => $status_keluarga,
                        'alasan' => $alasanDitolak,
                        'terakhir_diperbarui' => $keluarga->sortByDesc('updated_at')->first()->updated_at
                    ],
                    'data_keluarga' => $formattedData,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function getDataKeluarga: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeDataKeluarga(StoreDataKeluargaRequest $request, $data_karyawan_id)
    {
        try {
            $currentUser = Auth::user();
            if (!$currentUser->hasRole('Super Admin')) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.'
                ], Response::HTTP_FORBIDDEN);
            }

            // $currentMonth = Carbon::now('Asia/Jakarta')->month;
            // $currentYear = Carbon::now('Asia/Jakarta')->year;
            // $penggajianAda = Penggajian::where('data_karyawan_id', $data_karyawan_id)
            //     ->whereHas('riwayat_penggajians', function ($query) use ($currentMonth, $currentYear) {
            //         $query->whereMonth('periode', $currentMonth)
            //             ->whereYear('periode', $currentYear);
            //     })
            //     ->exists();
            // if ($penggajianAda) {
            //     return response()->json(new WithoutDataResource(
            //         Response::HTTP_FORBIDDEN,
            //         'Tidak dapat menyimpan data keluarga karena bulan ini sudah dilaksanakan penggajian. Silahkan create data keluarga sebelum penggajian.'
            //     ), Response::HTTP_FORBIDDEN);
            // }

            if (!Gate::allows('edit dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $user = User::where('data_karyawan_id', $data_karyawan_id)->first();
            if (!$user) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Karyawan dengan data karyawan tersebut tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $request->validated();
            DB::beginTransaction();
            $dataKeluarga = DataKeluarga::create([
                'data_karyawan_id' => $data_karyawan_id,
                'nama_keluarga' => $data['nama_keluarga'],
                'hubungan' => $data['hubungan'],
                'pendidikan_terakhir' => $data['pendidikan_terakhir'],
                'status_hidup' => $data['status_hidup'],
                'pekerjaan' => $data['pekerjaan'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'email' => $data['email'] ?? null,
                'status_keluarga_id' => 2,
                'is_bpjs' => $data['is_bpjs'],
                'verifikator_1' => $currentUser->id,
            ]);
            DB::commit();

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Data keluarga '{$dataKeluarga->nama_keluarga}' dari karyawan '{$user->nama}' berhasil ditambahkan."
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function storeDataKeluarga: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateDataKeluarga(UpdateDataKeluargaReqeust $request, $data_karyawan_id, $keluarga_id)
    {
        try {
            $currentUser = Auth::user();
            if (!$currentUser->hasRole('Super Admin')) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.'
                ], Response::HTTP_FORBIDDEN);
            }

            // $currentMonth = Carbon::now('Asia/Jakarta')->month;
            // $currentYear = Carbon::now('Asia/Jakarta')->year;
            // $penggajianAda = Penggajian::where('data_karyawan_id', $data_karyawan_id)
            //     ->whereHas('riwayat_penggajians', function ($query) use ($currentMonth, $currentYear) {
            //         $query->whereMonth('periode', $currentMonth)
            //             ->whereYear('periode', $currentYear);
            //     })
            //     ->exists();
            // if ($penggajianAda) {
            //     return response()->json(new WithoutDataResource(
            //         Response::HTTP_FORBIDDEN,
            //         'Tidak dapat melakukan perubahan data keluarga karena bulan ini sudah dilaksanakan penggajian. Silahkan update data keluarga sebelum penggajian.'
            //     ), Response::HTTP_FORBIDDEN);
            // }

            if (!Gate::allows('edit dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // Temukan data keluarga berdasarkan data_karyawan_id dan keluarga_id
            $dataKeluarga = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)
                ->where('id', $keluarga_id)
                ->first();
            if (!$dataKeluarga) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data keluarga tidak ditemukan untuk karyawan terkait.'), Response::HTTP_NOT_FOUND);
            }

            $validatedData = $request->validated();

            $dataKeluarga->update([
                'nama_keluarga' => $validatedData['nama_keluarga'],
                'hubungan' => $validatedData['hubungan'],
                'pendidikan_terakhir' => $validatedData['pendidikan_terakhir'],
                'status_hidup' => $validatedData['status_hidup'],
                'pekerjaan' => $validatedData['pekerjaan'] ?? $dataKeluarga->pekerjaan,
                'no_hp' => $validatedData['no_hp'] ?? $dataKeluarga->no_hp,
                'email' => $validatedData['email'] ?? $dataKeluarga->email,
                'is_bpjs' => $validatedData['is_bpjs'],
            ]);

            return response()->json(new WithoutDataResource(Response::HTTP_OK, "Data keluarga '{$dataKeluarga->nama_keluarga}' dari karyawan '{$dataKeluarga->data_karyawans->users->nama}' berhasil diperbarui."), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function updateDataKeluarga: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifikasiKeluarga(Request $request, $data_karyawan_id)
    {
        try {
            if (!Gate::allows('edit dataKaryawan')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            // $currentMonth = Carbon::now('Asia/Jakarta')->month;
            // $currentYear = Carbon::now('Asia/Jakarta')->year;
            // $penggajianAda = Penggajian::where('data_karyawan_id', $data_karyawan_id)
            //     ->whereHas('riwayat_penggajians', function ($query) use ($currentMonth, $currentYear) {
            //         $query->whereMonth('periode', $currentMonth)
            //             ->whereYear('periode', $currentYear);
            //     })
            //     ->exists();
            // if ($penggajianAda) {
            //     return response()->json(new WithoutDataResource(
            //         Response::HTTP_FORBIDDEN,
            //         'Tidak dapat melakukan verifikasi data keluarga karena bulan ini sudah dilaksanakan penggajian. Silahkan verifikasi data keluarga sebelum penggajian.'
            //     ), Response::HTTP_FORBIDDEN);
            // }

            $karyawan = DataKaryawan::find($data_karyawan_id);
            if (!$karyawan) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $dataKeluargaList = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)
                ->where('status_keluarga_id', 1)
                ->get();
            if ($dataKeluargaList->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada anggota keluarga yang perlu diverifikasi untuk karyawan '{$karyawan->users->nama}'."), Response::HTTP_NOT_FOUND);
            }

            foreach ($dataKeluargaList as $keluarga) {
                $status_keluarga_id = $keluarga->status_keluarga_id;

                // disetujui tahap 1
                if ($request->has('verifikasi_disetujui') && $request->verifikasi_disetujui == 1) {
                    if ($status_keluarga_id == 1) {
                        $keluarga->status_keluarga_id = 2;
                        $keluarga->verifikator_1 = Auth::id();
                        $keluarga->save();

                        $this->createNotifikasiKeluarga($keluarga, 'disetujui');
                    } else {
                        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Anggota keluarga dari karyawan '{$karyawan->users->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
                    }
                } else if ($request->has('verifikasi_ditolak') && $request->verifikasi_ditolak == 1) {
                    if ($status_keluarga_id == 1) {
                        $keluarga->status_keluarga_id = 3;
                        $keluarga->is_bpjs = 0;
                        $keluarga->verifikator_1 = Auth::id();
                        $keluarga->alasan = $request->input('alasan');
                        $keluarga->save();

                        $this->createNotifikasiKeluarga($keluarga, 'ditolak');
                    } else {
                        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Anggota keluarga karyawan '{$karyawan->users->nama}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
                }
            }

            return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi keluarga karyawan '{$karyawan->users->nama}' berhasil dilakukan."), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function verifikasiKeluarga: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiKeluarga($keluarga, $status)
    {
        try {
            $karyawan = $keluarga->data_karyawans;

            if (!$karyawan || !$karyawan->users) {
                throw new \Exception("User terkait dengan karyawan '{$karyawan->users->nama}' tidak ditemukan.");
            }

            if ($status == 'disetujui') {
                $message = "Keluarga karyawan '{$karyawan->users->nama}' telah diverifikasi dan disetujui.";
                $messageSuperAdmin = "Notifikasi untuk Super Admin: Keluarga karyawan '{$karyawan->users->nama}' telah diverifikasi dan disetujui.";
            } elseif ($status == 'ditolak') {
                $message = "Keluarga karyawan '{$karyawan->users->nama}' telah ditolak. Alasan: {$keluarga->alasan}.";
                $messageSuperAdmin = "Notifikasi untuk Super Admin: Keluarga karyawan '{$karyawan->users->nama}' telah ditolak. Alasan: {$keluarga->alasan}.";
            }

            $userIds = [$karyawan->users->id, 1];
            foreach ($userIds as $userId) {
                $messageToSend = $userId === 1 ? $messageSuperAdmin : $message;
                Notifikasi::create([
                    'kategori_notifikasi_id' => 12,
                    'user_id' => $userId,
                    'message' => $messageToSend,
                    'is_read' => false,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('| Karyawan | - Error function createNotifikasiKeluarga: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Bulk verification keluarga
    // public function verifikasiKeluarga(Request $request, $data_karyawan_id)
    // {
    //     if (!Gate::allows('edit dataKaryawan')) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     $dataKeluargaIds = $request->input('data_keluarga_id', []);
    //     if (empty($dataKeluargaIds)) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak ada anggota keluarga yang dipilih untuk verifikasi.'), Response::HTTP_BAD_REQUEST);
    //     }

    //     $karyawan = DataKaryawan::find($data_karyawan_id);
    //     if (!$karyawan) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    //     }

    //     $dataKeluargaList = DataKeluarga::where('data_karyawan_id', $data_karyawan_id)
    //         ->whereIn('id', $dataKeluargaIds)
    //         ->where('status_keluarga_id', 1)
    //         ->get();
    //     if ($dataKeluargaList->isEmpty()) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, "Tidak ada anggota keluarga yang perlu diverifikasi untuk karyawan '{$karyawan->users->nama}'."), Response::HTTP_NOT_FOUND);
    //     }

    //     foreach ($dataKeluargaList as $keluarga) {
    //         $status_keluarga_id = $keluarga->status_keluarga_id;

    //         if ($request->has('verifikasi_disetujui') && $request->has('verifikasi_ditolak')) {
    //             return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak dapat menyetujui dan menolak verifikasi pada saat yang bersamaan.'), Response::HTTP_BAD_REQUEST);
    //         }

    //         // disetujui tahap 1
    //         if ($request->has('verifikasi_disetujui') && $request->verifikasi_disetujui == 1) {
    //             if ($status_keluarga_id == 1) {
    //                 $keluarga->status_keluarga_id = 2;
    //                 $keluarga->verifikator_1 = Auth::id();
    //                 $keluarga->save();

    //                 $this->createNotifikasiKeluarga($keluarga, 'disetujui');
    //             } else {
    //                 return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Anggota keluarga dari karyawan '{$karyawan->users->nama}' tidak dalam status untuk disetujui."), Response::HTTP_BAD_REQUEST);
    //             }
    //         } else if ($request->has('verifikasi_ditolak') && $request->verifikasi_ditolak == 1) {
    //             if ($status_keluarga_id == 1) {
    //                 $keluarga->status_keluarga_id = 3;
    //                 $keluarga->is_bpjs = 0;
    //                 $keluarga->verifikator_1 = Auth::id();
    //                 $keluarga->alasan = $request->input('alasan');
    //                 $keluarga->save();

    //                 $this->createNotifikasiKeluarga($keluarga, 'ditolak');
    //             } else {
    //                 return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Anggota keluarga karyawan '{$karyawan->users->nama}' tidak dalam status untuk ditolak."), Response::HTTP_BAD_REQUEST);
    //             }
    //         } else {
    //             return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Aksi tidak valid.'), Response::HTTP_BAD_REQUEST);
    //         }
    //     }

    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, "Verifikasi keluarga karyawan '{$karyawan->users->nama}' berhasil dilakukan."), Response::HTTP_OK);
    // }
}
