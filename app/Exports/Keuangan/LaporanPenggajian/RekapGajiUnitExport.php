<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use App\Exports\Sheet\PenggajianKusyati\RekapGaji_1_Sheet;
use App\Exports\Sheet\PenggajianKusyati\RekapGaji_2_Sheet;
use App\Exports\Sheet\PenggajianKusyati\RekapGaji_3_Sheet;
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

                // if ($unitKerjas->isNotEmpty()) {
                //     $sheets[] = new RekapGajiUnitSheet('Rekap Gaji Unit Kerja', $unitKerjas, $month, $year);
                // }

                // if ($unitKerjas->isNotEmpty()) {
                //     $sheets[] = new RekapGajiUnitPengurangSheet('Pengurang', $unitKerjas, $month, $year);
                // }

                // if ($unitKerjas->isNotEmpty()) {
                //     $sheets[] = new RekapGajiUnitPenambahSheet('Penerimaan', $unitKerjas, $month, $year);
                // }

                // Rekapitulasi
                if ($unitKerjas->isNotEmpty()) {
                    $sheets[] = new RekapGaji_1_Sheet('Rekapitulasi Gaji', $unitKerjas, $month, $year);
                }

                // Laporan khusus Penambah
                if ($unitKerjas->isNotEmpty()) {
                    $sheets[] = new RekapGaji_2_Sheet('Laporan Gaji Penambah', $unitKerjas, $month, $year);
                }

                // Laporan khusus Pengurang
                if ($unitKerjas->isNotEmpty()) {
                    $sheets[] = new RekapGaji_3_Sheet('Laporan Gaji Pengurang', $unitKerjas, $month, $year);
                }

                if ($unitKerjas->isEmpty()) {
                    continue;
                }
            }
        }

        return $sheets;
    }
}
