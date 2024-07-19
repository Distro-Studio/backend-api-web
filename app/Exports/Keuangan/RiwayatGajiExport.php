<?php

namespace App\Exports\Keuangan;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Keuangan\LaporanPenggajian\Sheet\RiwayatGajiSheet;

class RiwayatGajiExport implements FromCollection, WithMultipleSheets
{
    protected $months;
    protected $year;

    public function __construct($months, $year)
    {
        $this->months = $months;
        $this->year = $year;
    }

    public function collection()
    {
        return collect([]);
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->months as $month) {
            $sheets[] = new RiwayatGajiSheet($month, $this->year);
        }

        return $sheets;
    }
}
