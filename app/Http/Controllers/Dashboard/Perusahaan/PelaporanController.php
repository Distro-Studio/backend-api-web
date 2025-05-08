<?php

namespace App\Http\Controllers\Dashboard\Perusahaan;

use Carbon\Carbon;
use App\Models\Berkas;
use App\Models\Pelaporan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Perusahaan\PelaporanExport;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PelaporanController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view pelaporanKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $pelaporan = Pelaporan::query()->orderBy('created_at', 'desc');

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $pelaporan->whereHas('user_pelapor.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $pelaporan->whereHas('user_pelapor.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($filters['status_karyawan'])) {
            $statusKaryawan = $filters['status_karyawan'];
            $pelaporan->whereHas('user_pelapor.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
                if (is_array($statusKaryawan)) {
                    $query->whereIn('id', $statusKaryawan);
                } else {
                    $query->where('id', '=', $statusKaryawan);
                }
            });
        }

        if (isset($filters['masa_kerja'])) {
            $masaKerja = $filters['masa_kerja'];
            $currentDate = Carbon::now('Asia/Jakarta');
            if (is_array($masaKerja)) {
                $pelaporan->whereHas('user_pelapor.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $pelaporan->whereHas('user_pelapor.data_karyawans', function ($query) use ($bulan, $currentDate) {
                    $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                });
            }
        }

        if (isset($filters['status_aktif'])) {
            $statusAktif = $filters['status_aktif'];
            $pelaporan->whereHas('user_pelapor', function ($query) use ($statusAktif) {
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
                $pelaporan->whereHas('user_pelapor.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->whereIn('tgl_masuk', $tglMasuk);
                });
            } else {
                $pelaporan->whereHas('user_pelapor.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->where('tgl_masuk', $tglMasuk);
                });
            }
        }

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $pelaporan->whereHas('user_pelapor.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $pelaporan->whereHas('user_pelapor.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $pelaporan->whereHas('user_pelapor.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $pelaporan->whereHas('user_pelapor.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $pelaporan->whereHas('user_pelapor.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $pelaporan->whereHas('user_pelapor.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                });
            }
        }

        if (isset($filters['jenis_kompetensi'])) {
            $jenisKaryawan = $filters['jenis_kompetensi'];
            if (is_array($jenisKaryawan)) {
                $pelaporan->whereHas('user_pelapor.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_kompetensi', $jk);
                        }
                    });
                });
            } else {
                $pelaporan->whereHas('user_pelapor.data_karyawans.kompetensis', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_kompetensi', $jenisKaryawan);
                });
            }
        }

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $pelaporan->where(function ($query) use ($searchTerm) {
                $query->whereHas('user_pelapor', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('user_pelapor.data_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($limit == 0) {
            $dataPelaporan = $pelaporan->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataPelaporan = $pelaporan->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataPelaporan->url(1),
                    'last' => $dataPelaporan->url($dataPelaporan->lastPage()),
                    'prev' => $dataPelaporan->previousPageUrl(),
                    'next' => $dataPelaporan->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataPelaporan->currentPage(),
                    'last_page' => $dataPelaporan->lastPage(),
                    'per_page' => $dataPelaporan->perPage(),
                    'total' => $dataPelaporan->total(),
                ]
            ];
        }

        if ($dataPelaporan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data pelaporan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $baseUrl = "https://192.168.0.20/RskiSistem24/file-storage/public"; // Ganti dengan URL server Anda

        // Format data untuk output
        $formattedData = $dataPelaporan->map(function ($pelaporan) use ($baseUrl) {
            // Mendapatkan upload_foto
            $uploadFotoBerkas = Berkas::where('id', $pelaporan->upload_foto)->first();

            $uploadFotoExt = $uploadFotoBerkas ? StorageServerHelper::getExtensionFromMimeType($uploadFotoBerkas->ext) : null;
            $uploadFotoUrl = $uploadFotoBerkas ? $baseUrl . $uploadFotoBerkas->path . '.' . $uploadFotoExt : null;

            return [
                'id' => $pelaporan->id,
                'pelapor' => [
                    'id' => $pelaporan->user_pelapor->id,
                    'nama' => $pelaporan->user_pelapor->nama,
                    'email_verified_at' => $pelaporan->user_pelapor->email_verified_at,
                    'data_karyawan_id' => $pelaporan->user_pelapor->data_karyawan_id,
                    'foto_profil' => $pelaporan->user_pelapor->foto_profiles ? [
                        'id' => $pelaporan->user_pelapor->foto_profiles->id,
                        'user_id' => $pelaporan->user_pelapor->foto_profiles->user_id,
                        'file_id' => $pelaporan->user_pelapor->foto_profiles->file_id,
                        'nama' => $pelaporan->user_pelapor->foto_profiles->nama,
                        'nama_file' => $pelaporan->user_pelapor->foto_profiles->nama_file,
                        'path' => $baseUrl . $pelaporan->user_pelapor->foto_profiles->path,
                        'ext' => $pelaporan->user_pelapor->foto_profiles->ext,
                        'size' => $pelaporan->user_pelapor->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $pelaporan->user_pelapor->data_completion_step,
                    'status_aktif' => $pelaporan->user_pelapor->status_aktif,
                    'created_at' => $pelaporan->user_pelapor->created_at,
                    'updated_at' => $pelaporan->user_pelapor->updated_at
                ],
                'pelaku' => [
                    'id' => $pelaporan->user_pelaku->id,
                    'nama' => $pelaporan->user_pelaku->nama,
                    'email_verified_at' => $pelaporan->user_pelaku->email_verified_at,
                    'data_karyawan_id' => $pelaporan->user_pelaku->data_karyawan_id,
                    'foto_profil' => $pelaporan->user_pelaku->foto_profiles ? [
                        'id' => $pelaporan->user_pelaku->foto_profiles->id,
                        'user_id' => $pelaporan->user_pelaku->foto_profiles->user_id,
                        'file_id' => $pelaporan->user_pelaku->foto_profiles->file_id,
                        'nama' => $pelaporan->user_pelaku->foto_profiles->nama,
                        'nama_file' => $pelaporan->user_pelaku->foto_profiles->nama_file,
                        'path' => $baseUrl . $pelaporan->user_pelaku->foto_profiles->path,
                        'ext' => $pelaporan->user_pelaku->foto_profiles->ext,
                        'size' => $pelaporan->user_pelaku->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $pelaporan->user_pelaku->data_completion_step,
                    'status_aktif' => $pelaporan->user_pelaku->status_aktif,
                    'created_at' => $pelaporan->user_pelaku->created_at,
                    'updated_at' => $pelaporan->user_pelaku->updated_at
                ],
                'tgl_kejadian' => $pelaporan->tgl_kejadian,
                'lokasi' => $pelaporan->lokasi,
                'kronologi' => $pelaporan->kronologi,
                'foto' => $uploadFotoUrl,
                'created_at' => $pelaporan->created_at,
                'updated_at' => $pelaporan->updated_at
            ];
        },);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data pelaporan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function exportPelaporan()
    {
        if (!Gate::allows('export pelaporanKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataPelaporan = Pelaporan::all();
        if ($dataPelaporan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data pelaporan karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new PelaporanExport(), 'perusahaan-pelaporan-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
