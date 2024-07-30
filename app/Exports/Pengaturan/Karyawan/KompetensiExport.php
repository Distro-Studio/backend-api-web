<?php

namespace App\Exports\Pengaturan\Karyawan;

use Carbon\Carbon;
use App\Models\Kompetensi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class KompetensiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Kompetensi::whereNull('deleted_at')->get();
    }

    public function headings(): array
    {
        return [
            'nama_kompetensi',
            'jenis_kompetensi',
            'total_tunjangan',
            'total_bor',
            'created_at',
            'updated_at',
        ];
    }

    public function map($kompetensi): array
    {
        return [
            $kompetensi->nama_kompetensi,
            $kompetensi->jenis_kompetensi ? 'Medis' : 'Non Medis',
            $kompetensi->total_tunjangan,
            $kompetensi->total_bor,
            Carbon::parse($kompetensi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($kompetensi->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
