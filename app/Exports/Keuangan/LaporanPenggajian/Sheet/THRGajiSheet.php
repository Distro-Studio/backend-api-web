<?php

namespace App\Exports\Keuangan\LaporanPenggajian\Sheet;

use Carbon\Carbon;
use App\Models\User;
use App\Models\DetailGaji;
use App\Models\RiwayatPenggajian;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class THRGajiSheet implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        $riwayatPenggajian = RiwayatPenggajian::with(['penggajians' => function ($query) {
            $query->whereHas('detail_gajis', function ($query) {
                $query->where('kategori', DetailGaji::STATUS_PENAMBAH)
                    ->where('nama_detail', 'THR')
                    ->whereNotNull('besaran');
            });
        }])
            ->whereYear('periode', $this->year)
            ->get();

        $exportData = $riwayatPenggajian->flatMap(function ($riwayatGaji) {
            return $riwayatGaji->penggajians->map(function ($penggajian) use ($riwayatGaji) {
                // Ambil nama karyawan dari tabel users
                $user = User::whereHas('data_karyawans', function ($query) use ($penggajian) {
                    $query->where('id', $penggajian->data_karyawan_id);
                })->first();

                $thrDetail = $penggajian->detail_gajis->where('kategori', DetailGaji::STATUS_PENAMBAH)
                    ->where('nama_detail', 'THR')
                    ->whereNotNull('besaran')
                    ->first();

                return [
                    'periode' => Carbon::parse($riwayatGaji->periode)->locale('id')->isoFormat('MMMM Y'),
                    'jumlah_karyawan_gaji' => $riwayatGaji->karyawan_verifikasi,
                    'status_riwayat_gaji' => $riwayatGaji->status_description,
                    'nama_karyawan' => $user->nama,
                    'gaji_pokok' => $penggajian->gaji_pokok,
                    'total_tunjangan' => $penggajian->total_tunjangan,
                    'reward' => $penggajian->reward ?? 'N/A',
                    'gaji_bruto' => $penggajian->gaji_bruto,
                    'total_premi' => $penggajian->total_premi ?? 'N/A',
                    'pph_21' => $penggajian->pph_21,
                    'take_home_pay' => $penggajian->take_home_pay,
                    'thr' => $thrDetail->besaran ?? 'N/A',
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
        return [
            ["THR Periode Tahun: {$this->year}"],
            [
                'periode_bulan',
                'jumlah_karyawan_gaji',
                'status_riwayat_gaji',
                'nama_karyawan',
                'gaji_pokok',
                'total_tunjangan',
                'reward',
                'gaji_bruto',
                'total_premi',
                'pph_21',
                'take_home_pay',
                'thr',
                'status_penggajian',
                'created_at',
                'updated_at',
            ]
        ];
    }

    public function title(): string
    {
        return $this->year;
    }
}
