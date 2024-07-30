<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\Presensi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PresensiSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

    private $number;
    private $filter;
    private $title;
    private $months;
    private $year;

    public function __construct($filter, $title, $months, $year)
    {
        $this->filter = $filter;
        $this->title = $title;
        $this->months = $months;
        $this->year = $year;
        $this->number = 0; // Reset numbering for each sheet
    }

    public function collection()
    {
        $query = Presensi::with([
            'users',
            'jadwals.shifts',
            'data_karyawans.unit_kerjas',
            'kategori_presensis'
        ])->whereHas('kategori_presensis', function ($query) {
            $query->where('label', $this->filter);
        });

        if (!empty($this->months) && !empty($this->year)) {
            $query->whereYear('jam_masuk', $this->year)
                ->whereIn(DB::raw('MONTH(jam_masuk)'), $this->months);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'nama_shift',
            'shift_masuk',
            'shift_keluar',
            'jadwal_mulai',
            'jadwal_selesai',
            'unit_kerja',
            'presensi_masuk',
            'presensi_keluar',
            'durasi',
            'lat_masuk',
            'long_masuk',
            'lat_keluar',
            'long_keluar',
            'kategori',
            'created_at',
            'updated_at'
        ];
    }

    public function map($presensi): array
    {
        $this->number++;
        return [
            $this->number,
            optional($presensi->users)->nama,
            optional(optional($presensi->jadwals)->shifts)->nama,
            optional($presensi->jadwals)->shifts ? Carbon::parse(optional($presensi->jadwals)->shifts->jam_from)->format('d-m-Y H:i:s') : null,
            optional($presensi->jadwals)->shifts ? Carbon::parse(optional($presensi->jadwals)->shifts->jam_to)->format('d-m-Y H:i:s') : null,
            optional($presensi->jadwals)->tgl_mulai ? Carbon::parse($presensi->jadwals->tgl_mulai)->format('d-m-Y') : null,
            optional($presensi->jadwals)->tgl_selesai ? Carbon::parse($presensi->jadwals->tgl_selesai)->format('d-m-Y') : null,
            optional(optional($presensi->data_karyawans)->unit_kerjas)->nama_unit,
            $presensi->jam_masuk ? Carbon::parse($presensi->jam_masuk)->format('d-m-Y H:i:s') : null,
            $presensi->jam_keluar ? Carbon::parse($presensi->jam_keluar)->format('d-m-Y H:i:s') : null,
            $this->formatDuration($presensi->durasi),
            $presensi->lat,
            $presensi->long,
            $presensi->latkeluar,
            $presensi->longkeluar,
            optional($presensi->kategori_presensis)->label,
            Carbon::parse($presensi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($presensi->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d jam %d menit", $hours, $minutes);
    }

    public function title(): string
    {
        return $this->title;
    }
}
