<?php

namespace App\Exports\Sheet;

use App\Models\DataKaryawan;
use Carbon\Carbon;
use App\Models\Penggajian;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGajiKompetensiSheet implements FromCollection, WithHeadings, WithTitle
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

        $rows = [];

        foreach ($this->kompetensi as $kompetensiInstance) {
            $penggajian = Penggajian::whereHas('data_karyawans', function ($query) use ($kompetensiInstance) {
                $query->where('kompetensi_id', $kompetensiInstance->id);
            })->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year)
                ->get();
            // dd($kompetensi);

            $takeHomePay = $penggajian->sum('take_home_pay');
            $jumlahKaryawan = Penggajian::whereHas('data_karyawans', function ($query) use ($kompetensiInstance) {
                $query->where('kompetensi_id', $kompetensiInstance->id);
            })->distinct('data_karyawan_id')->count('data_karyawan_id');
            $totalKaryawanKompetensi = DataKaryawan::where('kompetensi_id', $kompetensiInstance->id)->count();

            $rows[] = [
                $kompetensiInstance->nama_kompetensi,
                $totalKaryawanKompetensi,
                $jumlahKaryawan,
                $takeHomePay
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Nama Kompetensi',
            'Jumlah Karyawan Kompetensi',
            'Jumlah Karyawan Digaji',
            'Jumlah'
        ];
    }

    public function title(): string
    {
        return "{$this->sheetType} - {$this->periode_sekarang}";
    }
}
