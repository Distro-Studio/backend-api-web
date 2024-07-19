<?php

namespace App\Exports\Keuangan\LaporanPenggajian\Sheet;

use Carbon\Carbon;
use App\Models\UnitKerja;
use App\Models\DataKaryawan;
use App\Models\PenyesuaianGaji;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGajiPotonganSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

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
        $penggajianData = DB::table('penggajians')
            ->join('data_karyawans', 'penggajians.data_karyawan_id', '=', 'data_karyawans.id')
            ->join('users', 'data_karyawans.user_id', '=', 'users.id')
            ->join('unit_kerjas', 'data_karyawans.unit_kerja_id', '=', 'unit_kerjas.id')
            ->select(
                'data_karyawans.id as data_karyawan',
                'users.nama as nama_karyawan',
                'unit_kerjas.nama_unit as unit_kerja',
                'penggajians.gaji_bruto',
                'penggajians.pph_21',
                'penggajians.total_premi',
                'penggajians.take_home_pay'
            )
            ->where('data_karyawans.unit_kerja_id', $this->unit_kerja_id)
            ->whereMonth('penggajians.tgl_penggajian', $currentMonth)
            ->whereYear('penggajians.tgl_penggajian', $currentYear)
            ->get()
            ->groupBy('data_karyawan');

        $pengurangGajiData = DB::table('pengurang_gajis')
            ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
            ->join('data_karyawans', 'pengurang_gajis.data_karyawan_id', '=', 'data_karyawans.id')
            ->select(
                'data_karyawans.id as data_karyawan',
                'premis.nama_premi as nama_premi',
                'premis.besaran_premi as besaran_premi'
            )
            ->where('data_karyawans.unit_kerja_id', $this->unit_kerja_id)
            ->get()
            ->groupBy('data_karyawan');

        $penyesuaianGajiData = DB::table('penyesuaian_gajis')
            ->join('penggajians', 'penyesuaian_gajis.penggajian_id', '=', 'penggajians.id')
            ->join('data_karyawans', 'penggajians.data_karyawan_id', '=', 'data_karyawans.id')
            ->select(
                'data_karyawans.id as data_karyawan',
                'penyesuaian_gajis.nama_detail as nama_detail',
                'penyesuaian_gajis.besaran as besaran'
            )
            ->where('data_karyawans.unit_kerja_id', $this->unit_kerja_id)
            ->where('penyesuaian_gajis.kategori', PenyesuaianGaji::STATUS_PENGURANG)
            ->whereMonth('penyesuaian_gajis.created_at', $currentMonth)
            ->whereYear('penyesuaian_gajis.created_at', $currentYear)
            ->get()
            ->groupBy('data_karyawan');

        $exportData = collect([]);
        $counter = 1;

        foreach ($penggajianData as $karyawanId => $details) {
            $firstDetail = $details->first();

            $data = [
                'no' => $counter++,
                'nama_karyawan' => $firstDetail->nama_karyawan,
                'gaji_bruto' => $firstDetail->gaji_bruto ?? 0,
                'pph_21' => $firstDetail->pph_21 ?? 0,
                'total_premi' => $firstDetail->total_premi ?? 0,
                'take_home_pay' => $firstDetail->take_home_pay ?? 0,
            ];

            if (isset($pengurangGajiData[$karyawanId])) {
                foreach ($pengurangGajiData[$karyawanId] as $pengurang) {
                    $data[$pengurang->nama_premi] = $pengurang->besaran_premi;
                }
            }

            if (isset($penyesuaianGajiData[$karyawanId])) {
                foreach ($penyesuaianGajiData[$karyawanId] as $penyesuaian) {
                    $data[$penyesuaian->nama_detail] = $penyesuaian->besaran;
                }
            }

            $exportData->push($data);
        }

        return $exportData;
    }

    public function headings(): array
    {
        // Add unique headings from pengurang_gajis
        $pengurangHeadings = DB::table('pengurang_gajis')
            ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
            ->distinct()
            ->pluck('premis.nama_premi')
            ->map(function ($item) {
                return 'premi_' . str_replace(' ', '_', strtolower($item));
            })
            ->toArray();

        // Add unique headings from penyesuaian_gajis
        $penyesuaianHeadings = DB::table('penyesuaian_gajis')
            ->where('kategori', PenyesuaianGaji::STATUS_PENGURANG)
            ->distinct()
            ->pluck('nama_detail')
            ->map(function ($item) {
                return 'pengurang_' . str_replace(' ', '_', strtolower($item));
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
                    'gaji_bruto',
                    'pph_21',
                    'total_premi',
                    'take_home_pay',
                ],
                $pengurangHeadings,
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
            $row['gaji_bruto'],
            $row['pph_21'],
            $row['total_premi'],
            $row['take_home_pay'],
        ];

        $pengurangHeadings = DB::table('pengurang_gajis')
            ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
            ->distinct()
            ->pluck('premis.nama_premi')
            ->toArray();

        foreach ($pengurangHeadings as $heading) {
            $mappedRow[] = $row[$heading] ?? 0;
        }

        $penyesuaianHeadings = DB::table('penyesuaian_gajis')
            ->where('kategori', PenyesuaianGaji::STATUS_PENGURANG)
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
