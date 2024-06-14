<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\Lembur;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class LemburJadwalExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Lembur::with(['users', 'shifts'])->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'shift',
            'tgl_pengajuan',
            'kompensasi',
            'tipe',
            'durasi',
            'catatan',
            'status_lembur',
            'created_at',
            'updated_at',
        ];
    }

    public function map($lembur): array
    {
        $currentDate = now();
        if ($currentDate->lessThan($lembur->tgl_pengajuan)) {
            $status_lembur = 'Dijadwalkan';
        } elseif ($currentDate->isSameDay($lembur->tgl_pengajuan)) {
            $status_lembur = 'Berlangsung';
        } else {
            $status_lembur = 'Selesai';
        }

        return [
            $lembur->users->nama,
            $lembur->shifts->nama,
            Carbon::parse($lembur->tgl_pengajuan)->format('d-m-Y'),
            $lembur->kompensasi,
            $lembur->tipe,
            $lembur->durasi,
            $lembur->catatan,
            $status_lembur,
            Carbon::parse($lembur->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($lembur->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
