<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateJadwalExport implements FromArray, WithHeadings
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
            'nomor_induk_karyawan',
            'tanggal_mulai',
            'tanggal_selesai',
            'shift'
        ];
    }
}
