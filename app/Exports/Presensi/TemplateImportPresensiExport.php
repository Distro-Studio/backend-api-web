<?php

namespace App\Exports\Presensi;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateImportPresensiExport implements FromArray, WithHeadings
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
            'nik_ktp',
            'jam_masuk',
            'jam_keluar'
        ];
    }
}
