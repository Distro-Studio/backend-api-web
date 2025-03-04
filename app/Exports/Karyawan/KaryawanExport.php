<?php

namespace App\Exports\Karyawan;

use App\Helpers\RandomHelper;
use Carbon\Carbon;
use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class KaryawanExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private $filters;
    private static $number = 0;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $karyawan = DataKaryawan::with([
            'users',
            'users.roles',
            'unit_kerjas',
            'status_karyawans',
            'kategori_agamas',
            'jabatans',
            'kompetensis',
            'kelompok_gajis',
            'ptkps',
            'kategori_darahs'
        ])->where('id', '!=', 1);

        if (isset($this->filters['unit_kerja'])) {
            $namaUnitKerja = $this->filters['unit_kerja'];
            $karyawan->whereHas('unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        if (isset($this->filters['jabatan'])) {
            $namaJabatan = $this->filters['jabatan'];
            $karyawan->whereHas('jabatans', function ($query) use ($namaJabatan) {
                if (is_array($namaJabatan)) {
                    $query->whereIn('id', $namaJabatan);
                } else {
                    $query->where('id', '=', $namaJabatan);
                }
            });
        }

        if (isset($this->filters['status_karyawan'])) {
            $statusKaryawan = $this->filters['status_karyawan'];
            $karyawan->whereHas('status_karyawans', function ($query) use ($statusKaryawan) {
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
                $karyawan->where(function ($query) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $karyawan->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
            }
        }

        if (isset($this->filters['status_aktif'])) {
            $statusAktif = $this->filters['status_aktif'];
            $karyawan->whereHas('users', function ($query) use ($statusAktif) {
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
                $karyawan->whereIn('tgl_masuk', $tglMasuk);
            } else {
                $karyawan->where('tgl_masuk', $tglMasuk);
            }
        }

        if (isset($this->filters['agama'])) {
            $namaAgama = $this->filters['agama'];
            $karyawan->whereHas('kategori_agamas', function ($query) use ($namaAgama) {
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
                $karyawan->where(function ($query) use ($jenisKelamin) {
                    foreach ($jenisKelamin as $jk) {
                        $query->orWhere('jenis_kelamin', $jk);
                    }
                });
            } else {
                $karyawan->where('jenis_kelamin', $jenisKelamin);
            }
        }

        if (isset($this->filters['jenis_karyawan'])) {
            $jenisKaryawan = $this->filters['jenis_karyawan'];
            $karyawan->whereHas('unit_kerjas', function ($query) use ($jenisKaryawan) {
                if (is_array($jenisKaryawan)) {
                    $query->whereIn('jenis_karyawan', $jenisKaryawan);
                } else {
                    $query->where('jenis_karyawan', '=', $jenisKaryawan);
                }
            });
        }

        if (isset($this->filters['jenis_kompetensi'])) {
            $jenisKaryawan = $this->filters['jenis_kompetensi'];
            $karyawan->whereHas('kompetensis', function ($query) use ($jenisKaryawan) {
                if (is_array($jenisKaryawan)) {
                    $query->whereIn('jenis_kompetensi', $jenisKaryawan);
                } else {
                    $query->where('jenis_kompetensi', '=', $jenisKaryawan);
                }
            });
        }

        if (isset($this->filters['pendidikan_terakhir'])) {
            $namaPendidikan = $this->filters['pendidikan_terakhir'];
            $karyawan->whereHas('kategori_pendidikans', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
                }
            });
        }

        if (isset($this->filters['masa_diklat'])) {
            $masaDiklatJam = $this->filters['masa_diklat'];
            if (is_array($masaDiklatJam)) {
                $karyawan->where(function ($query) use ($masaDiklatJam) {
                    foreach ($masaDiklatJam as $jam) {
                        $detik = $jam * 3600; // Konversi dari jam ke detik
                        $query->orWhere('masa_diklat', '<=', $detik);
                    }
                });
            } else {
                $detik = $masaDiklatJam * 3600; // Konversi dari jam ke detik
                $karyawan->where('masa_diklat', '<=', $detik);
            }
        }

        return $karyawan->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nik',
            'nama',
            'username',
            'status_aktif',
            'tgl_dinonaktifkan',
            'role',
            'email',
            'no_rm',
            'no_manulife',
            'tgl_masuk',
            'unit_kerja',
            'jabatan',
            'kompetensi',
            'nik_ktp',
            'status_karyawan',
            'tempat_lahir',
            'tgl_lahir',
            'kelompok_gaji',
            'no_rekening',
            'tunjangan_jabatan',
            'tunjangan_fungsional',
            'tunjangan_khusus',
            'tunjangan_lainnya',
            'uang_lembur',
            'uang_makan',
            'ptkp',
            'tgl_keluar',
            'no_kk',
            'alamat',
            'gelar_depan',
            'gelar_belakang',
            'no_hp',
            'no_bpjsksh',
            'no_bpjsktk',
            'tgl_diangkat',
            'masa_kerja',
            'npwp',
            'jenis_kelamin',
            'agama',
            'golongan_darah',
            'tinggi_badan',
            'berat_badan',
            'bmi_value',
            'bmi_ket',
            'riwayat_penyakit',
            'no_ijazah',
            'tahun_lulus',
            'no_str',
            'tgl_dibuat_str',
            'tgl_berakhir_str',
            'no_sip',
            'tgl_dibuat_sip',
            'tgl_berakhir_sip',
            'tgl_berakhir_pks',
            'masa_diklat',
            'created_at',
            'updated_at',
        ];
    }

    public function map($karyawan): array
    {
        self::$number++;

        $roles = $karyawan->users->roles->map(function ($role) {
            return $role->name;
        })->toArray();

        $formatDate = fn($date) => $date ? Carbon::parse($date)->format('d-m-Y') : 'N/A';

        $masaKerja = $this->calculateMasaKerja($karyawan->tgl_masuk, $karyawan->tgl_keluar);

        return [
            self::$number,
            $karyawan->nik,
            $karyawan->users->nama,
            $karyawan->users->username,
            $karyawan->users->status_aktifs->label,
            $karyawan->users->tgl_dinonaktifkan,
            implode(', ', $roles),
            $karyawan->email ?? 'N/A',
            $karyawan->no_rm ?? 'N/A',
            $karyawan->no_manulife ?? 'N/A',
            $formatDate($karyawan->tgl_masuk),
            optional($karyawan->unit_kerjas)->nama_unit,
            optional($karyawan->jabatans)->nama_jabatan,
            optional($karyawan->kompetensis)->nama_kompetensi,
            $karyawan->nik_ktp ?? 'N/A',
            optional($karyawan->status_karyawans)->label,
            $karyawan->tempat_lahir ?? 'N/A',
            $formatDate($karyawan->tgl_lahir),
            optional($karyawan->kelompok_gajis)->nama_kelompok,
            $karyawan->no_rekening ?? 'N/A',
            $karyawan->tunjangan_jabatan ?? 'N/A',
            $karyawan->tunjangan_fungsional ?? 'N/A',
            $karyawan->tunjangan_khusus ?? 'N/A',
            $karyawan->tunjangan_lainnya ?? 'N/A',
            $karyawan->uang_lembur ?? 'N/A',
            $karyawan->uang_makan ?? 'N/A',
            optional($karyawan->ptkps)->kode_ptkp,
            $formatDate($karyawan->tgl_keluar),
            $karyawan->no_kk ?? 'N/A',
            $karyawan->alamat ?? 'N/A',
            $karyawan->gelar_depan ?? 'N/A',
            $karyawan->gelar_belakang ?? 'N/A',
            $karyawan->no_hp ?? 'N/A',
            $karyawan->no_bpjsksh ?? 'N/A',
            $karyawan->no_bpjsktk ?? 'N/A',
            $formatDate($karyawan->tgl_diangkat),
            $masaKerja,
            $karyawan->npwp ?? 'N/A',
            $karyawan->jenis_kelamin ? 'Laki-laki' : 'Perempuan',
            optional($karyawan->kategori_agamas)->label,
            optional($karyawan->kategori_darahs)->label,
            $karyawan->tinggi_badan,
            $karyawan->berat_badan,
            $karyawan->bmi_value,
            $karyawan->bmi_ket,
            $karyawan->riwayat_penyakit ?? 'N/A',
            $karyawan->no_ijazah ?? 'N/A',
            $karyawan->tahun_lulus,
            $karyawan->no_str ?? 'N/A',
            $formatDate($karyawan->created_str),
            Carbon::parse($karyawan->masa_berlaku_str)->format('d-m-Y'),
            $karyawan->no_sip ?? 'N/A',
            $formatDate($karyawan->created_sip),
            Carbon::parse($karyawan->masa_berlaku_sip)->format('d-m-Y'),
            $formatDate($karyawan->tgl_berakhir_pks),
            $this->formatDuration($karyawan->masa_diklat),
            Carbon::parse($karyawan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($karyawan->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d jam %d menit", $hours, $minutes);
    }

    public function calculateMasaKerja($tgl_masuk, $tgl_keluar = null)
    {
        $start = Carbon::parse($tgl_masuk);
        $end = $tgl_keluar ? Carbon::parse($tgl_keluar) : Carbon::now('Asia/Jakarta');

        $difference = $start->diff($end);

        return sprintf('%d Tahun %d Bulan %d Hari', $difference->y, $difference->m, $difference->d);
    }
}