<?php

namespace App\Exports\Perusahaan;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ListDiklatSheetExport implements FromCollection, WithHeadings, WithTitle
{
    protected $diklats;

    public function __construct($diklats)
    {
        $this->diklats = $diklats;
    }

    public function collection()
    {
        return collect($this->diklats)->map(function ($diklat, $idx) {
            return [
                'no' => $idx + 1,
                'nama' => $diklat->nama,
                'kategori' => $diklat->kategori_diklats->label ?? '-',
                'status' => $diklat->status_diklats->label ?? '-',
                'deskripsi' => $diklat->deskripsi,
                'kuota' => $diklat->kuota,
                'tgl_mulai' => $diklat->tgl_mulai,
                'tgl_selesai' => $diklat->tgl_selesai,
                'jam_mulai' => $diklat->jam_mulai,
                'jam_selesai' => $diklat->jam_selesai,
                'durasi' => $diklat->durasi,
                'lokasi' => $diklat->lokasi,
                'created_at' => $diklat->created_at,
                'updated_at' => $diklat->updated_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Kategori',
            'Status',
            'Deskripsi',
            'Kuota',
            'Tgl Mulai',
            'Tgl Selesai',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi',
            'Lokasi',
            'Created At',
            'Updated At',
        ];
    }

    public function title(): string
    {
        return 'List Diklat';
    }
}
