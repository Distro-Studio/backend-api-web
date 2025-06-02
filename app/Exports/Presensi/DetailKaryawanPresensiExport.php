<?php

namespace App\Exports\Presensi;

use Carbon\Carbon;
use App\Models\NonShift;
use App\Models\Presensi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DetailKaryawanPresensiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private $number;
    private $start_date;
    private $end_date;
    private $data_karyawan_id;

    public function __construct($start_date, $end_date, $data_karyawan_id)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->data_karyawan_id = $data_karyawan_id;
    }

    public function collection()
    {
        return Presensi::with([
            'users',
            'jadwals.shifts',
            'data_karyawans.unit_kerjas',
            'kategori_presensis'
        ])
            ->where('data_karyawan_id', $this->data_karyawan_id)
            ->whereBetween(DB::raw("DATE(jam_masuk)"), [$this->start_date, $this->end_date])
            ->get();
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
            'pembatalan_reward',
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
            optional($presensi->jadwals)->tgl_mulai ? $this->convertToDateString($presensi->jadwals->tgl_mulai) : 'N/A',
            optional($presensi->jadwals)->tgl_selesai ? $this->convertToDateString($presensi->jadwals->tgl_selesai) : 'N/A',
            $jamMasukNonShift,
            $jamKeluarNonShift,
            $unitKerja,
            $presensi->jam_masuk ? $this->convertToDateTimeString($presensi->jam_masuk) : 'N/A',
            $presensi->jam_keluar ? $this->convertToDateTimeString($presensi->jam_keluar) : 'N/A',
            $this->formatDuration($presensi->durasi),
            $presensi->lat ? $presensi->lat : 'N/A',
            $presensi->long ? $presensi->long : 'N/A',
            $presensi->latkeluar,
            $presensi->longkeluar,
            optional($presensi->kategori_presensis)->label,
            $presensi->is_pembatalan_reward ? 'Ya' : 'Tidak',
            Carbon::parse($presensi->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($presensi->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    private function convertToDateTimeString($date)
    {
        return $date ? Carbon::parse($date)->format('d-m-Y H:i:s') : null;
    }

    private function convertToDateString($date)
    {
        return $date ? Carbon::parse($date)->format('d-m-Y') : null;
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d jam %d menit", $hours, $minutes);
    }
}
