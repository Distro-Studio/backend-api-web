<?php

namespace App\Exports\Jadwal\CutiNew;

use App\Models\TipeCuti;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CutiExport implements WithMultipleSheets
{
    use Exportable;

    // private $startDate;
    // private $endDate;
    private $filters;

    public function __construct($filters = [])
    {
        // $this->startDate = $startDate;
        // $this->endDate = $endDate;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Menambahkan sheet untuk setiap kategori presensi
        $tipeCutis = TipeCuti::all();
        foreach ($tipeCutis as $tipeCuti) {
            $sheets[] = new CutiSheet($this->filters, $tipeCuti->nama, $tipeCuti->id);
        }

        return $sheets;
    }
}
