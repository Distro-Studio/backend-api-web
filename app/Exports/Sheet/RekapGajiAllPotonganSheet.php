<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekapGajiAllPotonganSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $month;
    protected $year;
    protected $periode_sekarang;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
        $this->periode_sekarang = Carbon::create($year, $month)->locale('id')->isoFormat('MMMM Y');
    }

    public function collection()
    {
        $unitKerjas = UnitKerja::all();
        $exportData = collect([]);
        $counter = 1;

        foreach ($unitKerjas as $unitKerja) {
            $penggajianData = DB::table('penggajians')
                ->join('data_karyawans', 'penggajians.data_karyawan_id', '=', 'data_karyawans.id')
                ->join('users', 'data_karyawans.user_id', '=', 'users.id')
                ->join('unit_kerjas', 'data_karyawans.unit_kerja_id', '=', 'unit_kerjas.id')
                ->select(
                    'data_karyawans.id as data_karyawan',
                    'data_karyawans.nik as nik',
                    'users.nama as nama_karyawan',
                    'unit_kerjas.nama_unit as unit_kerja',
                    'penggajians.gaji_bruto',
                    'penggajians.pph_21',
                    'penggajians.total_premi',
                    'penggajians.take_home_pay'
                )
                ->where('data_karyawans.unit_kerja_id', $unitKerja->id)
                ->whereMonth('penggajians.tgl_penggajian', $this->month)
                ->whereYear('penggajians.tgl_penggajian', $this->year)
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
                ->where('data_karyawans.unit_kerja_id', $unitKerja->id)
                ->whereMonth('pengurang_gajis.created_at', $this->month)
                ->whereYear('pengurang_gajis.created_at', $this->year)
                ->get()
                ->groupBy('data_karyawan');

            $penyesuaianGajiData = DB::table('penyesuaian_gajis')
                ->join('penggajians', 'penyesuaian_gajis.penggajian_id', '=', 'penggajians.id')
                ->join('data_karyawans', 'penggajians.data_karyawan_id', '=', 'data_karyawans.id')
                ->select(
                    'data_karyawans.id as data_karyawan',
                    DB::raw('SUM(penyesuaian_gajis.besaran) as total_penyesuaian')
                )
                ->where('data_karyawans.unit_kerja_id', $unitKerja->id)
                ->where('penyesuaian_gajis.kategori_gaji_id', 3)
                ->whereMonth('penyesuaian_gajis.created_at', $this->month)
                ->whereYear('penyesuaian_gajis.created_at', $this->year)
                ->groupBy('data_karyawans.id')
                ->get()
                ->keyBy('data_karyawan');
            $tagihanPotonganData = DB::table('tagihan_potongans')
                ->join('data_karyawans', 'tagihan_potongans.data_karyawan_id', '=', 'data_karyawans.id')
                ->join('kategori_tagihan_potongans', 'tagihan_potongans.kategori_tagihan_id', '=', 'kategori_tagihan_potongans.id')
                ->select(
                    'data_karyawans.id as data_karyawan',
                    'kategori_tagihan_potongans.label as nama_tagihan',
                    'tagihan_potongans.besaran as besaran_tagihan'
                )
                ->where('data_karyawans.unit_kerja_id', $unitKerja->id)
                ->whereMonth('tagihan_potongans.created_at', $this->month)
                ->whereYear('tagihan_potongans.created_at', $this->year)
                ->get()
                ->groupBy('data_karyawan');

            foreach ($penggajianData as $karyawanId => $details) {
                $firstDetail = $details->first();

                $data = [
                    'no' => $counter++,
                    'nama_karyawan' => $firstDetail->nama_karyawan,
                    'nik' => $firstDetail->nik,
                    'gaji_bruto' => $firstDetail->gaji_bruto ?? 0,
                    'pph_21' => $firstDetail->pph_21 ?? 0,
                    'total_premi' => $firstDetail->total_premi ?? 0,
                    'total_penyesuaian' => $penyesuaianGajiData[$karyawanId]->total_penyesuaian ?? 0,
                    'take_home_pay' => $firstDetail->take_home_pay ?? 0,
                ];

                if (isset($pengurangGajiData[$karyawanId])) {
                    foreach ($pengurangGajiData[$karyawanId] as $pengurang) {
                        $data[$pengurang->nama_premi] = $pengurang->besaran_premi;
                    }
                }

                if (isset($tagihanPotonganData[$karyawanId])) {
                    foreach ($tagihanPotonganData[$karyawanId] as $tagihan) {
                        $data[$tagihan->nama_tagihan] = $tagihan->besaran_tagihan;
                    }
                }

                $exportData->push($data);
            }
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

        $tagihanHeadings = DB::table('kategori_tagihan_potongans')
            ->distinct()
            ->pluck('label')
            ->map(function ($item) {
                return 'tagihan_' . str_replace(' ', '_', strtolower($item));
            })
            ->toArray();

        $heading = [
            ["Periode: {$this->periode_sekarang}"],
            array_merge(
                [
                    'no',
                    'nama_karyawan',
                    'nik',
                    'gaji_bruto',
                    'pph_21',
                    'total_premi',
                    'total_penyesuaian_gaji',
                    'take_home_pay',
                ],
                $pengurangHeadings,
                $tagihanHeadings
            )
        ];

        return $heading;
    }

    public function map($row): array
    {
        $mappedRow = [
            $row['no'],
            $row['nama_karyawan'],
            $row['nik'],
            $row['gaji_bruto'],
            $row['pph_21'],
            $row['total_premi'],
            $row['total_penyesuaian'],
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

        $tagihanHeadings = DB::table('kategori_tagihan_potongans')
            ->distinct()
            ->pluck('label')
            ->toArray();
        foreach ($tagihanHeadings as $heading) {
            $mappedRow[] = $row[$heading] ?? 0;
        }

        return $mappedRow;
    }

    public function title(): string
    {
        return 'Rekap Semua Potongan Unit Kerja' . ' - ' . $this->periode_sekarang;
    }
}
