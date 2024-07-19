<?php

namespace App\Exports\Pengaturan\Finance;

use Carbon\Carbon;
use App\Models\Premi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PremiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    public function collection()
    {
        return Premi::whereNull('deleted_at')->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'sumber_potongan',
            'jenis',
            'besaran',
            'minimal_rate',
            'maksimal_rate',
            'created_at',
            'updated_at',
        ];
    }

    public function map($premi): array
    {
        $besaranPremi = $premi->jenis_premi ? 'Rp' . $premi->besaran_premi : $premi->besaran_premi . '%';
        return [
            $premi->nama_premi,
            $premi->sumber_potongan,
            $premi->jenis_premi ? 'Nominal' : 'Persentase',
            $besaranPremi,
            $premi->minimal_rate ?? 'N/A',
            $premi->maksimal_rate ?? 'N/A',
            Carbon::parse($premi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($premi->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
