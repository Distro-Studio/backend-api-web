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
    protected $ids;

    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }
    public function collection()
    {
        if (!empty($this->ids)) {
            return Premi::whereIn('id', $this->ids)->get();
        }
        return Premi::all();
    }

    public function headings(): array
    {
        return [
            'nama_premi',
            'jenis_premi',
            'besaran_premi',
            'created_at',
            'updated_at',
        ];
    }

    public function map($premi): array
    {
        return [
            $premi->nama_premi,
            $premi->jenis_premi,
            $premi->besaran_premi,
            Carbon::parse($premi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($premi->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
