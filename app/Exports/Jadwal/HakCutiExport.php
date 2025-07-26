<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\HakCuti;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class HakCutiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = HakCuti::with(['data_karyawans', 'tipe_cutis'])
            ->whereHas('data_karyawans', function ($query) {
                $query->orderBy('nik', 'asc');
            });

        if (isset($this->filters['unit_kerja'])) {
            $namaUnitKerja = $this->filters['unit_kerja'];
            $query->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($this->filters['jabatan'])) {
            $namaJabatan = $this->filters['jabatan'];
            $query->whereHas('data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($this->filters['status_karyawan'])) {
            $statusKaryawan = $this->filters['status_karyawan'];
            $query->whereHas('data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
                if (is_array($statusKaryawan)) {
                    $query->whereIn('id', $statusKaryawan);
                } else {
                    $query->where('id', '=', $statusKaryawan);
                }
            });
        }

        if (isset($this->filters['masa_kerja'])) {
            $masaKerja = $this->filters['masa_kerja'];
            $currentDate = Carbon::now('Asia/Jakarta');
            if (is_array($masaKerja)) {
                $query->whereHas('data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $query->whereHas('data_karyawans', function ($query) use ($bulan, $currentDate) {
                    $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                });
            }
        }

        if (isset($this->filters['status_aktif'])) {
            $statusAktif = $this->filters['status_aktif'];
            $query->whereHas('data_karyawans.users', function ($query) use ($statusAktif) {
                if (is_array($statusAktif)) {
                    $query->whereIn('status_aktif', $statusAktif);
                } else {
                    $query->where('status_aktif', '=', $statusAktif);
                }
            });
        }

        if (isset($this->filters['tgl_masuk'])) {
            $tglMasuk = $this->filters['tgl_masuk'];
            if (is_array($tglMasuk)) {
                $query->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                    $query->whereIn('tgl_masuk', $tglMasuk);
                });
            } else {
                $query->whereHas('data_karyawans', function ($query) use ($tglMasuk) {
                    $query->where('tgl_masuk', $tglMasuk);
                });
            }
        }

        if (isset($this->filters['agama'])) {
            $namaAgama = $this->filters['agama'];
            $query->whereHas('data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
                if (is_array($namaAgama)) {
                    $query->whereIn('id', $namaAgama);
                } else {
                    $query->where('id', '=', $namaAgama);
                }
            });
        }

        if (isset($this->filters['jenis_kelamin'])) {
            $jenisKelamin = $this->filters['jenis_kelamin'];
            if (is_array($jenisKelamin)) {
                $query->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $query->whereHas('data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($this->filters['pendidikan_terakhir'])) {
            $namaPendidikan = $this->filters['pendidikan_terakhir'];
            $query->whereHas('data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
                }
            });
        }

        if (isset($this->filters['jenis_karyawan'])) {
            $jenisKaryawan = $this->filters['jenis_karyawan'];
            $query->whereHas('data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                if (is_array($jenisKaryawan)) {
                    $query->whereIn('jenis_karyawan', $jenisKaryawan);
                } else {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                }
            });
        }

        if (isset($this->filters['jenis_kompetensi'])) {
            $jenisKompetensi = $this->filters['jenis_kompetensi'];
            $query->whereHas('data_karyawans.kompetensis', function ($query) use ($jenisKompetensi) {
                if (is_array($jenisKompetensi)) {
                    $query->whereIn('jenis_kompetensi', $jenisKompetensi);
                } else {
                    $query->where('jenis_kompetensi', $jenisKompetensi);
                }
            });
        }

        if (isset($this->filters['tipe_cuti'])) {
            $namaTipeCuti = $this->filters['tipe_cuti'];
            $query->whereHas('tipe_cutis', function ($query) use ($namaTipeCuti) {
                if (is_array($namaTipeCuti)) {
                    $query->whereIn('id', $namaTipeCuti);
                } else {
                    $query->where('id', '=', $namaTipeCuti);
                }
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'nik',
            'kuota',
            // 'hak_cuti',
            'kuota_terpakai',
            'sisa_cuti',
            // 'cuti_administratif',
            // 'kuota_cuti_unlimited',
            'created_at',
            'updated_at',
        ];
    }

    public function map($hakCuti): array
    {
        static $no = 1;

        return [
            $no++,
            optional($hakCuti->data_karyawans->users)->nama ?? 'N/A',
            $hakCuti->data_karyawans->nik ?? 'N/A',
            // $hakCuti->tipe_cutis->nama ?? 'N/A',
            $hakCuti->tipe_cutis->kuota ?? 'N/A',
            $hakCuti->used_kuota ?? 0, // Kuota yang sudah digunakan
            $hakCuti->kuota ?? 0, // Jatah kuota yang tersedia
            // $hakCuti->tipe_cutis->cuti_administratif ? 'Ya' : 'Tidak',
            // $hakCuti->tipe_cutis->is_unlimited ? 'Ya' : 'Tidak',
            Carbon::parse($hakCuti->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($hakCuti->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
