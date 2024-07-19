<?php

namespace App\Exports\Keuangan;

use Carbon\Carbon;
use App\Models\PenyesuaianGaji;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PenyesuaianGajiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Return the collection of data to be exported.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return PenyesuaianGaji::with('penggajians')->get();
    }

    /**
     * Return the headings for the export file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'nama',
            'unit_kerja',
            'kelompok_gaji',
            'ptkp',
            'kategori',
            'nama_detail',
            'besaran',
            'bulan_mulai',
            'bulan_selesai',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Map the data for each row in the export file.
     *
     * @param mixed $penyesuaianGaji
     * @return array
     */
    public function map($penyesuaianGaji): array
    {
        return [
            $penyesuaianGaji->penggajians->data_karyawans->users->nama,
            $penyesuaianGaji->penggajians->data_karyawans->unit_kerjas->nama_unit,
            $penyesuaianGaji->penggajians->data_karyawans->kelompok_gajis->nama_kelompok,
            $penyesuaianGaji->penggajians->data_karyawans->ptkps->kode_ptkp,
            $penyesuaianGaji->status_description,
            $penyesuaianGaji->nama_detail,
            $penyesuaianGaji->besaran,
            Carbon::parse($penyesuaianGaji->bulan_mulai)->format('F Y'),
            Carbon::parse($penyesuaianGaji->bulan_selesai)->format('F Y'),
            Carbon::parse($penyesuaianGaji->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($penyesuaianGaji->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
