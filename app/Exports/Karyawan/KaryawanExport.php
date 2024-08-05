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

    private static $number = 0;

    public function collection()
    {
        return DataKaryawan::with([
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
        ])
            ->where('email', '!=', 'super_admin@admin.rski')
            ->get();
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

        return [
            self::$number,
            $karyawan->users->nama,
            implode(', ', $roles),
            $karyawan->email,
            $karyawan->no_rm,
            $karyawan->no_manulife,
            RandomHelper::convertToDateString($karyawan->tgl_masuk),
            optional($karyawan->unit_kerjas)->nama_unit,
            optional($karyawan->jabatans)->nama_jabatan,
            optional($karyawan->kompetensis)->nama_kompetensi,
            $karyawan->nik_ktp,
            optional($karyawan->status_karyawans)->label,
            $karyawan->tempat_lahir,
            RandomHelper::convertToDateString($karyawan->tgl_lahir),
            optional($karyawan->kelompok_gajis)->nama_kelompok,
            $karyawan->no_rekening,
            $karyawan->tunjangan_jabatan ?? 'N/A',
            $karyawan->tunjangan_fungsional ?? 'N/A',
            $karyawan->tunjangan_khusus ?? 'N/A',
            $karyawan->tunjangan_lainnya ?? 'N/A',
            $karyawan->uang_lembur ?? 'N/A',
            $karyawan->uang_makan ?? 'N/A',
            optional($karyawan->ptkps)->kode_ptkp,
            RandomHelper::convertToDateString($karyawan->tgl_keluar),
            $karyawan->no_kk,
            $karyawan->alamat,
            $karyawan->gelar_depan,
            $karyawan->no_hp,
            $karyawan->no_bpjsksh,
            $karyawan->no_bpjsktk,
            RandomHelper::convertToDateString($karyawan->tgl_diangkat),
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
            RandomHelper::convertToDateString($karyawan->tgl_berakhir_pks),
            $karyawan->masa_diklat,
            Carbon::parse($karyawan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($karyawan->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
