<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\NonShift;
use App\Models\HariLibur;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class JadwalNonShiftExport implements FromCollection, WithHeadings, WithTitle
{
    protected $start_date;
    protected $end_date;
    protected $counter;

    public function __construct()
    {
        $this->start_date = Carbon::now('Asia/Jakarta')->startOfMonth();
        $this->end_date = Carbon::now('Asia/Jakarta')->endOfMonth();
        $this->counter = 1;
    }

    public function collection()
    {
        $date_range = $this->generateDateRange($this->start_date, $this->end_date);

        $users = User::with(['data_karyawans.unit_kerjas'])->where('nama', '!=', 'Super Admin')
            ->whereHas('data_karyawans.unit_kerjas', function ($query) {
                $query->where('jenis_karyawan', 0);
            })
            ->get();

        $hariLibur = HariLibur::whereIn('tanggal', $date_range)->get()->keyBy('tanggal');
        $nonShifts = NonShift::all()->keyBy('nama');

        $schedules = $users->flatMap(function ($user) use ($date_range, $nonShifts, $hariLibur) {
            $user_schedule_array = [];
            $cutis = $user->cutis->filter(function ($cuti) {
                return $cuti->status_cuti_id == 4;
            });
            $cuti_dates = [];
            foreach ($cutis as $cuti) {
                $from = Carbon::createFromFormat('d-m-Y', $cuti->tgl_from);
                $to = Carbon::createFromFormat('d-m-Y', $cuti->tgl_to);
                while ($from->lte($to)) {
                    $cuti_dates[$from->format('Y-m-d')] = $cuti->tipe_cutis->nama ?? 'Cuti';
                    $from->addDay();
                }
            }

            if ($user->data_karyawans->unit_kerjas->jenis_karyawan == 0) {
                foreach ($date_range as $date) {
                    $day_of_week = Carbon::createFromFormat('Y-m-d', $date)->locale('id')->dayName;
                    $nonShift = $nonShifts->get($day_of_week);

                    if (isset($cuti_dates[$date])) {
                        $user_schedule_array[] = [
                            $this->counter++,
                            'user' => $user->nama,
                            'jenis_karyawan' => 'Non-Shift',
                            'nama_unit' => $user->data_karyawans->unit_kerjas->nama_unit ?? 'N/A',
                            'nama_shift' => $cuti_dates[$date],
                            'tanggal_mulai' => Carbon::parse($date)->format('d-m-Y'),
                            'tanggal_selesai' => Carbon::parse($date)->format('d-m-Y'),
                            'jam_mulai' => 'N/A',
                            'jam_selesai' => 'N/A'
                        ];
                    } else if ($day_of_week == 'Minggu') {
                        $user_schedule_array[] = [
                            $this->counter++,
                            'user' => $user->nama,
                            'jenis_karyawan' => 'Non-Shift',
                            'nama_unit' => $user->data_karyawans->unit_kerjas->nama_unit ?? 'N/A',
                            'nama_shift' => 'Libur Hari Minggu',
                            'tanggal_mulai' => Carbon::parse($date)->format('d-m-Y'),
                            'tanggal_selesai' => Carbon::parse($date)->format('d-m-Y'),
                            'jam_mulai' => 'N/A',
                            'jam_selesai' => 'N/A'
                        ];
                    } elseif (isset($hariLibur[$date])) {
                        $user_schedule_array[] = [
                            $this->counter++,
                            'user' => $user->nama,
                            'jenis_karyawan' => 'Non-Shift',
                            'nama_unit' => $user->data_karyawans->unit_kerjas->nama_unit ?? 'N/A',
                            'nama_shift' => $hariLibur[$date]->nama,
                            'tanggal_mulai' => Carbon::parse($date)->format('d-m-Y'),
                            'tanggal_selesai' => Carbon::parse($date)->format('d-m-Y'),
                            'jam_mulai' => 'N/A',
                            'jam_selesai' => 'N/A'
                        ];
                    } elseif ($nonShift) {
                        $user_schedule_array[] = [
                            $this->counter++,
                            'user' => $user->nama,
                            'jenis_karyawan' => 'Non-Shift',
                            'nama_unit' => $user->data_karyawans->unit_kerjas->nama_unit ?? 'N/A',
                            'nama_shift' => $nonShift->nama,
                            'tanggal_mulai' => Carbon::parse($date)->format('d-m-Y'),
                            'tanggal_selesai' => Carbon::parse($date)->format('d-m-Y'),
                            'jam_mulai' => $nonShift->jam_from,
                            'jam_selesai' => $nonShift->jam_to
                        ];
                    }
                }
            }

            return $user_schedule_array;
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
            'jam_selesai'
        ];
    }

    public function title(): string
    {
        return $this->start_date->locale('id')->isoFormat('MMMM Y'); // Contoh: 'Agustus 2024'
    }

    private function generateDateRange($start_date, $end_date)
    {
        $dates = [];
        for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }
}
