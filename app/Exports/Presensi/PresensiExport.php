<?php

namespace App\Exports\Presensi;

use App\Exports\Sheet\PresensiSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PresensiExport implements WithMultipleSheets
{
    use Exportable;

    private $months;
    private $year;

    public function __construct($months, $year)
    {
        $this->months = $months;
        $this->year = $year;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Menambahkan sheet untuk setiap kategori presensi
        $categories = ['Terlambat', 'Tepat Waktu', 'Cuti', 'Absen'];
        foreach ($categories as $category) {
            $sheets[] = new PresensiSheet($category, 'Laporan ' . str_replace(' ', '', $category), $this->months, $this->year);
        }

        return $sheets;
    }
}
