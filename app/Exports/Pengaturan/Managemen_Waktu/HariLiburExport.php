<?php

namespace App\Exports\Pengaturan\Managemen_Waktu;

use App\Models\HariLibur;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class HariLiburExport implements FromCollection, WithHeadings, WithMapping
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
            return HariLibur::whereIn('id', $this->ids)->get();
        }
        return HariLibur::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'tanggal',
            'created_at',
            'updated_at',
        ];
    }

    public function map($hari_libur): array
    {
        return [
            $hari_libur->nama,
            $hari_libur->tanggal,
            $hari_libur->created_at,
            $hari_libur->updated_at,
        ];
    }
}
