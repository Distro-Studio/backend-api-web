<?php

namespace App\Http\Controllers\Dashboard\Jadwal;

use App\Models\Lembur;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jadwal\LemburJadwalExport;
use App\Http\Requests\StoreLemburKaryawanRequest;
use App\Http\Requests\UpdateLemburKaryawanRequest;
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

        $lembur = Lembur::query()->orderBy('created_at', 'desc');

        // Ambil semua filter dari request body
        $filters = $request->all();

        // Filter
        if (isset($filters['unit_kerja'])) {
            $namaUnitKerja = $filters['unit_kerja'];
            $lembur->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($filters['jabatan'])) {
            $namaJabatan = $filters['jabatan'];
            $lembur->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
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
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $lembur->whereHas('users.data_karyawans', function ($query) use ($bulan) {
                    $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
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

        if (isset($filters['agama'])) {
            $namaAgama = $filters['agama'];
            $lembur->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $lembur->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $lembur->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($filters['pendidikan_terakhir'])) {
            $namaPendidikan = $filters['pendidikan_terakhir'];
            $lembur->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
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
                $lembur->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where(function ($query) use ($jenisKaryawan) {
                        foreach ($jenisKaryawan as $jk) {
                            $query->orWhere('jenis_karyawan', $jk);
                        }
                    });
                });
            } else {
                $lembur->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                    $query->where('jenis_karyawan', $jenisKaryawan);
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
            $timeString = RandomHelper::convertToTimeString($lembur->durasi);
            $durasi = RandomHelper::convertTimeStringToSeconds($timeString);
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
                'durasi' => $durasi,
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
        // Set default value for status_lembur_id
        $data['status_lembur_id'] = 1;

        $dataLembur = Lembur::create($data);
        $successMessage = "Lembur karyawan '{$dataLembur->users->nama}' berhasil ditambahkan.";

        return response()->json(new LemburJadwalResource(Response::HTTP_OK, $successMessage, $dataLembur), Response::HTTP_OK);
    }

    public function show($id)
    {
        if (!Gate::allows('view lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataLembur = Lembur::find($id);
        if (!$dataLembur) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }
        $message = "Detail lembur karyawan '{$dataLembur->users->nama}' berhasil ditampilkan.";

        return response()->json(new LemburJadwalResource(Response::HTTP_OK, $message, $dataLembur), Response::HTTP_OK);
    }

    public function update(UpdateLemburKaryawanRequest $request, $id)
    {
        if (!Gate::allows('edit lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $dataLembur = Lembur::find($id);
        if (!$dataLembur) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data lembur karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $dataLembur->update($data);
        $message = "Lembur karyawan '{$dataLembur->users->nama}' berhasil diperbarui.";

        return response()->json(new LemburJadwalResource(Response::HTTP_OK, $message, $dataLembur), Response::HTTP_OK);
    }

    public function exportJadwalLembur()
    {
        if (!Gate::allows('export lemburKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new LemburJadwalExport(), 'lembur-karyawan.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data jadwal lembur karyawan berhasil di download.'), Response::HTTP_OK);
    }
}
