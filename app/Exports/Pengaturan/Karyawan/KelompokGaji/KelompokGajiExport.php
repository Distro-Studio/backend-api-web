<?php

namespace App\Exports\Pengaturan\Karyawan\KelompokGaji;

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

    public function map($jabatan): array
    {
        return [
            $jabatan->id,
            $jabatan->nama_kelompok,
            $jabatan->besaran_gaji,
        ];
    }
}
