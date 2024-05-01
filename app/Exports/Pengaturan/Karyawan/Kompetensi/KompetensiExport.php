<?php

namespace App\Exports\Pengaturan\Karyawan\Kompetensi;

use App\Models\Kompetensi;
use Maatwebsite\Excel\Concerns\FromCollection;

class KompetensiExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Kompetensi::all();
    }

    public function headings(): array
    {
        return [
            'Kode Kompetensi',
            'Nama Kompetensi',
            'Jenis Kompetensi',
            'Tunjangan',
        ];
    }

    public function map($jabatan): array
    {
        return [
            $jabatan->id,
            $jabatan->nama_kompetensi,
            $jabatan->jenis_kompetensi,
            $jabatan->total_tunjangan,
        ];
    }
}
