<?php

namespace App\Exports\Karyawan;

use Carbon\Carbon;
use App\Models\TrackRecord;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RekamJejakExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return TrackRecord::with('users')->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'tgl_masuk',
            'tgl_keluar',
            'masa_kerja',
            'promosi',
            'mutasi',
            'penghargaan',
            'created_at',
            'updated_at',
        ];
    }

    public function map($rekam_jejak): array
    {
        if ($rekam_jejak->tgl_masuk) {
            $tglMasuk = Carbon::parse($rekam_jejak->tgl_masuk);
            $tglSekarang = Carbon::now();

            if ($rekam_jejak->tgl_keluar) {
                // jika ada tgl_keluar
                $tglKeluar = Carbon::parse($rekam_jejak->tgl_keluar);
            } else {
                $tglKeluar = $tglSekarang;
            }

            $totalDays = $tglMasuk->diffInDays($tglKeluar);
            $years = floor($totalDays / 365);
            $months = floor(($totalDays % 365) / 30);
            $masaKerja = "{$years} Tahun {$months} Bulan";
        } else {
            $masaKerja = 'N/A';
        }

        return [
            $rekam_jejak->users->nama,
            $rekam_jejak->tgl_masuk,
            $rekam_jejak->tgl_keluar ?? 'N/A',
            $masaKerja,
            $rekam_jejak->promosi ?? 'N/A',
            $rekam_jejak->mutasi ?? 'N/A',
            $rekam_jejak->penghargaan ?? 'N/A',
            Carbon::parse($rekam_jejak->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($rekam_jejak->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
