<?php

namespace App\Exports\Pengaturan\Finance;

use App\Models\Ter;
use Maatwebsite\Excel\Concerns\FromCollection;

class TER21Export implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // return Ter::all();
        return Ter::with(['kategori_ters', 'ptkps'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kategori TER',
            'Jenis PTKP',
            'Penghasilan Bruo Batas Awal Penghasilan',
            'Penghasilan Bruo Batas Akhir Penghasilan',
            'Persentase TER',
        ];
    }

    public function map($ter21): array
    {
        return [
            $ter21->id,
            $ter21->kategori_ters->nama_kategori_ter,
            $ter21->ptkps->kode_ptkp,
            $ter21->from_ter,
            $ter21->to_ter,
            $ter21->percentage_ter,
        ];
    }
}
