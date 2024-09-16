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

        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        if ($pengumuman->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data pengumuman yang tersedia.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $pengumuman->map(function ($pengumuman) {
            return [
                'id' => $pengumuman->id,
                'user' => [
                    'id' => $pengumuman->users->id,
                    'nama' => $pengumuman->users->nama,
                    'username' => $pengumuman->users->username,
                    'email_verified_at' => $pengumuman->users->email_verified_at,
                    'data_karyawan_id' => $pengumuman->users->data_karyawan_id,
                    'foto_profil' => $pengumuman->users->foto_profil,
                    'data_completion_step' => $pengumuman->users->data_completion_step,
                    'status_aktif' => $pengumuman->users->status_aktif,
                    'created_at' => $pengumuman->users->created_at,
                    'updated_at' => $pengumuman->users->updated_at
                ],
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
            'data' => $formattedData
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

            // Validasi apakah salah satu dari unit_kerja_id atau user_id ada yang tidak kosong
            if (empty($userIds)) {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Anda harus memilih salah satu karyawan terlebih dahulu untuk membuat pengumuman.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Jika user_id ada isinya
            if (!empty($userIds)) {
                $users = User::whereIn('id', $userIds)->get();

                // Cek apakah ada user_id yang tidak valid
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

                foreach ($users as $user) {
                    Pengumuman::create([
                        'user_id' => $user->id,
                        'judul' => $request->judul,
                        'konten' => $request->konten,
                        'tgl_berakhir' => $tanggalBerakhir,
                        'is_read' => 0,
                        'created_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Pengumuman '{$request->judul}' berhasil dibuat untuk karyawan terkait.",
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi kesalahan
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan pengumuman: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        if (!Gate::allows('view pengumuman')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengumuman tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = [
            'id' => $pengumuman->id,
            'user' => [
                'id' => $pengumuman->users->id,
                'nama' => $pengumuman->users->nama,
                'username' => $pengumuman->users->username,
                'email_verified_at' => $pengumuman->users->email_verified_at,
                'data_karyawan_id' => $pengumuman->users->data_karyawan_id,
                'foto_profil' => $pengumuman->users->foto_profil,
                'data_completion_step' => $pengumuman->users->data_completion_step,
                'status_aktif' => $pengumuman->users->status_aktif,
                'created_at' => $pengumuman->users->created_at,
                'updated_at' => $pengumuman->users->updated_at
            ],
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
            'data' => $formattedData
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
        $pengumuman->update($validatedData);

        $formattedData = [
            'id' => $pengumuman->id,
            'user' => [
                'id' => $pengumuman->users->id,
                'nama' => $pengumuman->users->nama,
                'username' => $pengumuman->users->username,
                'email_verified_at' => $pengumuman->users->email_verified_at,
                'data_karyawan_id' => $pengumuman->users->data_karyawan_id,
                'foto_profil' => $pengumuman->users->foto_profil,
                'data_completion_step' => $pengumuman->users->data_completion_step,
                'status_aktif' => $pengumuman->users->status_aktif,
                'created_at' => $pengumuman->users->created_at,
                'updated_at' => $pengumuman->users->updated_at
            ],
            'judul' => $pengumuman->judul,
            'konten' => $pengumuman->konten,
            'is_read' => $pengumuman->is_read,
            'tgl_berakhir' => $pengumuman->tgl_berakhir,
            'created_at' => $pengumuman->created_at,
            'updated_at' => $pengumuman->updated_at,
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Pengumuman '{$pengumuman->judul}' berhasil diperbarui.",
            'data' => $formattedData
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
