<?php

namespace App\Exports\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PekerjaKontrakExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return DataKaryawan::where('status_karyawan', 'Kontrak')->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'unit_kerja',
            'tgl_masuk',
            'tgl_keluar',
            'status_kontrak',
            'created_at',
            'updated_at'
        ];
    }

    public function map($kontrak): array
    {
        $statusAktif = ($kontrak->tgl_masuk && !$kontrak->tgl_keluar) ? 'Aktif' : 'Tidak Aktif'; // 1 = Aktif, 0 = Tidak Aktif
        return [
            $kontrak->users->nama,
            $kontrak->unit_kerjas->nama_unit,
            $kontrak->tgl_masuk,
            $kontrak->tgl_keluar ?? 'N/A',
            $statusAktif,
            Carbon::parse($kontrak->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($kontrak->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
