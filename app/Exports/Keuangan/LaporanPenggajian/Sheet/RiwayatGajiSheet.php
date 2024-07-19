<?php

namespace App\Exports\Keuangan\LaporanPenggajian\Sheet;

use Carbon\Carbon;
use App\Models\User;
use App\Models\RiwayatPenggajian;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RiwayatGajiSheet implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    protected $month;
    protected $year;
    protected $periode;
    protected $title_periode;
    protected $title_jumlah_karyawan;
    protected $title_status;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
        $this->periode = 'Penggajian ' . Carbon::create($this->year, $this->month)->locale('id')->isoFormat('MMMM Y');
    }

    public function collection()
    {
        $riwayatPenggajian = RiwayatPenggajian::with(['penggajians' => function ($query) {
            $query->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year);
        }])->whereHas('penggajians', function ($query) {
            $query->whereMonth('tgl_penggajian', $this->month)
                ->whereYear('tgl_penggajian', $this->year);
        })->get();

        if ($riwayatPenggajian->isNotEmpty()) {
            $this->title_periode = Carbon::parse($riwayatPenggajian->first()->created_at)->locale('id')->isoFormat('MMMM Y');
            $this->title_jumlah_karyawan = $riwayatPenggajian->first()->karyawan_verifikasi;
            $this->title_status = $riwayatPenggajian->first()->status_description;
        }

        $exportData = $riwayatPenggajian->flatMap(function ($riwayatGaji) {
            return $riwayatGaji->penggajians->map(function ($penggajian, $index) use ($riwayatGaji) {
                // Ambil nama karyawan dari tabel users
                $user = User::whereHas('data_karyawans', function ($query) use ($penggajian) {
                    $query->where('id', $penggajian->data_karyawan_id);
                })->first();

                return [
                    'no' => $index + 1,
                    'nama_karyawan' => $user->nama,
                    'gaji_pokok' => $penggajian->gaji_pokok,
                    'total_tunjangan' => $penggajian->total_tunjangan,
                    'reward' => $penggajian->reward ?? 0,
                    'gaji_bruto' => $penggajian->gaji_bruto,
                    'total_premi' => $penggajian->total_premi ?? 0,
                    'pph_21' => $penggajian->pph_21,
                    'take_home_pay' => $penggajian->take_home_pay,
                    'status_penggajian' => $penggajian->status_description,
                    'created_at' => $penggajian->created_at->format('Y-m-d'),
                    'updated_at' => $penggajian->updated_at->format('Y-m-d'),
                ];
            });
        });

        return collect($exportData);
    }

    public function headings(): array
    {
        if (!isset($this->title_periode) || !isset($this->title_jumlah_karyawan) || !isset($this->title_status)) {
            $this->collection();
        }

        return [
            ["Periode Penggajian Bulanan: {$this->title_periode}"],
            ["Status Penggajian: {$this->title_status}"],
            ["Jumlah Karyawan Digaji: {$this->title_jumlah_karyawan}"],
            [
                'no',
                'nama_karyawan',
                'gaji_pokok',
                'total_tunjangan',
                'reward',
                'gaji_bruto',
                'total_premi',
                'pph_21',
                'take_home_pay',
                'status_penggajian',
                'created_at',
                'updated_at',
            ]
        ];
    }

    public function title(): string
    {
        return $this->periode;
    }
}
