<?php

namespace App\Exports\Perusahaan;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PesertaDiklatSheetExport implements FromCollection, WithHeadings, WithTitle
{
    protected $diklat;
    protected $peserta;

    public function __construct($diklat, $peserta)
    {
        $this->diklat = $diklat;
        $this->peserta = $peserta;
    }

    public function collection()
    {
        return collect($this->peserta)->map(function ($peserta, $idx) {
            return [
                'no' => $idx + 1,
                'nama' => $peserta->users->nama ?? '-',
                'nip' => $peserta->users->nip ?? '-',
                'unit_kerja' => $peserta->users->data_karyawans->unit_kerjas->nama ?? '-',
                'jabatan' => $peserta->users->data_karyawans->jabatans->nama ?? '-',
                'status_karyawan' => $peserta->users->data_karyawans->status_karyawans->nama ?? '-',
                'jenis_kelamin' => $peserta->users->data_karyawans->jenis_kelamin ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIP',
            'Unit Kerja',
            'Jabatan',
            'Status Karyawan',
            'Jenis Kelamin',
        ];
    }

    public function title(): string
    {
        return $this->diklat->nama;
    }
}
