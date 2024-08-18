<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class TemplateJadwalExport implements FromArray, WithHeadings, WithColumnFormatting
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
            'tanggal_mulai',
            'tanggal_selesai',
            'shift'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
            'B' => \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
            'C' => \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
            'D' => \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
        ];
    }
}
