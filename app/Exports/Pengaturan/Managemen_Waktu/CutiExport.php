<?php

namespace App\Exports\Pengaturan\Managemen_Waktu;

use App\Models\TipeCuti;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CutiExport implements FromCollection, WithHeadings, WithMapping
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
            return TipeCuti::whereIn('id', $this->ids)->get();
        }
        return TipeCuti::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'durasi',
            // 'waktu',
            'created_at',
            'updated_at',
        ];
    }

    public function map($cuti): array
    {
        return [
            $cuti->nama,
            $cuti->durasi,

            // ? kolom waktu masih dibiarkan kosong
            // $cuti->waktu,
            $cuti->created_at,
            $cuti->updated_at,
        ];
    }
}
