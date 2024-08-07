<?php

namespace App\Exports\Jadwal;

use App\Helpers\RandomHelper;
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
        return Cuti::with(['users', 'tipe_cutis', 'status_cutis'])->get();
    }

    public function headings(): array
    {
        return [
            'no',
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
        static $no = 1;

        $convertTgl_From = RandomHelper::convertToDateString($cuti->tgl_from);
        $convertTgl_To = RandomHelper::convertToDateString($cuti->tgl_to);
        $tgl_from = Carbon::parse($convertTgl_From)->format('d-m-Y');
        $tgl_to = Carbon::parse($convertTgl_To)->format('d-m-Y');

        return [
            $no++,
            $cuti->users->nama,
            $cuti->tipe_cutis->nama,
            $tgl_from,
            $tgl_to,
            $cuti->catatan ?? 'N/A',
            $cuti->durasi . ' Hari',
            $cuti->status_cutis->label,
            Carbon::parse($cuti->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($cuti->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
