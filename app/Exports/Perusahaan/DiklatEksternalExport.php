<?php

namespace App\Exports\Perusahaan;

use Carbon\Carbon;
use App\Models\Diklat;
use App\Helpers\RandomHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DiklatEksternalExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private static $number = 0;

    public function collection()
    {
        return Diklat::with(['kategori_diklats', 'status_diklats', 'peserta_diklat.users'])->where('kategori_diklat_id', 2)->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama_diklat',
            'kategori_diklat',
            'status_diklat',
            'deskripsi',
            'kuota',
            'tgl_mulai',
            'tgl_selesai',
            'jam_mulai',
            'jam_selesai',
            'durasi',
            'lokasi',
            'created_at',
            'updated_at',
        ];
    }

    public function map($diklat): array
    {
        self::$number++;
        $convertTanggal_Mulai = RandomHelper::convertToDateString($diklat->tgl_mulai);
        $convertTanggal_Selesai = RandomHelper::convertToDateString($diklat->tgl_selesai);
        $convertJam_Mulai = RandomHelper::convertToTimeString($diklat->jam_mulai);
        $convertJam_Selesai = RandomHelper::convertToTimeString($diklat->jam_selesai);
        $tgl_mulai = Carbon::parse($convertTanggal_Mulai)->format('d-m-Y');
        $tgl_selesai = Carbon::parse($convertTanggal_Selesai)->format('d-m-Y');
        $jam_mulai = Carbon::parse($convertJam_Mulai)->format('H:i:s');
        $jam_selesai = Carbon::parse($convertJam_Selesai)->format('H:i:s');

        return [
            self::$number,
            $diklat->nama,
            $diklat->kategori_diklats->label,
            $diklat->status_diklats->label,
            $diklat->deskripsi,
            $diklat->kuota . ' Peserta',
            $tgl_mulai,
            $tgl_selesai,
            $jam_mulai,
            $jam_selesai,
            $this->formatDuration($diklat->durasi),
            $diklat->lokasi,
            Carbon::parse($diklat->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($diklat->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d jam %d menit", $hours, $minutes);
    }
}
