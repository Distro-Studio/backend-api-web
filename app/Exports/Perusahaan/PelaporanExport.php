<?php

namespace App\Exports\Perusahaan;

use Carbon\Carbon;
use App\Models\Pelaporan;
use App\Helpers\RandomHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PelaporanExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private static $number = 0;

    public function collection()
    {
        return Pelaporan::with(['user_pelapor', 'user_pelaku'])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'pelapor',
            'pelaku',
            'tgl_kejadian',
            'lokasi',
            'kronologi',
            'created_at',
            'updated_at',
        ];
    }

    public function map($pelaporan): array
    {
        self::$number++;
        $tgl_kejadian = Carbon::parse(RandomHelper::convertToDateString($pelaporan->tgl_kejadian))->format('d-m-Y');

        return [
            self::$number,
            $pelaporan->user_pelapor->nama,
            $pelaporan->user_pelaku->nama,
            $tgl_kejadian,
            $pelaporan->lokasi,
            $pelaporan->kronologi,
            Carbon::parse($pelaporan->created_at)->format('d-m-Y H:i:s')
        ];
    }
}
