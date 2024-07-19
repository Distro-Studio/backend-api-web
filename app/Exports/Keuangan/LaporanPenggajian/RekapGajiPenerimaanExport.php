<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Keuangan\LaporanPenggajian\Sheet\RekapGajiPenerimaanSheet;

class RekapGajiPenerimaanExport implements FromCollection, WithMultipleSheets
{
    use Exportable;

    protected $unit_kerja_ids;

    public function __construct($unit_kerja_ids)
    {
        $this->unit_kerja_ids = $unit_kerja_ids;
    }

    public function collection()
    {
        return collect([]);
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->unit_kerja_ids as $unit_kerja_id) {
            $sheets[] = new RekapGajiPenerimaanSheet($unit_kerja_id);
        }
        return $sheets;
    }
}
