<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use App\Exports\Sheet\RekapGajiUnitPenambahSheet;
use App\Exports\Sheet\RekapGajiUnitPengurangSheet;
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
                $unitKerjas = UnitKerja::whereHas('data_karyawan.penggajians', function ($query) use ($month, $year) {
                        $query->whereMonth('tgl_penggajian', $month)
                            ->whereYear('tgl_penggajian', $year);
                    })
                    ->get();

                // Tambahkan sheet gabungan
                if ($unitKerjas->isNotEmpty()) {
                    $sheets[] = new RekapGajiUnitSheet('Rekap Gaji Unit Kerja', $unitKerjas, $month, $year);
                }

                // Add Shift sheet
                if ($unitKerjas->isNotEmpty()) {
                    $sheets[] = new RekapGajiUnitPengurangSheet('Rekap Gaji Unit Kerja Pengurang', $unitKerjas, $month, $year);
                }

                // Add Non-Shift sheet
                if ($unitKerjas->isNotEmpty()) {
                    $sheets[] = new RekapGajiUnitPenambahSheet('Rekap Gaji Unit Kerja Penerimaan', $unitKerjas, $month, $year);
                }
            }
        }

        if (empty($sheets)) {
            $sheets[] = new RekapGajiUnitSheet('Sheet Kosong', collect([]), null, null);
        }

        return $sheets;
    }
}
