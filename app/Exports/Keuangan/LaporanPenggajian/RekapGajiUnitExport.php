<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use App\Models\UnitKerja;
use App\Exports\Sheet\RekapGajiUnitSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapGajiUnitExport implements FromCollection, WithMultipleSheets
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
                $shiftUnits = UnitKerja::where('jenis_karyawan', 1)
                    ->whereHas('data_karyawan.penggajians', function ($query) use ($month, $year) {
                        $query->whereMonth('tgl_penggajian', $month)
                            ->whereYear('tgl_penggajian', $year);
                    })
                    ->get();

                $nonShiftUnits = UnitKerja::where('jenis_karyawan', 0)
                    ->whereHas('data_karyawan.penggajians', function ($query) use ($month, $year) {
                        $query->whereMonth('tgl_penggajian', $month)
                            ->whereYear('tgl_penggajian', $year);
                    })
                    ->get();

                // Add Shift sheet
                if ($shiftUnits->isNotEmpty()) {
                    $sheets[] = new RekapGajiUnitSheet('Shift', $shiftUnits, $month, $year);
                }

                // Add Non-Shift sheet
                if ($nonShiftUnits->isNotEmpty()) {
                    $sheets[] = new RekapGajiUnitSheet('Non-Shift', $nonShiftUnits, $month, $year);
                }
            }
        }

        // if (empty($sheets)) {
        //     throw new \Exception('Data penggajian pada filter bulan dan tahun yang dipilih tidak ditemukan.');
        // }

        return $sheets;
    }
}
