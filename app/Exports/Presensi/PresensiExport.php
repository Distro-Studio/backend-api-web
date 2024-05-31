<?php

namespace App\Exports\Presensi;

use Carbon\Carbon;
use App\Models\Presensi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PresensiExport implements FromCollection, WithHeadings, WithMapping, WithDrawings
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
            return Presensi::whereIn('id', $this->ids)->get();
        }
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
            'bukti_absensi',
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
            $presensi->foto,
            $presensi->absensi,
            $presensi->kategori,
            Carbon::parse($presensi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($presensi->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $rows = $this->collection();
        $rowNumber = 2; // Start from row 2 because row 1 is the heading

        foreach ($rows as $row) {
            $path = storage_path('app/public/bukti_absensi/' . $row->foto);

            if (file_exists($path)) {
                $drawing = new Drawing();
                $drawing->setPath($path);
                $drawing->setHeight(50); // Adjust the height as needed
                $drawings[] = $drawing;
            }
            $rowNumber++;
        }
        return $drawings;
    }
}
