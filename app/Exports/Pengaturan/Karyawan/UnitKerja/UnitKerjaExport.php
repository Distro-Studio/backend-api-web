<?php

namespace App\Exports\Pengaturan\Karyawan\UnitKerja;

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
            'Kode Unit Kerja',
            'Nama Kompetensi',
        ];
    }

    public function map($jabatan): array
    {
        return [
            $jabatan->id,
            $jabatan->nama_unit,
        ];
    }
}
