<?php

namespace App\Exports\Presensi;

use App\Exports\Sheet\PresensiSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PresensiExport implements WithMultipleSheets
{
    use Exportable;

    private $startDate;
    private $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Menambahkan sheet untuk setiap kategori presensi
        $categories = ['Terlambat', 'Tepat Waktu', 'Alpha'];
        foreach ($categories as $category) {
            $sheets[] = new PresensiSheet($category, 'Laporan ' . str_replace(' ', '', $category), $this->startDate, $this->endDate);
        }

        return $sheets;
    }
}
