<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use App\Models\DataKaryawan;
use App\Models\Penggajian;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class LaporanGajiBankExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    private $rowNumber = 1;

    public function collection()
    {
        return Penggajian::with('data_karyawans.users')->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nik',
            'nama',
            'no_rek',
            'jumlah',
        ];
    }

    public function map($dataKaryawan): array
    {
        return [
            $this->rowNumber++,
            $dataKaryawan->data_karyawans->nik,
            $dataKaryawan->data_karyawans->users->nama,
            $dataKaryawan->data_karyawans->no_rekening,
            $dataKaryawan->take_home_pay,
        ];
    }
}
