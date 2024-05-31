<?php

namespace App\Exports\Pengaturan\Karyawan;

use Carbon\Carbon;
use App\Models\UnitKerja;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class UnitKerjaExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    protected $ids;

    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }
    public function collection()
    {
        if (!empty($this->ids)) {
            return UnitKerja::whereIn('id', $this->ids)->get();
        }
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
            Carbon::parse($unit_kerja->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($unit_kerja->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
