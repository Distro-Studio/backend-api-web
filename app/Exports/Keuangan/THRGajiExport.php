<?php

namespace App\Exports\Keuangan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\DetailGaji;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Keuangan\LaporanPenggajian\Sheet\THRGajiSheet;
use App\Exports\Sheet\RekapGajiTHRSheet;

class THRGajiExport implements FromCollection, WithMultipleSheets
{
    public function collection()
    {
        return collect([]);
    }

    public function sheets(): array
    {
        $sheets = [];
        $years = DB::table('riwayat_penggajians')
            ->select(DB::raw('YEAR(periode) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        foreach ($years as $year) {
            $sheets[] = new RekapGajiTHRSheet($year);
        }

        return $sheets;
    }
}
