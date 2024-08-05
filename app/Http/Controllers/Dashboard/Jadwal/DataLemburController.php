<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use App\Models\Lembur;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreLemburKaryawanRequest;
use App\Http\Resources\Dashboard\Jadwal\LemburJadwalResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataLemburController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $lembur = Lembur::query();

        // Filter
        if (isset($filters['nama_unit'])) {
            $namaUnitKerja = $filters['nama_unit'];
            $lembur->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $lembur->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $lembur->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $query->orWhereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$masa]);
                    }
                });
            } else {
                $lembur->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$masaKerja]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $lembur->whereHas('users', function ($query) use ($statusAktif) {
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
                $lembur->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $lembur->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $lembur->where(function ($query) use ($searchTerm) {
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('users.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        // Paginate
        if ($limit == 0) {
            $dataLembur = $lembur->get();
            $paginationData = null;
        } else {
            // Pastikan limit adalah integer
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataLembur = $lembur->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataLembur->url(1),
                    'last' => $dataLembur->url($dataLembur->lastPage()),
                    'prev' => $dataLembur->previousPageUrl(),
                    'next' => $dataLembur->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataLembur->currentPage(),
                    'last_page' => $dataLembur->lastPage(),
                    'per_page' => $dataLembur->perPage(),
                    'total' => $dataLembur->total(),
                ]
            ];
        }

        if ($dataLembur->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataLembur->map(function ($lembur) {
            return [
                'id' => $lembur->id,
                'user' => [
                    'id' => $lembur->users->id,
                    'nama' => $lembur->users->nama,
                    'email_verified_at' => $lembur->users->email_verified_at,
                    'data_karyawan_id' => $lembur->users->data_karyawan_id,
                    'foto_profil' => $lembur->users->foto_profil,
                    'data_completion_step' => $lembur->users->data_completion_step,
                    'status_aktif' => $lembur->users->status_aktif,
                    'created_at' => $lembur->users->created_at,
                    'updated_at' => $lembur->users->updated_at
                ],
                'jadwal' => $lembur->jadwals,
                'tgl_pengajuan' => $lembur->tgl_pengajuan,
                'kompensasi' => $lembur->kategori_kompensasis,
                // 'tipe' => $lembur->tipe,
                'durasi' => $lembur->durasi,
                'catatan' => $lembur->catatan,
                'status_lembur' => $lembur->status_lemburs,
                'created_at' => $lembur->created_at,
                'updated_at' => $lembur->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data lembur karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreLemburKaryawanRequest $request)
    {
        if (!Gate::allows('create lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $durasiJam = $request->input('durasi_jam');
        $durasiMenit = $request->input('durasi_menit');
        $totalDurasi = "{$durasiJam}j {$durasiMenit}m";
        $data['durasi'] = $totalDurasi;

        $dataLembur = Lembur::create($data);

        $message = "Lembur karyawan {$dataLembur->users->name} berhasil ditambahkan.";
        return response()->json(new LemburJadwalResource(Response::HTTP_OK, $message, $dataLembur), Response::HTTP_OK);
    }
}
