<?php

namespace App\Exports\Pengaturan\Finance;

use Carbon\Carbon;
use App\Models\Ter;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TER21Export implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Ter::all();
    }

    public function headings(): array
    {
        return [
            'nama_kategori_ter',
            'kode_ptkp',
            'from_ter',
            'to_ter',
            'percentage_ter',
            'created_at',
            'updated_at'
        ];
    }

    public function map($ter21): array
    {
        return [
            $ter21->kategori_ters->nama_kategori_ter,
            $ter21->ptkps->kode_ptkp,
            $ter21->from_ter,
            $ter21->to_ter,
            $ter21->percentage_ter,
            Carbon::parse($ter21->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($ter21->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
