<?php

namespace App\Exports\Sheet;

use Carbon\Carbon;
use App\Models\NonShift;
use App\Models\Presensi;
use App\Helpers\RandomHelper;
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
    private $startDate;
    private $endDate;

    public function __construct($filter, $title, $startDate, $endDate)
    {
        $this->filter = $filter;
        $this->title = $title;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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

        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('jam_masuk', [$this->startDate, $this->endDate]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'nik',
            'nama_shift',
            'shift_masuk',
            'shift_keluar',
            'jadwal_mulai',
            'jadwal_selesai',
            'jam_masuk_nShift',
            'jam_selesai_nShift',
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
        $shift = optional($presensi->jadwals)->shifts;
        $unitKerja = optional(optional($presensi->data_karyawans)->unit_kerjas)->nama_unit;

        // Non-Shifts
        $jamMasukDate = Carbon::parse($presensi->jam_masuk)->locale('id');
        $hari = $jamMasukDate->isoFormat('dddd');
        $nonShift = NonShift::where('nama', $hari)->first();

        if ($shift) {
            $jamMasukNonShift = 'N/A';
            $jamKeluarNonShift = 'N/A';
        } else {
            $nonShift = NonShift::where('nama', $hari)->first();
            $jamMasukNonShift = $nonShift ? $nonShift->jam_from : 'N/A';
            $jamKeluarNonShift = $nonShift ? $nonShift->jam_to : 'N/A';
        }
        return [
            $this->number,
            optional($presensi->users)->nama,
            optional($presensi->users->data_karyawans)->nik,
            $shift ? $shift->nama : 'N/A',
            $shift && isset($shift->jam_from) ? $shift->jam_from : 'N/A',
            $shift && isset($shift->jam_to) ? $shift->jam_to : 'N/A',
            optional($presensi->jadwals)->tgl_mulai ? RandomHelper::convertToDateString($presensi->jadwals->tgl_mulai) : 'N/A',
            optional($presensi->jadwals)->tgl_selesai ? RandomHelper::convertToDateString($presensi->jadwals->tgl_selesai) : 'N/A',
            $jamMasukNonShift,
            $jamKeluarNonShift,
            $unitKerja,
            $presensi->jam_masuk ? RandomHelper::convertToDateTimeString($presensi->jam_masuk) : 'N/A',
            $presensi->jam_keluar ? RandomHelper::convertToDateTimeString($presensi->jam_keluar) : 'N/A',
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
