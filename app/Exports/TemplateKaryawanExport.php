<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateKaryawanExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            // Tambahkan row kosong
        ];
    }

    public function headings(): array
    {
        return [
            'nama',
            'email',
            'role',
            'no_rm',
            'no_manulife',
            'tgl_masuk',
            'tgl_berakhir_pks',
            'nik',
            'unit_kerja',
            'jabatan',
            'kompetensi',
            'status_karyawan',
            'kelompok_gaji',
            'no_rekening',
            'tunjangan_fungsional',
            'tunjangan_khusus',
            'tunjangan_lainnya',
            'uang_makan',
            'uang_lembur',
            'kode_ptkp',

            // tambahan
            'gelar_depan',
            'gelar_belakang',
            'tempat_lahir',
            'tgl_lahir',
            'alamat',
            'no_hp',
            'nik_ktp',
            'no_kk',
            'npwp',
            'jenis_kelamin',
            'agama',
            'darah',
            'tinggi_badan',
            'berat_badan',
            'pendidikan_terakhir',
            'asal_sekolah',
            'tahun_lulus',
            'tgl_diangkat'
        ];
    }
}
