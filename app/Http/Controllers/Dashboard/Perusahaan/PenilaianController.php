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

class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        // Ambil penilaian terbaru untuk setiap user_dinilai
        $penilaian = Penilaian::with(['user_dinilais', 'user_penilais', 'unit_kerja_dinilais', 'jabatan_dinilais'])
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('penilaians')
                    ->groupBy('user_dinilai');
            })
            ->orderBy('created_at', 'desc');

        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $penilaian->whereHas('unit_kerja_dinilais', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $penilaian->whereHas('jabatan_dinilais', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $penilaian->whereHas('user_dinilais.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $penilaian->whereHas('user_dinilais', function ($query) use ($statusAktif) {
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
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($convertedDates) {
                    $query->whereIn('tgl_masuk', $convertedDates);
                });
            } else {
                $convertedDate = RandomHelper::convertToDateString($tglMasuk);
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($convertedDate) {
                    $query->where('tgl_masuk', $convertedDate);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $penilaian->whereHas('user_dinilais.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $penilaian->whereHas('user_dinilais.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $penilaian->whereHas('user_dinilais.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $penilaian->whereHas('user_dinilais.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $penilaian->whereHas('user_dinilais.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $penilaian->where(function ($query) use ($searchTerm) {
                $query->whereHas('user_dinilais', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('user_dinilais.data_karyawans', function ($query) use ($searchTerm) {
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
            // Ambil rata-rata dari user_dinilai jika dibutuhkan
            $rataRataDinilai = $this->calculateRataRata($penilaian->user_dinilai);

            // Ambil semua user_penilai yang terkait dengan penilaian ini
            $list_penilai = Penilaian::where('user_dinilai', $penilaian->user_dinilai)
                ->distinct()
                ->pluck('user_penilai')
                ->map(function ($user_penilai_id) {
                    $userPenilai = User::find($user_penilai_id);
                    return [
                        'user' => [
                            'id' => $userPenilai->id,
                            'nama' => $userPenilai->nama,
                            'email_verified_at' => $userPenilai->email_verified_at,
                            'data_karyawan_id' => $userPenilai->data_karyawan_id,
                            'foto_profil' => $userPenilai->foto_profil,
                            'data_completion_step' => $userPenilai->data_completion_step,
                            'status_aktif' => $userPenilai->status_aktif,
                            'created_at' => $userPenilai->created_at,
                            'updated_at' => $userPenilai->updated_at,
                        ],
                        'rata_rata' => $this->calculateRataRata($user_penilai_id),
                    ];
                });

            return [
                'user_dinilai' => [
                    'user' => $penilaian->user_dinilais,
                    'unit_kerja_dinilai' => $penilaian->unit_kerja_dinilais,
                    'jabatan_dinilai' => $penilaian->jabatan_dinilais,
                    'rata_rata' => $rataRataDinilai,  // rata_rata for user_dinilai
                    'created_at' => $penilaian->created_at,
                ],
                'list_penilai' => $list_penilai->toArray(),
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data penilaian berhasil ditampilkan.',
            'data' => $formatData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function exportPenilaian()
    {
        if (!Gate::allows('export penilaianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new PenilaianExport(), 'perusahaan-penilaian-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi kesalahan. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi kesalahan. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data pelaporan karyawan berhasil di download.'), Response::HTTP_OK);
    }

    private function calculateRataRata($userId)
    {
        // Fetch all the penilaians where the user is being evaluated (user_dinilai)
        $penilaians = Penilaian::where('user_dinilai', $userId)
            ->with('jawabans') // Ensure that 'jawabans' are loaded
            ->get();

        // Initialize the total score and total questions
        $totalScore = 0;
        $totalQuestions = 0;

        foreach ($penilaians as $penilaian) {
            foreach ($penilaian->jawabans as $jawaban) {
                $totalScore += $jawaban->jawaban; // Tambahkan skor tiap jawaban
            }
            $totalQuestions += $penilaian->total_pertanyaan;
        }

        if ($totalQuestions > 0) {
            $averageScore = ($totalScore / $totalQuestions) * 100; // Rata-rata dalam skala 1-5
            return round($averageScore, 0); // Kembalikan nilai akhir dengan 2 desimal
        } else {
            return null;
        }
    }
}
