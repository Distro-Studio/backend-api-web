<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGajiLaporanBankSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $month;
    protected $year;
    protected $periode_sekarang;
    protected $counter;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
        $this->periode_sekarang = Carbon::create($year, $month)->locale('id')->isoFormat('MMMM Y');
        $this->counter = 1; // Initialize counter for automatic numbering
    }

    public function collection()
    {
        return DB::table('penggajians')
            ->join('data_karyawans', 'penggajians.data_karyawan_id', '=', 'data_karyawans.id')
            ->join('users', 'data_karyawans.user_id', '=', 'users.id')
            ->select(
                'data_karyawans.id as data_karyawan',
                'data_karyawans.no_rekening as nomor_rekening',
                'users.nama as nama_karyawan',
                'penggajians.gaji_bruto',
                'penggajians.pph_21',
                'penggajians.total_premi',
                'penggajians.take_home_pay'
            )
            ->whereMonth('penggajians.tgl_penggajian', $this->month)
            ->whereYear('penggajians.tgl_penggajian', $this->year)
            ->get();
    }

    public function headings(): array
    {
        return [
            ["Periode: {$this->periode_sekarang}"],
            [
                'no',
                'nama_karyawan',
                'nomor_rekening',
                'gaji_bruto',
                'pph_21',
                'total_premi',
                'take_home_pay'
            ]
        ];
    }

    public function map($row): array
    {
        return [
            $this->counter++,
            $row->nama_karyawan,
            $row->nomor_rekening,
            $row->gaji_bruto,
            $row->pph_21,
            $row->total_premi,
            $row->take_home_pay
        ];
    }

    public function title(): string
    {
        return 'Laporan Bank - ' . $this->periode_sekarang;
    }
}
