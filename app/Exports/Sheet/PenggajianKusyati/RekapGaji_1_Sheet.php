<?php

namespace App\Exports\Sheet\PenggajianKusyati;

use Carbon\Carbon;
use App\Models\Penggajian;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGaji_1_Sheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $sheetType;
    protected $unitKerjas;
    protected $periode_sekarang;
    protected $month;
    protected $year;

    public function __construct($sheetType, $unitKerjas, $month, $year)
    {
        $this->sheetType = $sheetType;
        $this->unitKerjas = $unitKerjas;
        $this->periode_sekarang = Carbon::create($year, $month)->locale('id')->isoFormat('MMMM Y');
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        if ($this->unitKerjas->isEmpty()) {
            return collect([]);
        }

        $rows = [];
        $counter = 1;
        $totals = [
            'Jumlah Karyawan' => 0,
            'Take Home Pay' => 0,
        ];

        foreach ($this->unitKerjas as $unitKerja) {
            // TODO: Lakukan pengelompokan lagi dari unit kerja yang diambil.
            // 1. Ambil karyawan yg digaji dulu
            // BIKIN 3 variabel direksi, karyawan, dan magang_kontrak = []

            // direksi => dari step 1, ambil data karyawan where unit_kerjas.kategori_unit_id = 1
            // magang_kontrak => dari step 1, ambil data karyawan where status_karyawan_id = 2 dan 3
            // karyawan => dari step 1, ambil data karyawan where unit_kerjas.kategori_unit_id = 2, dan where status_karyawan_id = 1

            $penggajians = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year)
                ->get();

            $takeHomePay = $penggajians->sum('take_home_pay');

            // Calculate total number employees in this unit
            $jumlahKaryawanGaji = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->distinct('data_karyawan_id')->count('data_karyawan_id');

            $rows[] = [
                'No' => $counter++,
                'Unit Kerja' => $unitKerja->nama_unit,
                'Jumlah Karyawan' => $jumlahKaryawanGaji,
                'Take Home Pay' => $takeHomePay
            ];

            $totals['Jumlah Karyawan'] += $jumlahKaryawanGaji;
            $totals['Take Home Pay'] += $takeHomePay;
        }

        $rows[] = array_merge(
            [
                'No' => 'Total',
                'Unit Kerja' => '',
                $totals['Jumlah Karyawan'],
                $totals['Take Home Pay']
            ]
        );

        return collect($rows);
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Unit Kerja',
            'Jumlah Karyawan',
            'Take Home Pay',
        ];

        return $headers;
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
