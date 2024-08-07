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
        ])->where('email', '!=', 'super_admin@admin.rski');

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
            if (is_array($masaKerja)) {
                $karyawan->where(function ($query) use ($masaKerja) {
                    foreach ($masaKerja as $masa) {
                        $bulan = $masa * 12;
                        $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
                    }
                });
            } else {
                $bulan = $masaKerja * 12;
                $karyawan->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
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
                foreach ($tglMasuk as &$tgl) {
                    $tgl = RandomHelper::convertToDateString($tgl);
                }
                $karyawan->whereIn('tgl_masuk', $tglMasuk);
            } else {
                $tglMasuk = RandomHelper::convertToDateString($tglMasuk);
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

        if (isset($this->filters['pendidikan_terakhir'])) {
            $namaPendidikan = $this->filters['pendidikan_terakhir'];
            $karyawan->whereHas('pendidikan_terakhir', function ($query) use ($namaPendidikan) {
                if (is_array($namaPendidikan)) {
                    $query->whereIn('id', $namaPendidikan);
                } else {
                    $query->where('id', '=', $namaPendidikan);
                }
            });
        }

        return $karyawan->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
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
            'no_ijazah',
            'tahun_lulus',
            'no_str',
            'masa_berlaku_str',
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

        $convertTgl_Masuk = RandomHelper::convertToDateString($karyawan->tgl_masuk);
        $convertTgl_Lahir = RandomHelper::convertToDateString($karyawan->tgl_lahir);
        $convertTgl_Keluar = RandomHelper::convertToDateString($karyawan->tgl_keluar);
        $convertTgl_Diangkat = RandomHelper::convertToDateString($karyawan->tgl_diangkat);
        $convertTgl_Berakhir_PKS = RandomHelper::convertToDateString($karyawan->tgl_berakhir_pks);
        $tgl_masuk = Carbon::parse($convertTgl_Masuk)->format('d-m-Y');
        $tgl_lahir = Carbon::parse($convertTgl_Lahir)->format('d-m-Y');
        $tgl_keluar = Carbon::parse($convertTgl_Keluar)->format('d-m-Y');
        $tgl_diangkat = Carbon::parse($convertTgl_Diangkat)->format('d-m-Y');
        $tgl_berakhir_pks = Carbon::parse($convertTgl_Berakhir_PKS)->format('d-m-Y');

        return [
            self::$number,
            $karyawan->users->nama,
            implode(', ', $roles),
            $karyawan->email,
            $karyawan->no_rm,
            $karyawan->no_manulife,
            $tgl_masuk,
            optional($karyawan->unit_kerjas)->nama_unit,
            optional($karyawan->jabatans)->nama_jabatan,
            optional($karyawan->kompetensis)->nama_kompetensi,
            $karyawan->nik_ktp,
            optional($karyawan->status_karyawans)->label,
            $karyawan->tempat_lahir,
            $tgl_lahir,
            optional($karyawan->kelompok_gajis)->nama_kelompok,
            $karyawan->no_rekening,
            $karyawan->tunjangan_jabatan ?? 'N/A',
            $karyawan->tunjangan_fungsional ?? 'N/A',
            $karyawan->tunjangan_khusus ?? 'N/A',
            $karyawan->tunjangan_lainnya ?? 'N/A',
            $karyawan->uang_lembur ?? 'N/A',
            $karyawan->uang_makan ?? 'N/A',
            optional($karyawan->ptkps)->kode_ptkp,
            $tgl_keluar,
            $karyawan->no_kk,
            $karyawan->alamat,
            $karyawan->gelar_depan,
            $karyawan->no_hp,
            $karyawan->no_bpjsksh,
            $karyawan->no_bpjsktk,
            $tgl_diangkat,
            $karyawan->masa_kerja,
            $karyawan->npwp,
            $karyawan->jenis_kelamin ? 'Laki-laki' : 'Perempuan',
            optional($karyawan->kategori_agamas)->label,
            optional($karyawan->kategori_darahs)->label,
            $karyawan->tinggi_badan,
            $karyawan->berat_badan,
            $karyawan->no_ijazah,
            $karyawan->tahun_lulus,
            $karyawan->no_str,
            Carbon::parse($karyawan->masa_berlaku_str)->format('d-m-Y'),
            $tgl_berakhir_pks,
            $karyawan->masa_diklat,
            Carbon::parse($karyawan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($karyawan->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
