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
    protected $periode_sekarang;
    protected $month;
    protected $year;

    public function __construct($sheetType, $month, $year)
    {
        $this->sheetType = $sheetType;
        $this->periode_sekarang = Carbon::create($year, $month)->locale('id')->isoFormat('MMMM Y');
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        // Ambil semua penggajian di bulan & tahun tersebut
        $penggajians = Penggajian::with('data_karyawans.unit_kerjas')
            ->whereMonth('tgl_penggajian', $this->month)
            ->whereYear('tgl_penggajian', $this->year)
            ->get();

        // Filter Direksi (unit_kerjas.kategori_unit_id == 1)
        $direksiPenggajian = $penggajians->filter(function ($pg) {
            return $pg->data_karyawans->unit_kerjas->kategori_unit_id == 1;
        });
        $direksiCount = $direksiPenggajian->pluck('data_karyawan_id')->unique()->count();
        $direksiTakeHomePay = $direksiPenggajian->sum('take_home_pay');

        // Filter Magang/Kontrak (status_karyawan_id == 2 atau 3)
        $magangKontrakPenggajian = $penggajians->filter(function ($pg) {
            return in_array($pg->data_karyawans->status_karyawan_id, [2, 3]);
        });
        $magangKontrakCount = $magangKontrakPenggajian->pluck('data_karyawan_id')->unique()->count();
        $magangKontrakTakeHomePay = $magangKontrakPenggajian->sum('take_home_pay');

        // Filter Karyawan (unit_kerjas.kategori_unit_id == 2 dan status_karyawan_id == 1)
        $karyawanPenggajian = $penggajians->filter(function ($pg) {
            $unitKategori = $pg->data_karyawans->unit_kerjas->kategori_unit_id ?? null;
            return $unitKategori == 2 && $pg->data_karyawans->status_karyawan_id == 1;
        });
        $karyawanCount = $karyawanPenggajian->pluck('data_karyawan_id')->unique()->count();
        $karyawanTakeHomePay = $karyawanPenggajian->sum('take_home_pay');

        $rows = [];

        $rows[] = [
            'No' => 1,
            'Kategori' => 'Direksi',
            'Jumlah Karyawan' => $direksiCount,
            'Take Home Pay' => $direksiTakeHomePay,
        ];

        $rows[] = [
            'No' => 2,
            'Kategori' => 'Karyawan',
            'Jumlah Karyawan' => $karyawanCount,
            'Take Home Pay' => $karyawanTakeHomePay,
        ];

        $rows[] = [
            'No' => 3,
            'Kategori' => 'Magang/Kontrak',
            'Jumlah Karyawan' => $magangKontrakCount,
            'Take Home Pay' => $magangKontrakTakeHomePay,
        ];

        // Baris total keseluruhan
        $rows[] = [
            'No' => 'Total',
            'Kategori' => '',
            'Jumlah Karyawan' => $direksiCount + $karyawanCount + $magangKontrakCount,
            'Take Home Pay' => $direksiTakeHomePay + $karyawanTakeHomePay + $magangKontrakTakeHomePay,
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Kategori',
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
