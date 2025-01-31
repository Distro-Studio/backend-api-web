<?php

namespace App\Exports\Keuangan\LaporanPenggajian;

use App\Models\Kompetensi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Sheet\RekapGajiKompetensiSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapGajiKompetensiExport implements FromCollection, WithMultipleSheets
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
                $kompetensiMedis = Kompetensi::where('jenis_kompetensi', 1)
                    ->whereHas('data_karyawan.penggajians', function ($query) use ($month, $year) {
                        $query->whereMonth('tgl_penggajian', $month)
                            ->whereYear('tgl_penggajian', $year);
                    })
                    ->get();

                $kompetensiNonMedis = Kompetensi::where('jenis_kompetensi', 0)
                    ->whereHas('data_karyawan.penggajians', function ($query) use ($month, $year) {
                        $query->whereMonth('tgl_penggajian', $month)
                            ->whereYear('tgl_penggajian', $year);
                    })
                    ->get();

                // Gabungkan kompetensi medis dan non-medis
                $gabunganKompetensi = $kompetensiMedis->merge($kompetensiNonMedis);

                // gabungan kompetensi
                if ($gabunganKompetensi->isNotEmpty()) {
                    $sheets[] = new RekapGajiKompetensiSheet('Medis dan Non-Medis', $gabunganKompetensi, $month, $year);
                }

                // Add Medis sheet
                if ($kompetensiMedis->isNotEmpty()) {
                    $sheets[] = new RekapGajiKompetensiSheet('Medis', $kompetensiMedis, $month, $year);
                }

                // Add Non-Medis sheet
                if ($kompetensiNonMedis->isNotEmpty()) {
                    $sheets[] = new RekapGajiKompetensiSheet('NonMedis', $kompetensiNonMedis, $month, $year);
                }
            }
        }

        if (empty($sheets)) {
            $sheets[] = new RekapGajiKompetensiSheet('Sheet Kosong', collect([]), null, null);
        }

        return $sheets;
    }
}
