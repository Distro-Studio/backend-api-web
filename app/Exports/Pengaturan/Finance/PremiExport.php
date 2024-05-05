<?php

namespace App\Exports\Pengaturan\Finance;

use App\Models\Premi;
use Maatwebsite\Excel\Concerns\FromCollection;

class PremiExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Premi::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Premi',
            'Jenis Premi',
            'Rate Premi',
        ];
    }

    public function map($premi): array
    {
        return [
            $premi->id,
            $premi->nama_premi,
            $premi->jenis_premi,
            $premi->besaran_premi,
        ];
    }
}
