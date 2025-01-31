<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\Premi;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGajiUnitSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
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

        $premis = Premi::whereNull('deleted_at')->get();
        $rows = [];
        $counter = 1;
        $totals = [
            'Jumlah Karyawan Unit' => 0,
            'Jumlah Karyawan Digaji' => 0,
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
            'PPh21' => 0,
            'Pot. Koperasi' => 0,
            'Pot. Obat' => 0,
            'Potongan Lainnya' => 0,
            'Jumlah Potongan' => 0,
            'Take Home Pay' => 0,
        ];

        foreach ($premis as $premi) {
            $totals["premi_{$premi->id}"] = 0;
        }

        foreach ($this->unitKerjas as $unitKerja) {
            $penggajians = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year)
                ->get();

            $gajiBruto = $penggajians->sum('gaji_bruto');

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
            $pph21 = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'PPh21')->sum('besaran');
            });

            $koperasi = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Koperasi')->sum('besaran');
            });

            $obat = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('nama_detail', 'Obat/Perawatan')->sum('besaran');
            });

            $premiNames = $premis->pluck('nama_premi')->toArray();
            $potonganLain = $penggajians->sum(function ($penggajian) use ($premiNames) {
                return $penggajian->detail_gajis->where('kategori_gaji_id', 3)
                    ->whereNotIn('nama_detail', [
                        'PPh21',
                        'Koperasi',
                        'Obat/Perawatan'
                    ])
                    ->whereNotIn('nama_detail', $premiNames)
                    ->sum('besaran');
            });

            $jumlahPotongan = $penggajians->sum(function ($penggajian) {
                return $penggajian->detail_gajis->where('kategori_gaji_id', 3)->sum('besaran');
            });

            // Calculate total number employees in this unit
            $jumlahKaryawanGaji = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->distinct('data_karyawan_id')->count('data_karyawan_id');

            $totalKaryawanUnitKerja = DataKaryawan::where('unit_kerja_id', $unitKerja->id)->count();

            $premiValues = [];
            foreach ($premis as $premi) {
                $besaranPremi = $penggajians->sum(function ($penggajian) use ($premi) {
                    return $penggajian->detail_gajis
                        ->where('nama_detail', $premi->nama_premi)
                        ->sum('besaran');
                });
                $premiValues["premi_{$premi->id}"] = $besaranPremi;
                $totals["premi_{$premi->id}"] += $besaranPremi;
            }

            $rows[] = [
                'No' => $counter++,
                'Nama Unit' => $unitKerja->nama_unit,
                'Jumlah Karyawan Unit' => $totalKaryawanUnitKerja,
                'Jumlah Karyawan Digaji' => $jumlahKaryawanGaji,
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
                'PPh21' => $pph21,
                'Pot. Koperasi' => $koperasi,
                'Pot. Obat' => $obat,
                ...$premiValues,
                'Potongan Lainnya' => $potonganLain,
                'Jumlah Potongan' => $jumlahPotongan,
                'Take Home Pay' => $takeHomePay
            ];

            $totals['Jumlah Karyawan Unit'] += $totalKaryawanUnitKerja;
            $totals['Jumlah Karyawan Digaji'] += $jumlahKaryawanGaji;
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
            $totals['PPh21'] += $pph21;
            $totals['Pot. Koperasi'] += $koperasi;
            $totals['Pot. Obat'] += $obat;
            $totals['Potongan Lainnya'] += $potonganLain;
            $totals['Jumlah Potongan'] += $jumlahPotongan;
            $totals['Take Home Pay'] += $takeHomePay;
        }

        $rows[] = array_merge(
            [
                'No' => 'Total',
                'Nama Unit' => '',
                $totals['Jumlah Karyawan Unit'],
                $totals['Jumlah Karyawan Digaji'],
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
                $totals['PPh21'],
                $totals['Pot. Koperasi'],
                $totals['Pot. Obat'],
            ],
            array_map(fn($premiId) => $totals["premi_{$premiId}"], $premis->pluck('id')->toArray()),
            [
                $totals['Potongan Lainnya'],
                $totals['Jumlah Potongan'],
                $totals['Take Home Pay'],
            ]
        );

        return collect($rows);
    }

    public function headings(): array
    {
        $premis = Premi::whereNull('deleted_at')->get();

        $headers = [
            'No',
            'Nama Unit',
            'Jumlah Karyawan Unit',
            'Jumlah Karyawan Digaji',
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
            'PPh21',
            'Pot. Koperasi',
            'Pot. Obat',
        ];

        foreach ($premis as $premi) {
            $headers[] = $premi->nama_premi;
        }

        $headers[] = 'Potongan Lainnya';
        $headers[] = 'Jumlah Potongan';
        $headers[] = 'Take Home Pay';

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
