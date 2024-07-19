<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use App\Exports\Keuangan\LaporanPenggajian\Sheet\RekapGajiPotonganSheet;
use Carbon\Carbon;
use App\Models\UnitKerja;
use App\Models\DataKaryawan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapGajiPotonganExport implements FromCollection, WithMultipleSheets
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
            $sheets[] = new RekapGajiPotonganSheet($unit_kerja_id);
        }
        return $sheets;
    }
}
