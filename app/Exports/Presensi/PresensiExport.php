<?php

namespace App\Exports\Presensi;

use App\Exports\Sheet\PresensiSheet;
use App\Exports\Presensi\PresensiUserSheet;
use App\Models\User;
use App\Models\Presensi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PresensiExport implements WithMultipleSheets
{
    use Exportable;

    private $startDate;
    private $endDate;
    private $filters;

    public function __construct($startDate, $endDate, $filters = [])
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];
        // Jika ada filter selain date range (tgl_mulai / tgl_selesai) => buat sheet per karyawan
        $filterKeys = is_array($this->filters) ? array_keys($this->filters) : [];
        $nonDateFilterKeys = array_diff($filterKeys, ['tgl_mulai', 'tgl_selesai']);
        $hasOtherFilters = count($nonDateFilterKeys) > 0;
        if ($hasOtherFilters) {
            // Jika filter mengandung explicit user_id gunakan itu
            if (isset($this->filters['user_id'])) {
                $userFilter = $this->filters['user_id'];
                $userIds = is_array($userFilter) ? $userFilter : [$userFilter];
                $users = User::whereIn('id', $userIds)->get();
            } else {
                // Build user query based on provided filters (unit_kerja, jabatan, status_karyawan, jenis_karyawan, status_aktif, jenis_kelamin, etc.)
                $userQuery = User::query();

                if (isset($this->filters['unit_kerja'])) {
                    $unitKerja = $this->filters['unit_kerja'];
                    $userQuery->whereHas('data_karyawans.unit_kerjas', function ($q) use ($unitKerja) {
                        if (is_array($unitKerja)) {
                            $q->whereIn('id', $unitKerja);
                        } else {
                            $q->where('id', $unitKerja);
                        }
                    });
                }

                if (isset($this->filters['jabatan'])) {
                    $jabatan = $this->filters['jabatan'];
                    $userQuery->whereHas('data_karyawans.jabatans', function ($q) use ($jabatan) {
                        if (is_array($jabatan)) {
                            $q->whereIn('id', $jabatan);
                        } else {
                            $q->where('id', $jabatan);
                        }
                    });
                }

                if (isset($this->filters['status_karyawan'])) {
                    $statusKaryawan = $this->filters['status_karyawan'];
                    $userQuery->whereHas('data_karyawans.status_karyawans', function ($q) use ($statusKaryawan) {
                        if (is_array($statusKaryawan)) {
                            $q->whereIn('id', $statusKaryawan);
                        } else {
                            $q->where('id', $statusKaryawan);
                        }
                    });
                }

                if (isset($this->filters['status_aktif'])) {
                    $statusAktif = $this->filters['status_aktif'];
                    if (is_array($statusAktif)) {
                        $userQuery->whereIn('status_aktif', $statusAktif);
                    } else {
                        $userQuery->where('status_aktif', $statusAktif);
                    }
                }

                if (isset($this->filters['jenis_kelamin'])) {
                    $jenisKelamin = $this->filters['jenis_kelamin'];
                    $userQuery->whereHas('data_karyawans', function ($q) use ($jenisKelamin) {
                        if (is_array($jenisKelamin)) {
                            $q->where(function ($q2) use ($jenisKelamin) {
                                foreach ($jenisKelamin as $jk) {
                                    $q2->orWhere('jenis_kelamin', $jk);
                                }
                            });
                        } else {
                            $q->where('jenis_kelamin', $jenisKelamin);
                        }
                    });
                }

                if (isset($this->filters['jenis_karyawan'])) {
                    $jenisKaryawan = $this->filters['jenis_karyawan'];
                    $userQuery->whereHas('data_karyawans.unit_kerjas', function ($q) use ($jenisKaryawan) {
                        if (is_array($jenisKaryawan)) {
                            $q->whereIn('jenis_karyawan', $jenisKaryawan);
                        } else {
                            $q->where('jenis_karyawan', $jenisKaryawan);
                        }
                    });
                }

                $users = $userQuery->get();
            }

            // Jika tidak ada user matched, return default empty array
            if ($users->isEmpty()) {
                return $sheets;
            }

            foreach ($users as $user) {
                $sheets[] = new PresensiUserSheet($user, $this->startDate, $this->endDate, $this->filters);
            }

            return $sheets;
        }

        // Menambahkan sheet untuk setiap kategori presensi (default)
        $categories = ['Terlambat', 'Tepat Waktu', 'Alpha'];
        foreach ($categories as $category) {
            $sheets[] = new PresensiSheet($category, 'Laporan ' . str_replace(' ', '', $category), $this->startDate, $this->endDate, $this->filters);
        }

        return $sheets;
    }
}
