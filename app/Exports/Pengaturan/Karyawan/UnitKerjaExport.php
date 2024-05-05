<?php

namespace App\Exports\Pengaturan\Karyawan;

use App\Models\UnitKerja;
use Maatwebsite\Excel\Concerns\FromCollection;

class UnitKerjaExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UnitKerja::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Unit',
            'Jenis Karyawan',
        ];
    }

    public function map($unit_kerjas): array
    {
        return [
            $unit_kerjas->id,
            $unit_kerjas->nama_unit,

            // TODO: Buat boolean data
            // $unit_kerjas->jenis_karyawan,
        ];
    }
}
