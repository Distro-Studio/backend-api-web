<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use App\Models\UnitKerja;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Sheet\RekapGajiLaporanBankSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanGajiBankExport implements FromCollection, WithMultipleSheets
{
    use Exportable;

    protected $months;
    protected $years;

    public function __construct(array $months, array $years)
    {
        $this->months = $months;
        $this->years = $years;
    }

    public function collection()
    {
        return collect([]);
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->years as $year) {
            foreach ($this->months as $month) {
                $sheets[] = new RekapGajiLaporanBankSheet($month, $year);
            }
        }

        return $sheets;
    }
}
