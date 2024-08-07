<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\Lembur;
use App\Helpers\RandomHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class LemburJadwalExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Lembur::with([
            'users',
            'jadwals',
            'status_lemburs',
            'kategori_kompensasis'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'tanggal_pengajuan',
            'tanggal_mulai',
            'tanggal_selesai',
            'shift',
            'durasi',
            'catatan',
            'kompensasi_lembur',
            'status_lembur',
            'created_at',
            'updated_at',
        ];
    }

    public function map($lembur): array
    {
        static $no = 1;

        // Konversikan durasi ke time string, kemudian ke detik, lalu ke jam dan menit
        $timeString = RandomHelper::convertToTimeString($lembur->durasi);
        $seconds = RandomHelper::convertTimeStringToSeconds($timeString);
        $duration = RandomHelper::convertToHoursMinutes($seconds);

        $convertTgl_Pengajuan = RandomHelper::convertToDateString($lembur->tgl_pengajuan);
        $convertTgl_Mulai = RandomHelper::convertToDateString($lembur->jadwals->tgl_mulai);
        $convertTgl_Selesai = RandomHelper::convertToDateString($lembur->jadwals->tgl_selesai);
        $tgl_pengajuan = Carbon::parse($convertTgl_Pengajuan)->format('d-m-Y');
        $tgl_mulai = Carbon::parse($convertTgl_Mulai)->format('d-m-Y');
        $tgl_selesai = Carbon::parse($convertTgl_Selesai)->format('d-m-Y');

        return [
            $no++,
            $lembur->users->nama,
            $tgl_pengajuan,
            $tgl_mulai,
            $tgl_selesai,
            $lembur->jadwals->shifts->nama,
            $duration, // Ubah durasi menjadi jam dan menit
            $lembur->catatan,
            $lembur->kategori_kompensasis->label,
            $lembur->status_lemburs->label,
            Carbon::parse($lembur->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($lembur->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
