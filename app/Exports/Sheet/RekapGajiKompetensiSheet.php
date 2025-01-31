<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;

class RekapGajiKompetensiSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $sheetType;
    protected $kompetensi;
    protected $periode_sekarang;
    protected $month;
    protected $year;

    public function __construct($sheetType, $kompetensi, $month, $year)
    {
        $this->sheetType = $sheetType;
        $this->kompetensi = $kompetensi;
        $this->periode_sekarang = Carbon::create($year, $month)->locale('id')->isoFormat('MMMM Y');
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        if ($this->kompetensi->isEmpty()) {
            return collect([]);
        }

        $counter = 1;
        $rows = [];
        $totals = [
            'Jumlah Karyawan Kompetensi' => 0,
            'Jumlah Karyawan Digaji' => 0,
            'Take Home Pay' => 0
        ];

        foreach ($this->kompetensi as $kompetensiInstance) {
            $penggajian = Penggajian::whereHas('data_karyawans', function ($query) use ($kompetensiInstance) {
                $query->where('kompetensi_id', $kompetensiInstance->id);
            })->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year)
                ->get();
            // dd($kompetensi);

            $takeHomePay = $penggajian->sum('take_home_pay');
            $jumlahKaryawanGaji = Penggajian::whereHas('data_karyawans', function ($query) use ($kompetensiInstance) {
                $query->where('kompetensi_id', $kompetensiInstance->id);
            })->distinct('data_karyawan_id')->count('data_karyawan_id');
            $totalKaryawanKompetensi = DataKaryawan::where('kompetensi_id', $kompetensiInstance->id)->count();

            $rows[] = [
                'No' => $counter++,
                'Nama Kompetensi' => $kompetensiInstance->nama_kompetensi,
                'Jumlah Karyawan Kompetensi' => $totalKaryawanKompetensi,
                'Jumlah Karyawan Digaji' => $jumlahKaryawanGaji,
                'Take Home Pay' => $takeHomePay
            ];

            $totals['Jumlah Karyawan Kompetensi'] += $totalKaryawanKompetensi;
            $totals['Jumlah Karyawan Digaji'] += $jumlahKaryawanGaji;
            $totals['Take Home Pay'] += $takeHomePay;
        }

        $rows[] = array_merge(
            [
                'No' => 'Total',
                'Nama Kompetensi' => '',
                $totals['Jumlah Karyawan Kompetensi'],
                $totals['Jumlah Karyawan Digaji'],
                $totals['Take Home Pay'],
            ]
        );

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Kompetensi',
            'Jumlah Karyawan Kompetensi',
            'Jumlah Karyawan Digaji',
            'Take Home Pay'
        ];
    }

    public function title(): string
    {
        return "{$this->sheetType} - {$this->periode_sekarang}";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();

                // Merge kolom A sampai E di baris terakhir
                $sheet->mergeCells("A{$highestRow}:B{$highestRow}");

                // Set style untuk baris terakhir
                $sheet->getStyle("A{$highestRow}:B{$highestRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            },
        ];
    }
}
