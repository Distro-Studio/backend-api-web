<?php

namespace App\Exports\Pengaturan\Finance;

use App\Models\Premi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PremiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    public function collection()
    {
        return Premi::all();
    }

    public function headings(): array
    {
        return [
            'nama_premi',
            'jenis_premi',
            'besaran_premi',
            'created_at',
            'updated_at',
        ];
    }

    public function map($premi): array
    {
        return [
            $premi->nama_premi,
            $premi->jenis_premi,
            $premi->besaran_premi,
            $premi->created_at,
            $premi->updated_at,
        ];
    }
}
