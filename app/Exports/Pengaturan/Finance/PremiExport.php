<?php

namespace App\Exports\Pengaturan\Finance;

use Carbon\Carbon;
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
            'nama',
            'jenis',
            'besaran',
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
            Carbon::parse($premi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($premi->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
