<?php

namespace App\Exports\Pengaturan\Karyawan;

use App\Models\UnitKerja;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class UnitKerjaExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    public function collection()
    {
        return UnitKerja::all();
    }

    public function headings(): array
    {
        return [
            'nama_unit',
            'jenis_karyawan',
            'created_at',
            'updated_at',
        ];
    }

    public function map($unit_kerja): array
    {
        return [
            $unit_kerja->nama_unit,
            $unit_kerja->jenis_karyawan ? 'Shift' : 'Non-Shift',
            $unit_kerja->created_at,
            $unit_kerja->updated_at,
        ];
    }
}
