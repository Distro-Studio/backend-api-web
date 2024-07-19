<?php

namespace App\Exports\Presensi;

use Carbon\Carbon;
use App\Models\Presensi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PresensiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Presensi::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'shift',
            'unit_kerja',
            'jam_masuk',
            'jam_keluar',
            'durasi',
            'latitude',
            'longtitude',
            'absensi',
            'kategori',
            'created_at',
            'updated_at'
        ];
    }

    public function map($presensi): array
    {
        return [
            $presensi->users->nama,
            $presensi->jadwals->shifts->nama,
            $presensi->data_karyawans->unit_kerjas->nama_unit,
            Carbon::parse($presensi->jam_masuk)->format('d-m-Y H:i:s'),
            Carbon::parse($presensi->jam_keluar)->format('d-m-Y H:i:s'),
            $presensi->durasi,
            $presensi->lat,
            $presensi->long,
            $presensi->absensi,
            $presensi->kategori,
            Carbon::parse($presensi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($presensi->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
