<?php

namespace App\Exports\Pengaturan\Karyawan;

use App\Models\KelompokGaji;
use Maatwebsite\Excel\Concerns\FromCollection;

class KelompokGajiExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return KelompokGaji::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Gaji',
            'Besaran Gaji',
        ];
    }

    public function map($kelompok_gajis): array
    {
        return [
            $kelompok_gajis->id,
            $kelompok_gajis->nama_kelompok,
            $kelompok_gajis->besaran_gaji,
        ];
    }
}
