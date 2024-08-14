<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use App\Exports\Perusahaan\JenisPenilaianExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\JenisPenilaian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreJenisPenilaianRequest;
use App\Http\Requests\UpdateJenisPenilaianRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Resources\Dashboard\Penilaian\JenisPenilaianResource;

class JenisPenilaianController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $jenis_penilaian = JenisPenilaian::query()->orderBy('created_at', 'desc');

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $jenis_penilaian->whereHas('unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        // if (isset($filters['jabatan'])) {
        //     $namaJabatan = $filters['jabatan'];
        //     $jenis_penilaian->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
        //         if (is_array($namaJabatan)) {
        //             $query->whereIn('id', $namaJabatan);
        //         } else {
        //             $query->where('id', '=', $namaJabatan);
        //         }
        //     });
        // }

        // if (isset($filters['status_karyawan'])) {
        //     $statusKaryawan = $filters['status_karyawan'];
        //     $jenis_penilaian->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
        //         if (is_array($statusKaryawan)) {
        //             $query->whereIn('id', $statusKaryawan);
        //         } else {
        //             $query->where('id', '=', $statusKaryawan);
        //         }
        //     });
        // }

        // if (isset($filters['masa_kerja'])) {
        //     $masaKerja = $filters['masa_kerja'];
        //     if (is_array($masaKerja)) {
        //         $jenis_penilaian->whereHas('users.data_karyawans', function ($query) use ($masaKerja) {
        //             foreach ($masaKerja as $masa) {
        //                 $bulan = $masa * 12;
        //                 $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
        //             }
        //         });
        //     } else {
        //         $bulan = $masaKerja * 12;
        //         $jenis_penilaian->whereHas('users.data_karyawans', function ($query) use ($bulan) {
        //             $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
        //         });
        //     }
        // }

        // if (isset($filters['status_aktif'])) {
        //     $statusAktif = $filters['status_aktif'];
        //     $jenis_penilaian->whereHas('users', function ($query) use ($statusAktif) {
        //         if (is_array($statusAktif)) {
        //             $query->whereIn('status_aktif', $statusAktif);
        //         } else {
        //             $query->where('status_aktif', '=', $statusAktif);
        //         }
        //     });
        // }

        // if (isset($filters['tgl_masuk'])) {
        //     $tglMasuk = $filters['tgl_masuk'];
        //     if (is_array($tglMasuk)) {
        //         $convertedDates = array_map([RandomHelper::class, 'convertToDateString'], $tglMasuk);
        //         $jenis_penilaian->whereHas('users.data_karyawans', function ($query) use ($convertedDates) {
        //             $query->whereIn('tgl_masuk', $convertedDates);
        //         });
        //     } else {
        //         $convertedDate = RandomHelper::convertToDateString($tglMasuk);
        //         $jenis_penilaian->whereHas('users.data_karyawans', function ($query) use ($convertedDate) {
        //             $query->where('tgl_masuk', $convertedDate);
        //         });
        //     }
        // }

        // if (isset($filters['agama'])) {
        //     $namaAgama = $filters['agama'];
        //     $jenis_penilaian->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
        //         if (is_array($namaAgama)) {
        //             $query->whereIn('id', $namaAgama);
        //         } else {
        //             $query->where('id', '=', $namaAgama);
        //         }
        //     });
        // }

        // if (isset($filters['jenis_kelamin'])) {
        //     $jenisKelamin = $filters['jenis_kelamin'];
        //     if (is_array($jenisKelamin)) {
        //         $jenis_penilaian->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
        //             $query->where(function ($query) use ($jenisKelamin) {
        //                 foreach ($jenisKelamin as $jk) {
        //                     $query->orWhere('jenis_kelamin', $jk);
        //                 }
        //             });
        //         });
        //     } else {
        //         $jenis_penilaian->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
        //             $query->where('jenis_kelamin', $jenisKelamin);
        //         });
        //     }
        // }

        // if (isset($filters['pendidikan_terakhir'])) {
        //     $namaPendidikan = $filters['pendidikan_terakhir'];
        //     $jenis_penilaian->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
        //         if (is_array($namaPendidikan)) {
        //             $query->whereIn('id', $namaPendidikan);
        //         } else {
        //             $query->where('id', '=', $namaPendidikan);
        //         }
        //     });
        // }

        if (isset($filters['jenis_karyawan'])) {
            $jenisKaryawan = $filters['jenis_karyawan'];
            if (is_array($jenisKaryawan)) {
                $jenis_penilaian->whereHas('unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $jenis_penilaian->whereHas('unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $jenis_penilaian->where(function ($query) use ($searchTerm) {
                $query->whereHas('roles', function ($query) use ($searchTerm) {
                    $query->where('name', 'like', $searchTerm);
                })->orWhereHas('unit_kerjas', function ($query) use ($searchTerm) {
                    $query->where('nama_unit', 'like', $searchTerm);
                });
            });
        }

        // Paginate
        if ($limit == 0) {
            $dataJenisPenilaian = $jenis_penilaian->get();
            $paginationData = null;
        } else {
            // Pastikan limit adalah integer
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataJenisPenilaian = $jenis_penilaian->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataJenisPenilaian->url(1),
                    'last' => $dataJenisPenilaian->url($dataJenisPenilaian->lastPage()),
                    'prev' => $dataJenisPenilaian->previousPageUrl(),
                    'next' => $dataJenisPenilaian->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataJenisPenilaian->currentPage(),
                    'last_page' => $dataJenisPenilaian->lastPage(),
                    'per_page' => $dataJenisPenilaian->perPage(),
                    'total' => $dataJenisPenilaian->total(),
                ]
            ];
        }

        if ($dataJenisPenilaian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $dataJenisPenilaian->map(function ($jenis_penilaian) {
            return [
                'id' => $jenis_penilaian->id,
                'nama' => $jenis_penilaian->nama,
                'tgl_mulai' => $jenis_penilaian->tgl_mulai,
                'tgl_selesai' => $jenis_penilaian->tgl_selesai,
                'status_karyawan' => $jenis_penilaian->status_karyawans,
                'role_penilai' => $jenis_penilaian->role_penilais,
                'role_dinilai' => $jenis_penilaian->role_dinilais,
                'unit_kerja' => $jenis_penilaian->unit_kerjas,
                'created_at' => $jenis_penilaian->created_at,
                'updated_at' => $jenis_penilaian->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data jenis penilaian karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreJenisPenilaianRequest $request)
    {
        if (!Gate::allows('create penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $jenis_penilaian = JenisPenilaian::create($data);
        $successMessage = "Pengaturan jenis penilaian untuk status '{$jenis_penilaian->status_karyawans->label}' dan jabatan dinilai '{$jenis_penilaian->jabatan_dinilais->nama_jabatan}' berhasil ditambahkan.";

        return response()->json(new JenisPenilaianResource(Response::HTTP_OK, $successMessage, $jenis_penilaian), Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jenis_penilaian = JenisPenilaian::find($id);
        if (!$jenis_penilaian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jenis penilaian karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }
        $message = "Detail jenis penilaian karyawan untuk status '{$jenis_penilaian->status_karyawans->label}' dan jabatan dinilai '{$jenis_penilaian->jabatan_dinilais->nama_jabatan}' berhasil ditampilkan.";

        return response()->json(new JenisPenilaianResource(Response::HTTP_OK, $message, $jenis_penilaian), Response::HTTP_OK);
    }

    public function update(UpdateJenisPenilaianRequest $request, $id)
    {
        if (!Gate::allows('edit lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $jenis_penilaian = JenisPenilaian::find($id);
        if (!$jenis_penilaian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data jenis penilaian karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $existingDataValidation = JenisPenilaian::where('nama', $data['nama'])->where('id', '!=', $id)->first();
        if ($existingDataValidation) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Nama jenis penilaian tersebut sudah pernah dibuat.'), Response::HTTP_BAD_REQUEST);
        }

        $jenis_penilaian->update($data);
        $message = "Data jenis penilaian karyawan untuk status '{$jenis_penilaian->status_karyawans->label}' dan jabatan dinilai '{$jenis_penilaian->jabatan_dinilais->nama_jabatan}' berhasil diperbarui.";

        return response()->json(new JenisPenilaianResource(Response::HTTP_OK, $message, $jenis_penilaian), Response::HTTP_OK);
    }

    public function exportJenisPenilaian()
    {
        if (!Gate::allows('export lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jenis_penilaian = JenisPenilaian::all(); // Sesuaikan dengan model atau query Anda
        if ($jenis_penilaian->isEmpty()) {
            // Kembalikan respons JSON ketika tabel kosong
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data jenis penilaian karyawan yang tersedia untuk diekspor.'), Response::HTTP_OK);
        }

        try {
            return Excel::download(new JenisPenilaianExport(), 'jenis-penilaian.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
