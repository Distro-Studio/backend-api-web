<?php

namespace App\Exports\Pengaturan\Managemen_Waktu;

use Carbon\Carbon;
use App\Models\Shift;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ShiftExport implements FromCollection, WithHeadings, WithMapping
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
            return Shift::whereIn('id', $this->ids)->get();
        }
        return Shift::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'jam_from',
            'jam_to',
            'created_at',
            'updated_at',
        ];
    }

    public function map($shift): array
    {
        return [
            $shift->nama,
            $shift->jam_from,
            $shift->jam_to,
            Carbon::parse($shift->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($shift->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
