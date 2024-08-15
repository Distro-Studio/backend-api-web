<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

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
            'no',
            'nama',
            'tanggal_mulai',
            'tanggal_selesai',
            'shift'
        ];
    }
}
