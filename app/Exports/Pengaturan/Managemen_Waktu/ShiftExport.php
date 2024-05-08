<?php

namespace App\Exports\Pengaturan\Managemen_Waktu;

use App\Models\Shift;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ShiftExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    public function collection()
    {
        return Shift::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'jam_from',
            'jam_to',
            'created_at',
            'updated_at',
        ];
    }

    public function map($shift): array
    {
        return [
            $shift->nama,
            $shift->jam_from,
            $shift->jam_to,
            $shift->created_at,
            $shift->updated_at,
        ];
    }
}
