<?php

namespace App\Exports\Pengaturan\Finance;

use App\Models\Ter;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TER21Export implements FromCollection, WithHeadings, WithMapping
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
            return Ter::whereIn('id', $this->ids)->get();
        }
        return Ter::all();
    }

    public function headings(): array
    {
        return [
            'kategori_ter_id',
            'ptkp_id',
            'from_ter',
            'to_ter',
            'percentage_ter',
            'created_at',
            'updated_at'
        ];
    }

    public function map($ter21): array
    {
        return [
            $ter21->kategori_ters->nama_kategori_ter,
            $ter21->ptkps->kode_ptkp,
            $ter21->from_ter,
            $ter21->to_ter,
            $ter21->percentage_ter,
            $ter21->created_at,
            $ter21->updated_at,
        ];
    }
}
