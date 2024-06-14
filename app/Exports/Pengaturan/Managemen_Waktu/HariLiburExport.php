<?php

namespace App\Exports\Pengaturan\Managemen_Waktu;

use Carbon\Carbon;
use App\Models\HariLibur;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class HariLiburExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return HariLibur::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'tanggal',
            'created_at',
            'updated_at',
        ];
    }

    public function map($hari_libur): array
    {
        return [
            $hari_libur->nama,
            $hari_libur->tanggal,
            Carbon::parse($hari_libur->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($hari_libur->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
