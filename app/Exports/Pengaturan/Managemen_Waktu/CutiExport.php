<?php

namespace App\Exports\Pengaturan\Managemen_Waktu;

use Carbon\Carbon;
use App\Models\TipeCuti;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CutiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return TipeCuti::whereNull('deleted_at')->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'kuota',
            'is_need_requirement',
            'keterangan',
            'cuti_administratif',
            'created_at',
            'updated_at',
        ];
    }

    public function map($cuti): array
    {
        return [
            $cuti->nama,
            $cuti->kuota,
            $cuti->is_need_requirement ? 'Ya' : 'Tidak',
            $cuti->keterangan,
            $cuti->cuti_administratif ? 'Ya' : 'Tidak',
            Carbon::parse($cuti->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($cuti->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
