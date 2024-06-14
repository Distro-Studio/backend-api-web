<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\Cuti;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CutiJadwalExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Cuti::with(['users', 'tipe_cutis'])->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'tipe_cuti',
            'tgl_from',
            'tgl_to',
            'catatan',
            'durasi',
            'status_cuti',
            'created_at',
            'updated_at',
        ];
    }

    public function map($cuti): array
    {
        $currentDate = now();
        if ($currentDate->lessThan($cuti->tgl_from)) {
            $status_cuti = 'Dijadwalkan';
        } elseif ($currentDate->between($cuti->tgl_from, $cuti->tgl_to)) {
            $status_cuti = 'Berlangsung';
        } else {
            $status_cuti = 'Selesai';
        }

        return [
            $cuti->users->nama,
            $cuti->tipe_cutis->nama,
            Carbon::parse($cuti->tgl_from)->format('d-m-Y'),
            Carbon::parse($cuti->tgl_to)->format('d-m-Y'),
            $cuti->catatan ?? 'N/A',
            $cuti->durasi . ' Hari',
            $status_cuti,
            Carbon::parse($cuti->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($cuti->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
