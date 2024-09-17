<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UnitKerja;
use App\Models\Pengumuman;
use App\Models\DataKaryawan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StorePengumumanRequest;
use App\Http\Requests\UpdatePengumumanRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PengumumanController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Ambil semua pengumuman dari database
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        if ($pengumuman->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data pengumuman yang tersedia.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $pengumuman->map(function ($pengumuman) {
            return [
                'id' => $pengumuman->id,
                'judul' => $pengumuman->judul,
                'konten' => $pengumuman->konten,
                'is_read' => $pengumuman->is_read,
                'tgl_berakhir' => $pengumuman->tgl_berakhir,
                'created_at' => $pengumuman->created_at,
                'updated_at' => $pengumuman->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data pengumuman berhasil didapatkan.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StorePengumumanRequest $request)
    {
        if (!Gate::allows('create pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        DB::beginTransaction();
        try {
            $tanggalBerakhir = Carbon::parse($request->tgl_berakhir)->format('Y-m-d');
            $userIds = $request->input('user_id', []);

            $users = User::whereIn('id', $userIds)->get();
            $foundUserIds = $users->pluck('id')->toArray();
            $invalidUserIds = array_diff($userIds, $foundUserIds);

            if (!empty($invalidUserIds)) {
                DB::rollBack();
                Log::error('User ID ' . implode(', ', $invalidUserIds) . ' tidak ditemukan atau tidak valid.');
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Tidak dapat melanjutkan proses. Terdapat karyawan yang tidak valid.',
                ], Response::HTTP_BAD_REQUEST);
            }

            Pengumuman::create([
                'user_id' => $userIds,
                'judul' => $request->judul,
                'konten' => $request->konten,
                'tgl_berakhir' => $tanggalBerakhir,
                'is_read' => 0,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Pengumuman '{$request->judul}' berhasil dibuat.",
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan pengumuman: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        if (!Gate::allows('view pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melihat pengumuman ini.'), Response::HTTP_FORBIDDEN);
        }

        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengumuman tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $users = User::whereIn('id', $pengumuman->user_id)->get()->map(function ($user) {
            return [
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
            ];
        });

        $formattedData = [
            'id' => $pengumuman->id,
            'user' => $users,
            'judul' => $pengumuman->judul,
            'konten' => $pengumuman->konten,
            'is_read' => $pengumuman->is_read,
            'tgl_berakhir' => $pengumuman->tgl_berakhir,
            'created_at' => $pengumuman->created_at,
            'updated_at' => $pengumuman->updated_at,
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail pengumuman '{$pengumuman->judul}' berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update(UpdatePengumumanRequest $request, $id)
    {
        if (!Gate::allows('edit pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengumuman tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validated();
        $userIds = $request->input('user_id', []);

        // Cek apakah user_id yang diberikan valid
        $users = User::whereIn('id', $userIds)->get();
        $foundUserIds = $users->pluck('id')->toArray();
        $invalidUserIds = array_diff($userIds, $foundUserIds);

        if (!empty($invalidUserIds)) {
            Log::error('User ID ' . implode(', ', $invalidUserIds) . ' tidak ditemukan atau tidak valid saat update.');
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Tidak dapat melanjutkan proses. Terdapat karyawan yang tidak valid.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $pengumuman->update([
            'user_id' => $userIds,
            'judul' => $validatedData['judul'],
            'konten' => $validatedData['konten'],
            'tgl_berakhir' => Carbon::parse($validatedData['tgl_berakhir'])->format('Y-m-d'),
        ]);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Pengumuman '{$pengumuman->judul}' berhasil diperbarui."
        ], Response::HTTP_OK);
    }

    public function destroy(Pengumuman $pengumuman)
    {
        if (!Gate::allows('delete pengumuman', $pengumuman)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pengumuman->delete();

        $successMessage = "Data pengumuman '{$pengumuman->judul}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }
}
