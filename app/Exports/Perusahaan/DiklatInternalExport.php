<?php

namespace App\Exports\Perusahaan;

use Carbon\Carbon;
use App\Models\Diklat;
use App\Helpers\RandomHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DiklatInternalExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private static $number = 0;

    public function collection()
    {
        return Diklat::with(['kategori_diklats', 'status_diklats', 'peserta_diklat.users'])->where('kategori_diklat_id', 1)->orderBy('created_at', 'desc')->get();
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
            'peserta_diklat',
            'created_at',
            'updated_at',
        ];
    }

    public function map($diklat): array
    {
        self::$number++;

        $pesertaDiklat = $diklat->peserta_diklat->map(function ($peserta) {
            return $peserta->users->nama ?? 'N/A';
        })->join(', ');

        return [
            self::$number,
            $diklat->nama,
            $diklat->kategori_diklats->label,
            $diklat->status_diklats->label,
            $diklat->deskripsi,
            $diklat->kuota . ' Peserta',
            $diklat->tgl_mulai,
            $diklat->tgl_selesai,
            $diklat->jam_mulai,
            $diklat->jam_selesai,
            $this->formatDuration($diklat->durasi),
            $diklat->lokasi,
            $pesertaDiklat,
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
