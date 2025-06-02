<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\NonShift;
use App\Models\Presensi;
use App\Helpers\RandomHelper;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PresensiSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

    private $number;
    private $filters;
    private $category;
    private $title;
    private $startDate;
    private $endDate;

    public function __construct($filters = [], $title, $startDate, $endDate, $category = null)
    {
        $this->filters = $filters;
        $this->title = $title;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->category = $category;
        $this->number = 0; // Reset numbering for each sheet
    }

    public function collection()
    {
        $query = Presensi::with([
            'users',
            'jadwals.shifts',
            'data_karyawans.unit_kerjas',
            'kategori_presensis'
        ])->whereHas('kategori_presensis', function ($query) {
            $query->where('label', $this->category);
        });

        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('jam_masuk', [$this->startDate, $this->endDate]);
        }

        if (isset($this->filters['unit_kerja'])) {
            $namaUnitKerja = $this->filters['unit_kerja'];
            $query->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($this->filters['jabatan'])) {
            $namaJabatan = $this->filters['jabatan'];
            $query->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($this->filters['status_karyawan'])) {
            $statusKaryawan = $this->filters['status_karyawan'];
            $query->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $query->whereHas('users.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $query->whereHas('users.data_karyawans', function ($query) use ($bulan, $currentDate) {
                    $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                });
            }
        }

        if (isset($this->filters['status_aktif'])) {
            $statusAktif = $this->filters['status_aktif'];
            $query->whereHas('users', function ($query) use ($statusAktif) {
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
                $query->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->whereIn('tgl_masuk', $tglMasuk);
                });
            } else {
                $query->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->where('tgl_masuk', $tglMasuk);
                });
            }
        }

        if (isset($this->filters['agama'])) {
            $namaAgama = $this->filters['agama'];
            $query->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $query->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $query->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($this->filters['pendidikan_terakhir'])) {
            $namaPendidikan = $this->filters['pendidikan_terakhir'];
            $query->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
                }
            });
        }

        if (isset($this->filters['jenis_karyawan'])) {
            $jenisKaryawan = $this->filters['jenis_karyawan'];
            $query->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                if (is_array($jenisKaryawan)) {
                    $query->whereIn('jenis_karyawan', $jenisKaryawan);
                } else {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                }
            });
        }

        if (isset($this->filters['jenis_kompetensi'])) {
            $jenisKompetensi = $this->filters['jenis_kompetensi'];
            $query->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKompetensi) {
                if (is_array($jenisKompetensi)) {
                    $query->whereIn('jenis_kompetensi', $jenisKompetensi);
                } else {
                    $query->where('jenis_kompetensi', $jenisKompetensi);
                }
            });
        }

        if (isset($this->filters['unit_kerja'])) {
            $namaUnitKerja = $this->filters['unit_kerja'];
            $query->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($this->filters['jabatan'])) {
            $namaJabatan = $this->filters['jabatan'];
            $query->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($this->filters['status_karyawan'])) {
            $statusKaryawan = $this->filters['status_karyawan'];
            $query->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
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
                $query->whereHas('users.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $query->whereHas('users.data_karyawans', function ($query) use ($bulan, $currentDate) {
                    $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                });
            }
        }

        if (isset($this->filters['status_aktif'])) {
            $statusAktif = $this->filters['status_aktif'];
            $query->whereHas('users', function ($query) use ($statusAktif) {
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
                $query->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->whereIn('tgl_masuk', $tglMasuk);
                });
            } else {
                $query->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
                    $query->where('tgl_masuk', $tglMasuk);
                });
            }
        }

        if (isset($this->filters['agama'])) {
            $namaAgama = $this->filters['agama'];
            $query->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
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
                $query->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $query->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
                    $query->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        if (isset($this->filters['pendidikan_terakhir'])) {
            $namaPendidikan = $this->filters['pendidikan_terakhir'];
            $query->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
                }
            });
        }

        if (isset($this->filters['jenis_karyawan'])) {
            $jenisKaryawan = $this->filters['jenis_karyawan'];
            $query->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
                if (is_array($jenisKaryawan)) {
                    $query->whereIn('jenis_karyawan', $jenisKaryawan);
                } else {
                    $query->where('jenis_karyawan', $jenisKaryawan);
                }
            });
        }

        if (isset($this->filters['jenis_kompetensi'])) {
            $jenisKompetensi = $this->filters['jenis_kompetensi'];
            $query->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKompetensi) {
                if (is_array($jenisKompetensi)) {
                    $query->whereIn('jenis_kompetensi', $jenisKompetensi);
                } else {
                    $query->where('jenis_kompetensi', $jenisKompetensi);
                }
            });
        }

        // JOIN untuk bisa sort by nik
        $query->join('data_karyawans', 'presensis.data_karyawan_id', '=', 'data_karyawans.id')
            ->orderBy('data_karyawans.nik', 'asc')
            ->select('presensis.*');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'nik',
            'nama_shift',
            'shift_masuk',
            'shift_keluar',
            'jadwal_mulai',
            'jadwal_selesai',
            'jam_masuk_nShift',
            'jam_selesai_nShift',
            'unit_kerja',
            'presensi_masuk',
            'presensi_keluar',
            'durasi',
            'lat_masuk',
            'long_masuk',
            'lat_keluar',
            'long_keluar',
            'kategori',
            'pembatalan_reward',
            'created_at',
            'updated_at'
        ];
    }

    public function map($presensi): array
    {
        $this->number++;
        $shift = optional($presensi->jadwals)->shifts;
        $unitKerja = optional(optional($presensi->data_karyawans)->unit_kerjas)->nama_unit;

        // Non-Shifts
        $jamMasukDate = Carbon::parse($presensi->jam_masuk)->locale('id');
        $hari = $jamMasukDate->isoFormat('dddd');
        $nonShift = NonShift::where('nama', $hari)->first();

        if ($shift) {
            $jamMasukNonShift = 'N/A';
            $jamKeluarNonShift = 'N/A';
        } else {
            $nonShift = NonShift::where('nama', $hari)->first();
            $jamMasukNonShift = $nonShift ? $nonShift->jam_from : 'N/A';
            $jamKeluarNonShift = $nonShift ? $nonShift->jam_to : 'N/A';
        }
        return [
            $this->number,
            optional($presensi->users)->nama,
            optional($presensi->users->data_karyawans)->nik,
            $shift ? $shift->nama : 'N/A',
            $shift && isset($shift->jam_from) ? $shift->jam_from : 'N/A',
            $shift && isset($shift->jam_to) ? $shift->jam_to : 'N/A',
            optional($presensi->jadwals)->tgl_mulai ? RandomHelper::convertToDateString($presensi->jadwals->tgl_mulai) : 'N/A',
            optional($presensi->jadwals)->tgl_selesai ? RandomHelper::convertToDateString($presensi->jadwals->tgl_selesai) : 'N/A',
            $jamMasukNonShift,
            $jamKeluarNonShift,
            $unitKerja,
            $presensi->jam_masuk ? RandomHelper::convertToDateTimeString($presensi->jam_masuk) : 'N/A',
            $presensi->jam_keluar ? RandomHelper::convertToDateTimeString($presensi->jam_keluar) : 'N/A',
            $this->formatDuration($presensi->durasi),
            $presensi->lat,
            $presensi->long,
            $presensi->latkeluar,
            $presensi->longkeluar,
            optional($presensi->kategori_presensis)->label,
            $presensi->is_pembatalan_reward ? 'Ya' : 'Tidak',
            Carbon::parse($presensi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($presensi->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d jam %d menit", $hours, $minutes);
    }

    public function title(): string
    {
        return $this->title;
    }
}
