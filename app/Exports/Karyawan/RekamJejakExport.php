<?php

namespace App\Exports\Karyawan;

use Carbon\Carbon;
use App\Models\TrackRecord;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekamJejakExport implements FromCollection, WithHeadings, WithMapping
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
            return TrackRecord::whereIn('id', $this->ids)->get();
        }
        return TrackRecord::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'tgl_masuk',
            'tgl_keluar',
            'promosi',
            'mutasi',
            'penghargaan',
            'created_at',
            'updated_at',
        ];
    }

    public function map($rekam_jejak): array
    {
        return [
            $rekam_jejak->users->nama,
            $rekam_jejak->tgl_masuk,
            $rekam_jejak->tgl_keluar ?? 'Data tidak tersedia',
            $rekam_jejak->promosi ?? 'Data tidak tersedia',
            $rekam_jejak->mutasi ?? 'Data tidak tersedia',
            $rekam_jejak->penghargaan ?? 'Data tidak tersedia',
            Carbon::parse($rekam_jejak->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($rekam_jejak->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
