<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use App\Models\User;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Perusahaan\PenilaianExport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Carbon\Carbon;

class PenilaianController extends Controller
{
    // TODO: plan => klik run penilaian untuk mengetahui rata" 
    public function index(Request $request)
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        // Ambil penilaian terbaru untuk setiap user_dinilai
        $penilaian = Penilaian::query()->orderBy('created_at', 'desc');

        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $penilaian->whereHas('status_karyawans.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $penilaian->whereHas('status_karyawans.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $penilaian->whereHas('status_karyawans', function ($query) use ($statusKaryawan) {
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
                $penilaian->whereHas('status_karyawans.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $penilaian->whereHas('status_karyawans.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $penilaian->whereHas('status_karyawans.data_karyawans.users', function ($query) use ($statusAktif) {
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
                $convertedDates = array_map([RandomHelper::class, 'convertSpecialDateFormat'], $tglMasuk);
                $penilaian->whereHas('status_karyawans.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertSpecialDateFormat($tglMasuk);
                $penilaian->whereHas('status_karyawans.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $penilaian->whereHas('status_karyawans.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
                if (is_array($namaAgama)) {
                    $query->whereIn('id', $namaAgama);
                } else {
                    $query->where('id', '=', $namaAgama);
                }
            });
        }

        if (isset($filters['jenis_kelamin'])) {
            $jenisKelamin = $filters['jenis_kelamin'];
            if (is_array($jenisKelamin)) {
                $penilaian->whereHas('status_karyawans.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $penilaian->whereHas('status_karyawans.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $penilaian->whereHas('status_karyawans.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
                }
            });
        }

        if (isset($filters['jenis_karyawan'])) {
            $jenisKaryawan = $filters['jenis_karyawan'];
            if (is_array($jenisKaryawan)) {
                $penilaian->whereHas('status_karyawans.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $penilaian->whereHas('status_karyawans.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $penilaian->where(function ($query) use ($searchTerm) {
                $query->whereHas('status_karyawans.data_karyawans.users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('status_karyawans.data_karyawans.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($limit == 0) {
            $dataPenilaian = $penilaian->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataPenilaian = $penilaian->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataPenilaian->url(1),
                    'last' => $dataPenilaian->url($dataPenilaian->lastPage()),
                    'prev' => $dataPenilaian->previousPageUrl(),
                    'next' => $dataPenilaian->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataPenilaian->currentPage(),
                    'last_page' => $dataPenilaian->lastPage(),
                    'per_page' => $dataPenilaian->perPage(),
                    'total' => $dataPenilaian->total(),
                ]
            ];
        }

        if ($dataPenilaian->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penilaian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format Data
        $formatData = $dataPenilaian->map(function ($penilaian) {
            return [
                'id' => $penilaian->id,
                'periode' => $penilaian->tgl_mulai,
                'tgl_mulai' => $penilaian->tgl_mulai,
                'tgl_selesai' => $penilaian->tgl_selesai,
                'status_karyawan' => $penilaian->status_karyawans,
                'lama_bekerja' => $penilaian->lama_bekerja,
                'total_pertanyaan' => $penilaian->total_pertanyaan,
                'rata_rata' => $penilaian->rata_rata,
                'created_at' => $penilaian->created_at,
                'updated_at' => $penilaian->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data penilaian berhasil ditampilkan.',
            'data' => $formatData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Ambil penilaian berdasarkan ID
        $penilaian = Penilaian::with(['pertanyaans'])->find($id);
        if (!$penilaian) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penilaian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data pertanyaan
        $formattedPertanyaans = $penilaian->pertanyaans->map(function ($pertanyaan) {
            return [
                'id' => $pertanyaan->id,
                'role' => $pertanyaan->roles,
                'pertanyaan' => $pertanyaan->pertanyaan,
                'created_at' => $pertanyaan->created_at,
                'updated_at' => $pertanyaan->updated_at,
            ];
        });

        $tglMulai = RandomHelper::convertSpecialDateFormat($penilaian->tgl_mulai);
        $tglSelesai = RandomHelper::convertSpecialDateFormat($penilaian->tgl_selesai);
        $periode = Carbon::parse($tglMulai)->diffInDays(Carbon::parse($tglSelesai));

        // Response dengan data detail penilaian
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Detail penilaian berhasil ditampilkan.',
            'data' => [
                'id' => $penilaian->id,
                'periode' => $periode,
                'rata_rata' => $penilaian->rata_rata,
                'pertanyaan' => $formattedPertanyaans
            ]
        ], Response::HTTP_OK);
    }

    public function exportPenilaian()
    {
        if (!Gate::allows('export penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPenilaian = Penilaian::all();
        if ($dataPenilaian->isEmpty()) {
            // Kembalikan respons JSON ketika tabel kosong
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penilaian karyawan yang tersedia untuk diekspor.'), Response::HTTP_OK);
        }

        try {
            return Excel::download(new PenilaianExport(), 'perusahaan-penilaian-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
