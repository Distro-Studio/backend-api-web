<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\Premi;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGajiUnitSheet implements FromCollection, WithHeadings, WithTitle
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

        foreach ($this->unitKerjas as $unitKerja) {
            $penggajians = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year)
                ->get();

            $gajiBruto = $penggajians->sum('gaji_bruto');

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
            $tambahan = $tunjanganJabatan + $tunjanganFungsional + $tunjanganKhusus + $tunjanganLainnya + $uangLembur + $uangMakan + $bor + $rewardAbsensi + $tambahanLain;

            // gaji bruto, tambahan
            $totalPenghasilan = $gajiBruto + $tambahan;

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
            $jumlahKaryawan = Penggajian::whereHas('data_karyawans', function ($query) use ($unitKerja) {
                $query->where('unit_kerja_id', $unitKerja->id);
            })->distinct('data_karyawan_id')->count('data_karyawan_id');

            $totalKaryawanUnitKerja = DataKaryawan::where('unit_kerja_id', $unitKerja->id)->count();

            $premiValues = [];
            foreach ($premis as $premi) {
                $besaranPremi = 0;
                foreach ($penggajians as $penggajian) {
                    $besaranPremi += $penggajian->detail_gajis->where('nama_detail', $premi->nama_premi)->sum('besaran');
                }
                $premiValues[] = $besaranPremi;
            }

            $rows[] = [
                $unitKerja->nama_unit,
                $totalKaryawanUnitKerja,
                $jumlahKaryawan,
                $gajiBruto,
                $tambahan,
                $totalPenghasilan,
                $pph21,
                $koperasi,
                $obat,
                ...$premiValues,
                $potonganLain,
                $jumlahPotongan
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        $premis = Premi::whereNull('deleted_at')->get();

        $headers = [
            'Nama Unit',
            'Jumlah Karyawan Unit',
            'Jumlah Karyawan Digaji',
            'Gaji Bruto',
            'Tambahan',
            'Total Penghasilan',
            'PPh21',
            'Pot. Koperasi',
            'Pot. Obat'
        ];

        foreach ($premis as $premi) {
            $headers[] = $premi->nama_premi;
        }

        $headers[] = 'Potongan Lainnya';
        $headers[] = 'Jumlah Potongan';

        return $headers;
    }

    public function title(): string
    {
        return "{$this->sheetType} - {$this->periode_sekarang}";
    }
}
