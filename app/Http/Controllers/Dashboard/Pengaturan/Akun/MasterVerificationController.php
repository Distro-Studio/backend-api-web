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

    public function index()
    {
        if (!Gate::allows('view masterVerifikasi')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $verification = RelasiVerifikasi::query()->orderBy('created_at', 'desc');

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
            ->first();
        if ($existingVerification) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'message' => "Kombinasi verifikator dengan order yang sama sudah ada. Verifikator ID '{$data['verifikator']}' dengan order '{$data['order']}' sudah terdaftar.",
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
        $verification = RelasiVerifikasi::find($master_verifikasi);

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
            ->where('id', '!=', $verification->id) // kecuali data saat ini
            ->first();
        if ($existingVerification) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'message' => "Kombinasi verifikator dengan order yang sama sudah ada. Verifikator ID '{$data['verifikator']}' dengan order '{$data['order']}' sudah terdaftar.",
            ], Response::HTTP_CONFLICT);
        }

        $verification->update([
            'nama' => $data['nama'],
            'verifikator' => $data['verifikator'],
            'modul_verifikasi' => $data['modul_verifikasi'],
            'order' => $data['order'],
            'user_diverifikasi' => $user_diverifikasi,
            'created_at' => Carbon::now('Asia/Jakarta'),
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

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($verification) {
            return [
                'id' => $verification->id,
                'name' => $verification->nama,
                'user' => $verification->users,
                'modul_verifikasi' => $verification->modul_verifikasis,
                'order' => $verification->order,
                'user_diverifikasi' => $verification->user_diverifikasi,
                'created_at' => $verification->created_at,
                'updated_at' => $verification->updated_at,
            ];
        });
    }
}
