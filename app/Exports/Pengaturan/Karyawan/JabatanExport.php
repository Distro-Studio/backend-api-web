<?php

namespace App\Exports\Pengaturan\Karyawan;

use Carbon\Carbon;
use App\Models\Jabatan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class JabatanExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Jabatan::all();
    }

    public function headings(): array
    {
        return [
            'nama_jabatan',
            'is_struktural',
            'tunjangan',
            'created_at',
            'updated_at'
        ];
    }

    public function map($jabatan): array
    {
        return [
            $jabatan->nama_jabatan,
            $jabatan->is_struktural ? 'Ya' : 'Tidak',
            $jabatan->tunjangan,
            Carbon::parse($jabatan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($jabatan->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
