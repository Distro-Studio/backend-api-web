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

    private $startDate;
    private $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $jadwals = Jadwal::with(['users', 'shifts', 'users.data_karyawans.unit_kerjas'])
            ->whereHas('users.data_karyawans.unit_kerjas', function ($query) {
                $query->where('jenis_karyawan', 1);
            })
            ->whereBetween('tgl_mulai', [$this->startDate, $this->endDate])
            ->orWhereBetween('tgl_selesai', [$this->startDate, $this->endDate])
            ->join('data_karyawans', 'jadwals.user_id', '=', 'data_karyawans.user_id') // Join dengan data_karyawans
            ->orderBy('data_karyawans.nik', 'asc') // Mengurutkan berdasarkan nik
            ->get();

        $schedules = $jadwals->map(function ($schedule, $index) {
            // Jika shift_id = 0, karyawan libur
            if ($schedule->shift_id == 0) {
                $namaShift = 'Libur';
                $jam_from = 'N/A';
                $jam_to = 'N/A';
            } else {
                $namaShift = $schedule->shifts->nama ?? 'N/A';
                $jam_from = $schedule->shifts->jam_from
                    ? Carbon::parse($schedule->shifts->jam_from)->format('H:i:s')
                    : 'N/A';
                $jam_to = $schedule->shifts->jam_to
                    ? Carbon::parse($schedule->shifts->jam_to)->format('H:i:s')
                    : 'N/A';
            }

            $tgl_mulai = $schedule->tgl_mulai
                ? Carbon::parse($schedule->tgl_mulai)->format('d-m-Y')
                : 'N/A';
            $tgl_selesai = $schedule->tgl_selesai
                ? Carbon::parse($schedule->tgl_selesai)->format('d-m-Y')
                : 'N/A';

            $unitKerjas = $schedule->users->data_karyawans->unit_kerjas ?? 'N/A';
            $jenisKaryawan = $unitKerjas ? ($unitKerjas->jenis_karyawan ? 'Shift' : 'Non-Shift') : 'N/A';
            $namaUnit = $unitKerjas ? $unitKerjas->nama_unit : 'N/A';

            return [
                'no' => $index + 1,
                'user' => $schedule->users->nama,
                'nik' => $schedule->users->data_karyawans->nik,
                'jenis_karyawan' => $jenisKaryawan,
                'nama_unit' => $namaUnit,
                'nama_shift' => $namaShift,
                $tgl_mulai,
                $tgl_selesai,
                $jam_from,
                $jam_to,
                'ex_libur' => $schedule->ex_libur ? 'Ya' : 'Tidak',
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
            'nik',
            'jenis_karyawan',
            'unit_kerja',
            'shift',
            'tanggal_mulai',
            'tanggal_selesai',
            'jam_mulai',
            'jam_selesai',
            'extra_libur',
            'created_at',
            'updated_at',
        ];
    }
}
