<?php

namespace App\Exports\Pengaturan\Karyawan;

use Carbon\Carbon;
use App\Models\KelompokGaji;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class KelompokGajiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return KelompokGaji::whereNull('deleted_at')->get();
    }

    public function headings(): array
    {
        return [
            'nama_kelompok',
            'besaran_gaji',
            'created_at',
            'updated_at',
        ];
    }

    public function map($kelompok_gaji): array
    {
        return [
            $kelompok_gaji->nama_kelompok,
            $kelompok_gaji->besaran_gaji,
            Carbon::parse($kelompok_gaji->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($kelompok_gaji->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
