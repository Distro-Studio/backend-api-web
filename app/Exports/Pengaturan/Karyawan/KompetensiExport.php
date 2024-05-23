<?php

namespace App\Exports\Pengaturan\Karyawan;

use App\Models\Kompetensi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class KompetensiExport implements FromCollection, WithHeadings, WithMapping
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
            return Kompetensi::whereIn('id', $this->ids)->get();
        }
        return Kompetensi::all();
    }

    public function headings(): array
    {
        return [
            'nama_kompetensi',
            'jenis_kompetensi',
            'total_tunjangan',
            'created_at',
            'updated_at',
        ];
    }

    public function map($kompetensi): array
    {
        return [
            $kompetensi->nama_kompetensi,
            $kompetensi->jenis_kompetensi,
            $kompetensi->total_tunjangan,
            $kompetensi->created_at,
            $kompetensi->updated_at,
        ];
    }
}
