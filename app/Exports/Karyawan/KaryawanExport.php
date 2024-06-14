<?php

namespace App\Exports\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class KaryawanExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return DataKaryawan::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'nik',
            'no_rm',
            'nik_ktp',
            'unit_kerja',
            'status_karyawan',
            'tempat_lahir',
            'tgl_lahir',
            'created_at',
            'updated_at',
        ];
    }

    public function map($karyawan): array
    {
        return [
            $karyawan->users->nama,
            $karyawan->nik ?? 'N/A',
            $karyawan->no_rm,
            $karyawan->nik_ktp ?? 'N/A',
            $karyawan->unit_kerjas->nama_unit,
            $karyawan->status_karyawan,
            $karyawan->tempat_lahir ?? 'N/A',
            $karyawan->tgl_lahir ?? 'N/A',
            Carbon::parse($karyawan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($karyawan->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
