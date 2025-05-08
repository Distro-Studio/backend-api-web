<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Akun;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ModulVerifikasi;
use App\Models\RelasiVerifikasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreMasterVerificationRequest;
use App\Http\Requests\UpdateMasterVerificationRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class MasterVerificationController extends Controller
{
    public function getAllRelationVerification()
    {
        if (!Gate::allows('view masterVerifikasi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataVerifikasi = RelasiVerifikasi::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all relation verification for dropdown',
            'data' => $dataVerifikasi
        ], Response::HTTP_OK);
    }

    public function getAllKaryawanDiverifikasi($karyawan_diverifikasi)
    {
        if (!Gate::allows('view masterVerifikasi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $modulVerifikasiList = ModulVerifikasi::all();

            // Iterasi setiap modul dan ordernya
            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $result = $modulVerifikasiList->flatMap(function ($modul) use ($karyawan_diverifikasi, $baseUrl) {
                $data = [];
                for ($order = 1; $order <= $modul->max_order; $order++) {
                    $relasiVerifikasi = RelasiVerifikasi::whereJsonContains('user_diverifikasi', (int) $karyawan_diverifikasi)
                        ->where('modul_verifikasi', $modul->id)
                        ->where('order', $order)
                        ->with(['users', 'modul_verifikasis'])
                        ->first();

                    // Tambahkan data ke dalam array
                    $data[] = [
                        'modul' => [
                            'id' => $modul->id,
                            'label' => $modul->label,
                            'max_order' => $modul->max_order,
                            'created_at' => $modul->created_at,
                            'updated_at' => $modul->updated_at,
                        ],
                        'order' => $order,
                        'data' => $relasiVerifikasi ? [
                            'id' => $relasiVerifikasi->id,
                            'name' => $relasiVerifikasi->nama,
                            'verifikator' => [
                                'id' => $relasiVerifikasi->users->id,
                                'nama' => $relasiVerifikasi->users->nama,
                                'username' => $relasiVerifikasi->users->username,
                                'email_verified_at' => $relasiVerifikasi->users->email_verified_at,
                                'data_karyawan_id' => $relasiVerifikasi->users->data_karyawan_id,
                                'foto_profil' => $relasiVerifikasi->users->foto_profiles ? [
                                    'id' => $relasiVerifikasi->users->foto_profiles->id,
                                    'user_id' => $relasiVerifikasi->users->foto_profiles->user_id,
                                    'file_id' => $relasiVerifikasi->users->foto_profiles->file_id,
                                    'nama' => $relasiVerifikasi->users->foto_profiles->nama,
                                    'nama_file' => $relasiVerifikasi->users->foto_profiles->nama_file,
                                    'path' => $baseUrl . $relasiVerifikasi->users->foto_profiles->path,
                                    'ext' => $relasiVerifikasi->users->foto_profiles->ext,
                                    'size' => $relasiVerifikasi->users->foto_profiles->size,
                                ] : null,
                                'data_completion_step' => $relasiVerifikasi->users->data_completion_step,
                                'status_aktif' => $relasiVerifikasi->users->status_aktif,
                                'created_at' => $relasiVerifikasi->users->created_at,
                                'updated_at' => $relasiVerifikasi->users->updated_at,
                            ],
                            'modul_verifikasi' => $relasiVerifikasi->modul_verifikasis,
                            'order' => $relasiVerifikasi->order,
                            'user_diverifikasi' => $relasiVerifikasi->user_diverifikasi,
                            'created_at' => $relasiVerifikasi->created_at,
                            'updated_at' => $relasiVerifikasi->updated_at,
                            'deleted_at' => $relasiVerifikasi->deleted_at,
                        ] : null,
                    ];
                }
                return $data;
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data hak verifikasi karyawan terkait berhasil ditampilkan.',
                'data' => $result,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("| Modul Verifikasi | - Error saat mengambil data modul verifikasi: {$e->getMessage()}");
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllKaryawanVerifikator(Request $request)
    {
        if (!Gate::allows('view masterVerifikasi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $modulVerifikasi = $request->input('modul_verifikasi');
            $order = $request->input('order');

            $verifications = RelasiVerifikasi::where('modul_verifikasi', $modulVerifikasi)
                ->where('order', $order)
                ->with('users')
                ->get();
            if ($verifications->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data verifikator yang ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedUsers = $verifications->map(function ($verification) use ($baseUrl) {
                $user = $verification->users;
                return [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email_verified_at' => $user->email_verified_at,
                    'data_karyawan_id' => $user->data_karyawan_id,
                    'foto_profil' => $user->foto_profiles ? [
                        'id' => $user->foto_profiles->id,
                        'user_id' => $user->foto_profiles->user_id,
                        'file_id' => $user->foto_profiles->file_id,
                        'nama' => $user->foto_profiles->nama,
                        'nama_file' => $user->foto_profiles->nama_file,
                        'path' => $baseUrl . $user->foto_profiles->path,
                        'ext' => $user->foto_profiles->ext,
                        'size' => $user->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ];
            });

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data karyawan verifikator berhasil ditampilkan.',
                'data' => $formattedUsers
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Master Verifikasi | - Error getAllKaryawanVerifikator: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index()
    {
        if (!Gate::allows('view masterVerifikasi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $verification = RelasiVerifikasi::withTrashed()->orderBy('created_at', 'desc');

        $dataVerifikasi = $verification->get();
        if ($dataVerifikasi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data master verifikasi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $this->formatData($dataVerifikasi);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data master verifikasi berhasil ditampilkan.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreMasterVerificationRequest $request)
    {
        if (!Gate::allows('create masterVerifikasi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $user_diverifikasi = $request->input('user_diverifikasi', []);

        $users = User::whereIn('id', $user_diverifikasi)->get();
        $foundUserIds = $users->pluck('id')->toArray();
        $invalidUserIds = array_diff($user_diverifikasi, $foundUserIds);
        if (!empty($invalidUserIds)) {
            DB::rollBack();
            Log::error('User ID ' . implode(', ', $invalidUserIds) . ' tidak ditemukan atau tidak valid saat create master verifikasi.');
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Tidak dapat melanjutkan proses. Terdapat karyawan yang tidak valid.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $modulVerifikasi = ModulVerifikasi::find($data['modul_verifikasi']);
        if (!$modulVerifikasi) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Modul verifikasi tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }
        if ($data['order'] > $modulVerifikasi->max_order) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => "Order yang diisi tidak boleh lebih dari {$modulVerifikasi->max_order}.",
            ], Response::HTTP_BAD_REQUEST);
        }

        $existingVerification = RelasiVerifikasi::where('verifikator', $data['verifikator'])
            ->where('order', $data['order'])
            ->where('modul_verifikasi', $data['modul_verifikasi'])
            ->whereNull('deleted_at')
            ->first();
        if ($existingVerification) {
            $existingVerifikator = User::find($data['verifikator']);
            $labelModulVerifikasi = $modulVerifikasi->label;
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'message' => "Kombinasi modul, level verifikasi, dan verifikator sudah ada. Verifikator '{$existingVerifikator->nama}' dengan level verifikasi '{$data['order']}' dan modul '{$labelModulVerifikasi}' sudah terdaftar.",
            ], Response::HTTP_CONFLICT);
        }

        $verification = RelasiVerifikasi::create([
            'nama' => $data['nama'],
            'verifikator' => $data['verifikator'],
            'modul_verifikasi' => $data['modul_verifikasi'],
            'order' => $data['order'],
            'user_diverifikasi' => $user_diverifikasi,
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);
        $successMessage = "Data master verifikasi '{$verification->nama}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$verification]))->first();

        return response()->json([
            'status' => Response::HTTP_CREATED,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_CREATED);
    }

    public function show(RelasiVerifikasi $master_verifikasi)
    {
        if (!Gate::allows('view masterVerifikasi', $master_verifikasi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$master_verifikasi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data master verifikasi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $this->formatData(collect([$master_verifikasi]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data master verifikasi '{$master_verifikasi->nama}' berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update($master_verifikasi, UpdateMasterVerificationRequest $request)
    {
        $verification = RelasiVerifikasi::withTrashed()->find($master_verifikasi);

        if (!Gate::allows('edit masterVerifikasi', $verification)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $user_diverifikasi = $request->input('user_diverifikasi', []);
        $users = User::whereIn('id', $user_diverifikasi)->get();
        $foundUserIds = $users->pluck('id')->toArray();
        $invalidUserIds = array_diff($user_diverifikasi, $foundUserIds);
        if (!empty($invalidUserIds)) {
            DB::rollBack();
            Log::error('User ID ' . implode(', ', $invalidUserIds) . ' tidak ditemukan atau tidak valid saat create master verifikasi.');
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Tidak dapat melanjutkan proses. Terdapat karyawan yang tidak valid.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validasi apakah order lebih besar dari max_order
        $modulVerifikasi = ModulVerifikasi::find($data['modul_verifikasi']);
        if (!$modulVerifikasi) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Modul verifikasi tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }
        if ($data['order'] > $modulVerifikasi->max_order) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => "Order tidak boleh lebih dari {$modulVerifikasi->max_order}.",
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validasi unik verifikator dan order
        $existingVerification = RelasiVerifikasi::where('verifikator', $data['verifikator'])
            ->where('order', $data['order'])
            ->where('modul_verifikasi', $data['modul_verifikasi'])
            ->whereNull('deleted_at')
            ->where('id', '!=', $verification->id) // kecuali data saat ini
            ->first();
        if ($existingVerification) {
            $existingVerifikator = User::find($data['verifikator']);
            $labelModulVerifikasi = $modulVerifikasi->label;
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'message' => "Kombinasi modul, level verifikasi, dan verifikator sudah ada. Verifikator '{$existingVerifikator->nama}' dengan level verifikasi '{$data['order']}' dan modul '{$labelModulVerifikasi}' sudah terdaftar.",
            ], Response::HTTP_CONFLICT);
        }

        $verification->update([
            'nama' => $data['nama'],
            'verifikator' => $data['verifikator'],
            'modul_verifikasi' => $data['modul_verifikasi'],
            'order' => $data['order'],
            'user_diverifikasi' => $user_diverifikasi,
            'updated_at' => now('Asia/Jakarta'),
        ]);
        $successMessage = "Data master verifikasi '{$verification->nama}' berhasil diubah.";
        $formattedData = $this->formatData(collect([$verification]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(RelasiVerifikasi $master_verifikasi)
    {
        if (!Gate::allows('delete masterVerifikasi', $master_verifikasi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $master_verifikasi->delete();

        $successMessage = "Data master verifikasi '{$master_verifikasi->nama}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $verifikasi = RelasiVerifikasi::withTrashed()->find($id);

        if (!Gate::allows('delete masterVerifikasi', $verifikasi)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $existingVerification = RelasiVerifikasi::where('verifikator', $verifikasi->verifikator)
            ->where('order', $verifikasi->order)
            ->where('modul_verifikasi', $verifikasi->modul_verifikasi)
            ->whereNull('deleted_at')
            ->first();
        if ($existingVerification) {
            $existingVerifikator = User::find($verifikasi->verifikator);
            $modulVerifikasi = ModulVerifikasi::find($verifikasi->modul_verifikasi);
            $labelModulVerifikasi = $modulVerifikasi->label;
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'message' => "Kombinasi modul, level verifikasi, dan verifikator sudah ada. Verifikator '{$existingVerifikator->nama}' dengan level verifikasi '{$verifikasi->order}' dan modul '{$labelModulVerifikasi}' sudah terdaftar.",
            ], Response::HTTP_CONFLICT);
        }

        $verifikasi->restore();

        if (is_null($verifikasi->deleted_at)) {
            $successMessage = "Data hak verifikasi '{$verifikasi->nama}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($verification) {
            $userIds = $verification->user_diverifikasi;
            $diverifiedUsers = User::whereIn('id', $userIds)->get();
            $baseUrl = env('STORAGE_SERVER_DOMAIN');
            $formattedDiverifiedUsers = $diverifiedUsers->map(function ($user) use ($baseUrl) {
                return [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email_verified_at' => $user->email_verified_at,
                    'data_karyawan_id' => $user->data_karyawan_id,
                    'foto_profil' => $user->foto_profiles ? [
                        'id' => $user->foto_profiles->id,
                        'user_id' => $user->foto_profiles->user_id,
                        'file_id' => $user->foto_profiles->file_id,
                        'nama' => $user->foto_profiles->nama,
                        'nama_file' => $user->foto_profiles->nama_file,
                        'path' => $baseUrl . $user->foto_profiles->path,
                        'ext' => $user->foto_profiles->ext,
                        'size' => $user->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $user->data_completion_step,
                    'status_aktif' => $user->status_aktif,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ];
            });

            $baseUrl = env('STORAGE_SERVER_DOMAIN');

            return [
                'id' => $verification->id,
                'name' => $verification->nama,
                'verifikator' => [
                    'id' => $verification->users->id,
                    'nama' => $verification->users->nama,
                    'username' => $verification->users->username,
                    'email_verified_at' => $verification->users->email_verified_at,
                    'data_karyawan_id' => $verification->users->data_karyawan_id,
                    'foto_profil' => $verification->users->foto_profiles ? [
                        'id' => $verification->users->foto_profiles->id,
                        'user_id' => $verification->users->foto_profiles->user_id,
                        'file_id' => $verification->users->foto_profiles->file_id,
                        'nama' => $verification->users->foto_profiles->nama,
                        'nama_file' => $verification->users->foto_profiles->nama_file,
                        'path' => $baseUrl . $verification->users->foto_profiles->path,
                        'ext' => $verification->users->foto_profiles->ext,
                        'size' => $verification->users->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $verification->users->data_completion_step,
                    'status_aktif' => $verification->users->status_aktif,
                    'created_at' => $verification->users->created_at,
                    'updated_at' => $verification->users->updated_at
                ],
                'modul_verifikasi' => $verification->modul_verifikasis,
                'order' => $verification->order,
                'user_diverifikasi' => $formattedDiverifiedUsers,
                'created_at' => $verification->created_at,
                'updated_at' => $verification->updated_at,
                'deleted_at' => $verification->deleted_at
            ];
        });
    }
}
