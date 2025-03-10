<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use App\Models\UnitKerja;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheet\RekapGajiPenerimaanAllUnitsSheet;
use App\Exports\Keuangan\LaporanPenggajian\Sheet\RekapGajiPenerimaanSheet;
use App\Exports\Sheet\RekapGajiPenerimaanSheet as SheetRekapGajiPenerimaanSheet;

class RekapGajiPenerimaanExport implements FromCollection, WithMultipleSheets
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

        $sheets[] = new RekapGajiPenerimaanAllUnitsSheet($this->months, $this->years);

        foreach ($this->years as $year) {
            foreach ($this->months as $month) {
                // Get unit_kerjas that have users with payroll data for the given month and year
                $unitKerjas = UnitKerja::whereHas('data_karyawan.penggajians', function ($query) use ($month, $year) {
                    $query->whereMonth('tgl_penggajian', $month)
                        ->whereYear('tgl_penggajian', $year);
                })->get();

                foreach ($unitKerjas as $unitKerja) {
                    $sheets[] = new SheetRekapGajiPenerimaanSheet($unitKerja->id, $month, $year);
                }
            }
        }

        return $sheets;
    }
}
