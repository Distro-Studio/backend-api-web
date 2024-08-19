<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\Jadwal;
use App\Helpers\RandomHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class JadwalShiftExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function collection()
    {
        $jadwals = Jadwal::with(['users', 'shifts', 'users.data_karyawans.unit_kerjas'])
            ->get();

        $schedules = $jadwals->map(function ($schedule, $index) {
            // Jika shift_id = 0, karyawan libur
            if ($schedule->shift_id == 0) {
                $namaShift = 'Libur';
                $jam_from = 'N/A';
                $jam_to = 'N/A';
            } else {
                $namaShift = $schedule->shifts->nama ?? 'N/A';
                $convertJam_From = RandomHelper::convertToTimeString($schedule->shifts->jam_from);
                $convertJam_To = RandomHelper::convertToTimeString($schedule->shifts->jam_to);
                $jam_from = $convertJam_From ? Carbon::parse($convertJam_From)->format('H:i:s') : 'N/A';
                $jam_to = $convertJam_To ? Carbon::parse($convertJam_To)->format('H:i:s') : 'N/A';
            }

            $convertTanggal_Mulai = RandomHelper::convertToDateString($schedule->tgl_mulai);
            $convertTanggal_Selesai = RandomHelper::convertToDateString($schedule->tgl_selesai);
            $tgl_mulai = $convertTanggal_Mulai ? Carbon::parse($convertTanggal_Mulai)->format('d-m-Y') : 'N/A';
            $tgl_selesai = $convertTanggal_Selesai ? Carbon::parse($convertTanggal_Selesai)->format('d-m-Y') : 'N/A';

            $unitKerjas = $schedule->users->data_karyawans->unit_kerjas ?? 'N/A';
            $jenisKaryawan = $unitKerjas ? ($unitKerjas->jenis_karyawan ? 'Shift' : 'Non-Shift') : 'N/A';
            $namaUnit = $unitKerjas ? $unitKerjas->nama_unit : 'N/A';

            return [
                'no' => $index + 1,
                'user' => $schedule->users->nama,
                'jenis_karyawan' => $jenisKaryawan,
                'nama_unit' => $namaUnit,
                'nama_shift' => $namaShift,
                $tgl_mulai,
                $tgl_selesai,
                $jam_from,
                $jam_to,
                'created_at' => Carbon::parse($schedule->created_at)->format('d-m-Y H:i:s'),
                'updated_at' => Carbon::parse($schedule->updated_at)->format('d-m-Y H:i:s'),
            ];
        });

        return new Collection($schedules);
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'jenis_karyawan',
            'unit_kerja',
            'shift',
            'tanggal_mulai',
            'tanggal_selesai',
            'jam_mulai',
            'jam_selesai',
            'created_at',
            'updated_at',
        ];
    }
}
