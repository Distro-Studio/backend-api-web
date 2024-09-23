<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateTagihanPotonganImport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'nama',
            'kategori_tagihan',
            'besaran',
            'bulan_mulai',
            'bulan_selesai'
        ];
    }
}
