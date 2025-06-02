<?php

namespace App\Exports\Sheet\PenggajianKusyati;

use Carbon\Carbon;
use App\Models\Penggajian;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGaji_2_Sheet implements FromCollection, WithHeadings, WithTitle, WithEvents
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
            'Gaji Pokok' => 0,
            'Tunjangan Jabatan' => 0,
            'Tunjangan Fungsional' => 0,
            'Tunjangan Khusus' => 0,
            'Tunjangan Lainnya' => 0,
            'Uang Lembur' => 0,
            'Uang Makan' => 0,
            'Reward BOR' => 0,
            'Reward Absensi' => 0,
            'Tambahan Lainnya' => 0,
            'Total Penghasilan' => 0,
            'Jumlah Potongan' => 0,
            'Take Home Pay' => 0,
        ];

        foreach ($this->unitKerjas as $unitKerja) {
            $penggajians = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year)
                ->get();

            $takeHomePay = $penggajians->sum('take_home_pay');

            $gajiPokok = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Gaji Pokok')->sum('besaran');
            });

            $tunjanganJabatan = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Jabatan')->sum('besaran');
            });

            $tunjanganFungsional = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Fungsional')->sum('besaran');
            });

            $tunjanganKhusus = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Khusus')->sum('besaran');
            });

            $tunjanganLainnya = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Lainnya')->sum('besaran');
            });

            $uangLembur = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Uang Lembur')->sum('besaran');
            });

            $bor = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Reward BOR')->sum('besaran');
            });

            $rewardAbsensi = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Reward Absensi')->sum('besaran');
            });

            $uangMakan = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Uang Makan')->sum('besaran');
            });

            $tambahanLain = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('kategori_gaji_id', 2)
                    ->whereNotIn('nama_detail', [
                        'Gaji Pokok',
                        'Tunjangan Jabatan',
                        'Tunjangan Fungsional',
                        'Tunjangan Khusus',
                        'Tunjangan Lainnya',
                        'Uang Lembur',
                        'Uang Makan',
                        'Reward BOR',
                        'Reward Absensi'
                    ])->sum('besaran');
            });

            // tunjangan, uang lembur, uang makan, bor, reward
            $totalPenghasilan = $gajiPokok + $tunjanganJabatan + $tunjanganFungsional + $tunjanganKhusus + $tunjanganLainnya + $uangLembur + $uangMakan + $bor + $rewardAbsensi + $tambahanLain;

            // potongan
            $jumlahPotongan = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('kategori_gaji_id', 3)->sum('besaran');
            });

            // Calculate total number employees in this unit
            $jumlahKaryawanGaji = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->distinct('data_karyawan_id')->count('data_karyawan_id');

            $rows[] = [
                'No' => $counter++,
                'Unit Kerja' => $unitKerja->nama_unit,
                'Jumlah Karyawan' => $jumlahKaryawanGaji,
                'Gaji Pokok' => $gajiPokok,
                'Tunjangan Jabatan' => $tunjanganJabatan,
                'Tunjangan Fungsional' => $tunjanganFungsional,
                'Tunjangan Khusus' => $tunjanganKhusus,
                'Tunjangan Lainnya' => $tunjanganLainnya,
                'Uang Lembur' => $uangLembur,
                'Uang Makan' => $uangMakan,
                'Reward BOR' => $bor,
                'Reward Absensi' => $rewardAbsensi,
                'Tambahan Lainnya' => $tambahanLain,
                'Total Penghasilan' => $totalPenghasilan,
                'Jumlah Potongan' => $jumlahPotongan,
                'Take Home Pay' => $takeHomePay
            ];

            $totals['Jumlah Karyawan'] += $jumlahKaryawanGaji;
            $totals['Gaji Pokok'] += $gajiPokok;
            $totals['Tunjangan Jabatan'] += $tunjanganJabatan;
            $totals['Tunjangan Fungsional'] += $tunjanganFungsional;
            $totals['Tunjangan Khusus'] += $tunjanganKhusus;
            $totals['Tunjangan Lainnya'] += $tunjanganLainnya;
            $totals['Uang Lembur'] += $uangLembur;
            $totals['Uang Makan'] += $uangMakan;
            $totals['Reward BOR'] += $bor;
            $totals['Reward Absensi'] += $rewardAbsensi;
            $totals['Tambahan Lainnya'] += $tambahanLain;
            $totals['Total Penghasilan'] += $totalPenghasilan;
            $totals['Jumlah Potongan'] += $jumlahPotongan;
            $totals['Take Home Pay'] += $takeHomePay;
        }

        $rows[] = array_merge(
            [
                'No' => 'Total',
                'Unit Kerja' => '',
                $totals['Jumlah Karyawan'],
                $totals['Gaji Pokok'],
                $totals['Tunjangan Jabatan'],
                $totals['Tunjangan Fungsional'],
                $totals['Tunjangan Khusus'],
                $totals['Tunjangan Lainnya'],
                $totals['Uang Lembur'],
                $totals['Uang Makan'],
                $totals['Reward BOR'],
                $totals['Reward Absensi'],
                $totals['Tambahan Lainnya'],
                $totals['Total Penghasilan'],
                $totals['Jumlah Potongan'],
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
            'Gaji Pokok',
            'Tunjangan Jabatan',
            'Tunjangan Fungsional',
            'Tunjangan Khusus',
            'Tunjangan Lainnya',
            'Uang Lembur',
            'Uang Makan',
            'Reward BOR',
            'Reward Absensi',
            'Tambahan Lainnya',
            'Total Penghasilan',
            'Jumlah Potongan',
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
