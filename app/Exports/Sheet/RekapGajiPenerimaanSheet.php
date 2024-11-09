<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGajiPenerimaanSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $unit_kerja_id;
    protected $unit_kerja_nama;
    protected $periode_sekarang;
    protected $month;
    protected $year;

    public function __construct($unit_kerja_id, $month, $year)
    {
        $this->unit_kerja_id = $unit_kerja_id;
        $this->unit_kerja_nama = UnitKerja::find($unit_kerja_id)->nama_unit;
        $this->periode_sekarang = Carbon::create($year, $month)->locale('id')->isoFormat('MMMM Y');
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $detailGajiData = DB::table('detail_gajis')
            ->join('penggajians', 'detail_gajis.penggajian_id', '=', 'penggajians.id')
            ->join('data_karyawans', 'penggajians.data_karyawan_id', '=', 'data_karyawans.id')
            ->join('users', 'data_karyawans.user_id', '=', 'users.id')
            ->select(
                'data_karyawans.id as data_karyawan',
                'users.nama as nama_karyawan',
                'detail_gajis.nama_detail',
                'detail_gajis.besaran',
                'penggajians.gaji_bruto as gaji_bruto',
                'penggajians.total_premi as total_premi',
                'penggajians.take_home_pay as take_home_pay'
            )
            ->where('data_karyawans.unit_kerja_id', $this->unit_kerja_id)
            ->whereMonth('penggajians.tgl_penggajian', $this->month)
            ->whereYear('penggajians.tgl_penggajian', $this->year)
            ->get()
            ->groupBy('data_karyawan');

        $exportData = collect([]);
        $counter = 1;

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
                'nama_karyawan' => $firstDetail->nama_karyawan,
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
                'gaji_diterima' => $firstDetail->take_home_pay ?? 0,
            ];
            $exportData->push($data);
        }
        return $exportData;
    }

    public function headings(): array
    {
        $heading = [
            ["Unit Kerja: {$this->unit_kerja_nama}"],
            ["Periode: {$this->periode_sekarang}"],
            array_merge(
                [
                    'no',
                    'nama_karyawan',
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
            $row['nama_karyawan'],
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
        return $this->unit_kerja_nama . ' - ' . $this->periode_sekarang;
    }
}
