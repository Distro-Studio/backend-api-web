<?php

namespace App\Exports\Pengaturan\Karyawan;

use Carbon\Carbon;
use App\Models\Pertanyaan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PertanyaanExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Pertanyaan::with('jabatans')->whereNull('deleted_at')->get();
    }

    public function headings(): array
    {
        return [
            'pertanyaan',
            'jabatan',
            'created_at',
            'updated_at',
        ];
    }

    public function map($pertanyaan): array
    {
        return [
            $pertanyaan->pertanyaan,
            $pertanyaan->jabatans ? $pertanyaan->jabatans->nama_jabatan : 'N/A',
            Carbon::parse($pertanyaan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($pertanyaan->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
