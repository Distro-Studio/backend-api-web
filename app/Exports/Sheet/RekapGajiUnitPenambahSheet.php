<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\Premi;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGajiUnitPenambahSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $sheetType;
    protected $unitKerjas;
    protected $periode_sekarang;
    protected $month;
    protected $year;
    private static $number = 0;

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

        foreach ($this->unitKerjas as $unitKerja) {
            $penggajians = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year)
                ->get();

            $gajiBruto = $penggajians->sum('gaji_bruto');

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
            $tambahan = $tunjanganJabatan + $tunjanganFungsional + $tunjanganKhusus + $tunjanganLainnya + $uangLembur + $uangMakan + $bor + $rewardAbsensi + $tambahanLain;

            // gaji bruto, tambahan
            $totalPenghasilan = $gajiBruto + $tambahan;

            // Calculate total number employees in this unit
            $jumlahKaryawan = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->distinct('data_karyawan_id')->count('data_karyawan_id');

            $totalKaryawanUnitKerja = DataKaryawan::where('unit_kerja_id', $unitKerja->id)->count();

            self::$number++;
            $rows[] = [
                self::$number,
                $unitKerja->nama_unit,
                $totalKaryawanUnitKerja,
                $jumlahKaryawan,
                $gajiBruto,
                $tambahan,
                $totalPenghasilan
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Nama Unit',
            'Jumlah Karyawan Unit',
            'Jumlah Karyawan Digaji',
            'Gaji Bruto',
            'Tambahan',
            'Total Penghasilan',
        ];

        return $headers;
    }

    public function title(): string
    {
        return "{$this->sheetType} - {$this->periode_sekarang}";
    }
}
