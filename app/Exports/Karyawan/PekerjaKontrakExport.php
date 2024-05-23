<?php

namespace App\Exports\Karyawan;

use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PekerjaKontrakExport implements FromCollection, WithHeadings, WithMapping
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
        return DataKaryawan::where('status_karyawan', 'Kontrak')->get();
    }

    public function headings(): array
    {
        return [
            'user_id',
            'unit_kerja_id',
            'tgl_masuk',
            'tgl_keluar',
            'status_karyawan',
            'created_at',
            'updated_at'
        ];
    }

    public function map($kontrak): array
    {
        return [
            $kontrak->users->nama,
            $kontrak->unit_kerjas->nama_unit,
            $kontrak->tgl_masuk,
            $kontrak->tgl_keluar ?? 'Data tidak tersedia',
            $kontrak->status_karyawan,
            $kontrak->created_at,
            $kontrak->updated_at,
        ];
    }
}
