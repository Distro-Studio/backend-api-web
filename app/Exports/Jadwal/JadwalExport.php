<?php

namespace App\Exports\Jadwal;

use App\Helpers\RandomHelper;
use Carbon\Carbon;
use App\Models\Shift;
use App\Models\Jadwal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class JadwalExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function collection()
    {
        $jadwals = Jadwal::with(['users', 'shifts', 'users.data_karyawans.unit_kerjas'])
            ->get();

        $schedules = $jadwals->map(function ($schedule, $index) {
            $convertTanggal_Mulai = RandomHelper::convertToDateString($schedule->tgl_mulai);
            $convertTanggal_Selesai = RandomHelper::convertToDateString($schedule->tgl_selesai);
            $tgl_mulai = Carbon::parse($convertTanggal_Mulai)->format('d-m-Y');
            $tgl_selesai = Carbon::parse($convertTanggal_Selesai)->format('d-m-Y');

            $unitKerjas = $schedule->users->data_karyawans->unit_kerjas ?? null;
            $jenisKaryawan = $unitKerjas ? ($unitKerjas->jenis_karyawan ? 'Shift' : 'Non-Shift') : 'N/A'; // 1 = Shift, 0 = Non-Shift
            $namaUnit = $unitKerjas ? $unitKerjas->nama_unit : 'N/A';

            return [
                'no' => $index + 1,
                'user' => $schedule->users->nama,
                'jenis_karyawan' => $jenisKaryawan,
                'nama_unit' => $namaUnit,
                'nama_shift' => $schedule->shifts->nama,
                $tgl_mulai,
                $tgl_selesai,
                'jam_from' => $schedule->shifts->jam_from,
                'jam_to' => $schedule->shifts->jam_to,
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
