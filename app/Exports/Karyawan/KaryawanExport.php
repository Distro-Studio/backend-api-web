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
    protected $ids;

    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        if (!empty($this->ids)) {
            return DataKaryawan::whereIn('id', $this->ids)->get();
        }
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
            $karyawan->nik ?? 'Data tidak tersedia',
            $karyawan->no_rm,
            $karyawan->nik_ktp ?? 'Data tidak tersedia',
            $karyawan->unit_kerjas->nama_unit,
            $karyawan->status_karyawan,
            $karyawan->tempat_lahir ?? 'Data tidak tersedia',
            $karyawan->tgl_lahir ?? 'Data tidak tersedia',
            Carbon::parse($karyawan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($karyawan->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
