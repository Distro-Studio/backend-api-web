<?php

namespace App\Exports\Pengaturan\Karyawan;

use App\Models\Jabatan;
use Maatwebsite\Excel\Concerns\FromCollection;

class JabatanExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Jabatan::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Jabatan',
            // 'Is Struktural', // Adjust column names as needed
            'Tunjangan',
        ];
    }

    public function map($jabatan): array
    {
        return [
            $jabatan->id,
            $jabatan->nama_jabatan,
            // $jabatan->is_struktural ? 'Ya' : 'Tidak', // Convert boolean to string
            $jabatan->tunjangan,
        ];
    }
}
