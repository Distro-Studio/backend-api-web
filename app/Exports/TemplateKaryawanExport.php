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
            'no',
            'nama',
            'email',
            'role',
            'no_rm',
            'no_manulife',
            'tgl_masuk',
            'unit_kerja',
            'jabatan',
            'kompetensi',
            'status_karyawan',
            'kelompok_gaji',
            'no_rekening',
            'tunjangan_jabatan',
            'tunjangan_fungsional',
            'tunjangan_khusus',
            'tunjangan_lainnya',
            'uang_makan',
            'uang_lembur',
            'kode_ptkp',
        ];
    }
}
