<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;

class RekapGajiPenerimaanAllUnitsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
{
    protected $months;
    protected $years;
    protected $periode_sekarang;

    public function __construct(array $months, array $years)
    {
        $this->months = $months;
        $this->years = $years;
        $this->periode_sekarang = Carbon::createFromFormat('Y-m', "{$years[0]}-{$months[0]}")->locale('id')->isoFormat('MMMM Y');
    }

    public function collection()
    {
        $detailGajiData = DB::table('detail_gajis')
            ->join('penggajians', 'detail_gajis.penggajian_id', '=', 'penggajians.id')
            ->join('data_karyawans', 'penggajians.data_karyawan_id', '=', 'data_karyawans.id')
            ->join('status_karyawans', 'data_karyawans.status_karyawan_id', '=', 'status_karyawans.id')
            ->join('users', 'data_karyawans.user_id', '=', 'users.id')
            ->join('unit_kerjas', 'data_karyawans.unit_kerja_id', '=', 'unit_kerjas.id')
            ->select(
                'data_karyawans.id as data_karyawan',
                'data_karyawans.nik as nik',
                'users.nama as nama_karyawan',
                'status_karyawans.label as status_karyawan',
                'unit_kerjas.nama_unit as unit_kerja',
                'detail_gajis.nama_detail',
                'detail_gajis.besaran',
                'penggajians.gaji_bruto as gaji_bruto',
                'penggajians.total_premi as total_premi',
                'penggajians.take_home_pay as take_home_pay'
            )
            ->whereIn(DB::raw('MONTH(penggajians.tgl_penggajian)'), $this->months)
            ->whereIn(DB::raw('YEAR(penggajians.tgl_penggajian)'), $this->years)
            ->get()
            ->groupBy('data_karyawan');

        $exportData = collect([]);
        $counter = 1;
        $totals = [
            'gaji_pokok' => 0,
            'tunjangan_jabatan' => 0,
            'tunjangan_fungsional' => 0,
            'tunjangan_khusus' => 0,
            'tunjangan_lainnya' => 0,
            'uang_lembur' => 0,
            'uang_makan' => 0,
            'bonus_bor' => 0,
            'bonus_presensi' => 0,
            'pph21' => 0,
            'penambah_gaji' => 0,
            'pengurang_gaji' => 0,
            'jumlah_penghasilan' => 0,
            'jumlah_premi' => 0,
            'gaji_diterima' => 0,
        ];

        foreach ($detailGajiData as $karyawanId => $details) {
            $firstDetail = $details->first();

            $penyesuaian_penambah = $details->where('kategori_gaji_id', 2)
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

            $penyesuaian_pengurang = $details->where('kategori_gaji_id', 3)->whereNotIn('nama_detail', [
                'PPh21'
            ])->sum('besaran');

            $data = [
                'no' => $counter++,
                'nik' => $firstDetail->nik,
                'nama_karyawan' => $firstDetail->nama_karyawan,
                'status_karyawan' => $firstDetail->status_karyawan,
                'unit_kerja' => $firstDetail->unit_kerja,
                'gaji_pokok' => $details->where('nama_detail', 'Gaji Pokok')->first()->besaran ?? 0,
                'tunjangan_jabatan' => $details->where('nama_detail', 'Tunjangan Jabatan')->first()->besaran ?? 0,
                'tunjangan_fungsional' => $details->where('nama_detail', 'Tunjangan Fungsional')->first()->besaran ?? 0,
                'tunjangan_khusus' => $details->where('nama_detail', 'Tunjangan Khusus')->first()->besaran ?? 0,
                'tunjangan_lainnya' => $details->where('nama_detail', 'Tunjangan Lainnya')->first()->besaran ?? 0,
                'uang_lembur' => $details->where('nama_detail', 'Uang Lembur')->first()->besaran ?? 0,
                'uang_makan' => $details->where('nama_detail', 'Uang Makan')->first()->besaran ?? 0,
                'bonus_bor' => $details->where('nama_detail', 'Reward BOR')->first()->besaran ?? 0,
                'bonus_presensi' => $details->where('nama_detail', 'Reward Absensi')->first()->besaran ?? 0,
                'pph21' => $details->where('nama_detail', 'PPh21')->first()->besaran ?? 0,
                'penambah_gaji' => $penyesuaian_penambah,
                'pengurang_gaji' => $penyesuaian_pengurang,
                'jumlah_penghasilan' => $firstDetail->gaji_bruto ?? 0,
                'jumlah_premi' => $firstDetail->total_premi ?? 0,
                'gaji_diterima' => $firstDetail->take_home_pay ?? 0
            ];

            foreach ($totals as $key => &$total) {
                $total += $data[$key];
            }

            $exportData->push($data);
        }

        // Tambahkan baris Total
        $totalsRow = array_merge(
            ['no' => 'Total', 'nik' => '', 'nama_karyawan' => '', 'status_karyawan' => '', 'unit_kerja' => ''],
            $totals
        );

        $exportData->push($totalsRow);

        return $exportData;
    }

    public function headings(): array
    {
        $heading = [
            ['Rekap Penggajian Semua Unit Kerja'],
            ["Periode: {$this->periode_sekarang}"],
            array_merge(
                [
                    'no',
                    'nik',
                    'nama_karyawan',
                    'status_karyawan',
                    'unit_kerja',
                    'gaji_pokok',
                    'tunjangan_jabatan',
                    'tunjangan_fungsional',
                    'tunjangan_khusus',
                    'tunjangan_lainnya',
                    'uang_lembur',
                    'uang_makan',
                    'reward_bor',
                    'reward_absensi',
                    'jumlah_penghasilan',
                    'jumlah_premi',
                    'PPh21',
                    'penambah_gaji',
                    'pengurang_gaji',
                    'take_home_pay'
                ]
            )
        ];

        return $heading;
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['nik'],
            $row['nama_karyawan'],
            $row['status_karyawan'],
            $row['unit_kerja'],
            $row['gaji_pokok'],
            $row['tunjangan_jabatan'],
            $row['tunjangan_fungsional'],
            $row['tunjangan_khusus'],
            $row['tunjangan_lainnya'],
            $row['uang_lembur'],
            $row['uang_makan'],
            $row['bonus_bor'],
            $row['bonus_presensi'],
            $row['jumlah_penghasilan'],
            $row['jumlah_premi'],
            $row['pph21'],
            $row['penambah_gaji'],
            $row['pengurang_gaji'],
            $row['gaji_diterima']
        ];
    }

    public function title(): string
    {
        return 'Rekap Penggajian Semua Unit Kerja ' . $this->periode_sekarang;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();

                // Merge kolom A sampai E di baris terakhir
                $sheet->mergeCells("A{$highestRow}:E{$highestRow}");

                // Set style untuk baris terakhir
                $sheet->getStyle("A{$highestRow}:E{$highestRow}")->applyFromArray([
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
