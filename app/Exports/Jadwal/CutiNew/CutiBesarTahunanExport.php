<?php

namespace App\Exports\Jadwal\CutiNew;

use App\Models\TipeCuti;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CutiBesarTahunanExport implements WithMultipleSheets
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

        if (in_array(1, $this->tipeCutiFilter)) {
            $sheets[] = new CutiTahunanSheet($this->filters, 'Cuti Tahunan', $this->startDate, $this->endDate, 1);
        }

        if (in_array(5, $this->tipeCutiFilter)) {
            $sheets[] = new CutiBesarSheet($this->filters, 'Cuti Besar', $this->startDate, $this->endDate, 5);
        }

        return $sheets;
    }
}
