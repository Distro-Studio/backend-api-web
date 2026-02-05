<?php

namespace App\Exports\Jadwal\CutiNew;

use App\Models\TipeCuti;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CutiExport implements WithMultipleSheets
{
    use Exportable;

    private $filters;
    private $startDate;
    private $endDate;
    private $tipeCutiFilter;

    public function __construct($filters = [], $startDate, $endDate, $tipeCutiFilter = null)
    {
        $this->filters = $filters;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tipeCutiFilter = is_array($tipeCutiFilter) ? $tipeCutiFilter : [$tipeCutiFilter]; // pastikan array
    }

    public function sheets(): array
    {
        $sheets = [];

        // Menambahkan sheet untuk setiap kategori presensi
        $tipeCutis = TipeCuti::whereNotIn('id', [0, 5])
            ->when(!empty($this->tipeCutiFilter), function ($query) {
                $query->whereIn('id', $this->tipeCutiFilter);
            })
            ->get();
        foreach ($tipeCutis as $tipeCuti) {
            $sheets[] = new CutiSheet($this->filters, $tipeCuti->nama, $this->startDate, $this->endDate, $tipeCuti->id);
        }

        return $sheets;
    }
}
