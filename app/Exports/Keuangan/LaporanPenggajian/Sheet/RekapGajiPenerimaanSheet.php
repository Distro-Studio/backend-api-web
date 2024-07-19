<?php

namespace App\Exports\Keuangan\LaporanPenggajian\Sheet;

use Carbon\Carbon;
use App\Models\UnitKerja;
use App\Models\DataKaryawan;
use App\Models\PenyesuaianGaji;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapGajiPenerimaanSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $unit_kerja_id;
    protected $unit_kerja_nama;
    protected $jumlah_karyawan;
    protected $periode_sekarang;

    public function __construct($unit_kerja_id)
    {
        $this->unit_kerja_id = $unit_kerja_id;
        $this->unit_kerja_nama = UnitKerja::find($unit_kerja_id)->nama_unit;
        $this->jumlah_karyawan = DataKaryawan::where('unit_kerja_id', $unit_kerja_id)->count();
        $this->periode_sekarang = Carbon::now()->locale('id')->isoFormat('MMMM Y');
    }

    public function collection()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
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
            ->whereMonth('penggajians.tgl_penggajian', $currentMonth)
            ->whereYear('penggajians.tgl_penggajian', $currentYear)
            ->get()
            ->groupBy('data_karyawan');

        $penyesuaianGajiData = DB::table('penyesuaian_gajis')
            ->join('penggajians', 'penyesuaian_gajis.penggajian_id', '=', 'penggajians.id')
            ->join('data_karyawans', 'penggajians.data_karyawan_id', '=', 'data_karyawans.id')
            ->select(
                'data_karyawans.id as data_karyawan',
                'penyesuaian_gajis.nama_detail',
                'penyesuaian_gajis.besaran'
            )
            ->where('data_karyawans.unit_kerja_id', $this->unit_kerja_id)
            ->where('penyesuaian_gajis.kategori', PenyesuaianGaji::STATUS_PENAMBAH)
            ->whereMonth('penyesuaian_gajis.created_at', $currentMonth)
            ->whereYear('penyesuaian_gajis.created_at', $currentYear)
            ->get()
            ->groupBy('data_karyawan');

        $exportData = collect([]);
        $counter = 1;

        foreach ($detailGajiData as $karyawanId => $details) {
            $firstDetail = $details->first();

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
                'bonus_bor' => $details->where('nama_detail', 'Bonus BOR')->first()->besaran ?? 0,
                'bonus_presensi' => $details->where('nama_detail', 'Bonus Presensi')->first()->besaran ?? 0,
                'jumlah_penghasilan' => $firstDetail->gaji_bruto ?? 0,
                'jumlah_potongan' => $firstDetail->total_premi ?? 0,
                'gaji_diterima' => $firstDetail->take_home_pay ?? 0,
            ];

            if (isset($penyesuaianGajiData[$karyawanId])) {
                foreach ($penyesuaianGajiData[$karyawanId] as $penyesuaian) {
                    $data[$penyesuaian->nama_detail] = $penyesuaian->besaran ?? 0;
                }
            }

            $exportData->push($data);
        }

        return $exportData;
    }

    public function headings(): array
    {
        $penyesuaianHeadings = DB::table('penyesuaian_gajis')
            ->where('kategori', PenyesuaianGaji::STATUS_PENAMBAH)
            ->distinct()
            ->pluck('nama_detail')
            ->map(function ($item) {
                return 'penambah_' . str_replace(' ', '_', strtolower($item));
            })
            ->toArray();

        $heading = [
            ["Unit Kerja: {$this->unit_kerja_nama}"],
            ["Jumlah karyawan per unit kerja: {$this->jumlah_karyawan}"],
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
                    'bonus_bor',
                    'bonus_presensi',
                    'jumlah_penghasilan',
                    'jumlah_potongan',
                    'gaji_diterima',
                ],
                $penyesuaianHeadings
            )
        ];

        return $heading;
    }

    public function map($row): array
    {
        $mappedRow = [
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
            $row['jumlah_potongan'],
            $row['gaji_diterima']
        ];

        $penyesuaianHeadings = DB::table('penyesuaian_gajis')
            ->where('kategori', PenyesuaianGaji::STATUS_PENAMBAH)
            ->distinct()
            ->pluck('nama_detail')
            ->toArray();

        foreach ($penyesuaianHeadings as $heading) {
            $mappedRow[] = $row[$heading] ?? 0;
        }

        return $mappedRow;
    }

    public function title(): string
    {
        return $this->unit_kerja_nama;
    }
}
