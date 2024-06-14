<?php

namespace App\Exports\Jadwal;

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

        $schedules = $jadwals->map(function ($schedule) {
            $unitKerjas = $schedule->users->data_karyawans->unit_kerjas ?? null;
            $jenisKaryawan = $unitKerjas ? ($unitKerjas->jenis_karyawan ? 'Shift' : 'Non-Shift') : 'N/A'; // 1 = Shift, 0 = Non-Shift
            $namaUnit = $unitKerjas ? $unitKerjas->nama_unit : 'N/A';

            return [
                'user' => $schedule->users->nama,
                'jenis_karyawan' => $jenisKaryawan,
                'nama_unit' => $namaUnit,
                'nama_shift' => $schedule->shifts->nama,
                'tanggal_mulai' => $schedule->tanggal_mulai,
                'tanggal_selesai' => $schedule->tanggal_selesai,
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
            'Nama Karyawan',
            'Jenis Karyawan',
            'Unit Kerja',
            'Nama Shift',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Jam From',
            'Jam To',
            'created_at',
            'updated_at',
        ];
    }
}
