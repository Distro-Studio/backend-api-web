<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\User;
use App\Models\NonShift;
use App\Models\HariLibur;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

// class JadwalNonShiftExport implements FromCollection, WithHeadings, WithTitle
// {
//     protected $start_date;
//     protected $end_date;
//     protected $counter;

//     public function __construct($startDate, $endDate)
//     {
//         // $this->start_date = Carbon::now('Asia/Jakarta')->startOfMonth();
//         // $this->end_date = Carbon::now('Asia/Jakarta')->endOfMonth();
//         $this->start_date = $startDate;
//         $this->end_date = $endDate;
//         $this->counter = 1;
//     }

//     public function collection()
//     {
//         $date_range = $this->generateDateRange($this->start_date, $this->end_date);

//         $users = User::with(['data_karyawans.unit_kerjas'])->where('nama', '!=', 'Super Admin')
//             ->whereHas('data_karyawans.unit_kerjas', function ($query) {
//                 $query->where('jenis_karyawan', 0);
//             })
//             ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
//             ->orderBy('data_karyawans.nik', 'asc') // Urutkan berdasarkan NIK
//             ->get();

//         $hariLibur = HariLibur::whereIn('tanggal', $date_range)->get()->keyBy('tanggal');
//         $nonShifts = NonShift::all()->keyBy('nama');

//         $schedules = $users->flatMap(function ($user) use ($date_range, $nonShifts, $hariLibur) {
//             $user_schedule_array = [];
//             $cutis = $user->cutis->filter(function ($cuti) {
//                 return $cuti->status_cuti_id == 4;
//             });
//             $cuti_dates = [];
//             foreach ($cutis as $cuti) {
//                 $from = Carbon::createFromFormat('d-m-Y', $cuti->tgl_from);
//                 $to = Carbon::createFromFormat('d-m-Y', $cuti->tgl_to);
//                 while ($from->lte($to)) {
//                     $cuti_dates[$from->format('Y-m-d')] = $cuti->tipe_cutis->nama ?? 'Cuti';
//                     $from->addDay();
//                 }
//             }

//             // Pastikan user memiliki data_karyawans dan unit_kerjas
//             if ($user->data_karyawans && $user->data_karyawans->unit_kerjas) {

//                 // Cek jika jenis_karyawan adalah non-shift
//                 if ($user->data_karyawans->unit_kerjas->jenis_karyawan == 0) {
//                     foreach ($date_range as $date) {
//                         $day_of_week = Carbon::createFromFormat('Y-m-d', $date)->locale('id')->dayName;
//                         $nonShift = $nonShifts->get($day_of_week);

//                         if (isset($cuti_dates[$date])) {
//                             $user_schedule_array[] = [
//                                 $this->counter++,
//                                 'user' => $user->nama,
//                                 'nik' => $user->data_karyawans->nik,
//                                 'jenis_karyawan' => 'Non-Shift',
//                                 'nama_unit' => $user->data_karyawans->unit_kerjas->nama_unit ?? 'N/A',
//                                 'nama_shift' => $cuti_dates[$date],
//                                 'tanggal_mulai' => Carbon::parse($date)->format('d-m-Y'),
//                                 'tanggal_selesai' => Carbon::parse($date)->format('d-m-Y'),
//                                 'jam_mulai' => 'N/A',
//                                 'jam_selesai' => 'N/A'
//                             ];
//                         } else if ($day_of_week == 'Minggu') {
//                             $user_schedule_array[] = [
//                                 $this->counter++,
//                                 'user' => $user->nama,
//                                 'nik' => $user->data_karyawans->nik,
//                                 'jenis_karyawan' => 'Non-Shift',
//                                 'nama_unit' => $user->data_karyawans->unit_kerjas->nama_unit ?? 'N/A',
//                                 'nama_shift' => 'Libur Hari Minggu',
//                                 'tanggal_mulai' => Carbon::parse($date)->format('d-m-Y'),
//                                 'tanggal_selesai' => Carbon::parse($date)->format('d-m-Y'),
//                                 'jam_mulai' => 'N/A',
//                                 'jam_selesai' => 'N/A'
//                             ];
//                         } elseif (isset($hariLibur[$date])) {
//                             $user_schedule_array[] = [
//                                 $this->counter++,
//                                 'user' => $user->nama,
//                                 'nik' => $user->data_karyawans->nik,
//                                 'jenis_karyawan' => 'Non-Shift',
//                                 'nama_unit' => $user->data_karyawans->unit_kerjas->nama_unit ?? 'N/A',
//                                 'nama_shift' => $hariLibur[$date]->nama,
//                                 'tanggal_mulai' => Carbon::parse($date)->format('d-m-Y'),
//                                 'tanggal_selesai' => Carbon::parse($date)->format('d-m-Y'),
//                                 'jam_mulai' => 'N/A',
//                                 'jam_selesai' => 'N/A'
//                             ];
//                         } elseif ($nonShift) {
//                             $user_schedule_array[] = [
//                                 $this->counter++,
//                                 'user' => $user->nama,
//                                 'nik' => $user->data_karyawans->nik,
//                                 'jenis_karyawan' => 'Non-Shift',
//                                 'nama_unit' => $user->data_karyawans->unit_kerjas->nama_unit ?? 'N/A',
//                                 'nama_shift' => $nonShift->nama,
//                                 'tanggal_mulai' => Carbon::parse($date)->format('d-m-Y'),
//                                 'tanggal_selesai' => Carbon::parse($date)->format('d-m-Y'),
//                                 'jam_mulai' => $nonShift->jam_from,
//                                 'jam_selesai' => $nonShift->jam_to
//                             ];
//                         }
//                     }
//                 }
//             }

//             return $user_schedule_array;
//         });

//         return new Collection($schedules);
//     }

//     public function headings(): array
//     {
//         return [
//             'no',
//             'nama',
//             'nik',
//             'jenis_karyawan',
//             'unit_kerja',
//             'shift',
//             'tanggal_mulai',
//             'tanggal_selesai',
//             'jam_mulai',
//             'jam_selesai'
//         ];
//     }

//     public function title(): string
//     {
//         return $this->start_date->locale('id')->isoFormat('MMMM Y'); // Contoh: 'Agustus 2024'
//     }

//     private function generateDateRange($start_date, $end_date)
//     {
//         $dates = [];
//         for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
//             $dates[] = $date->format('Y-m-d');
//         }
//         return $dates;
//     }
// }

class JadwalNonShiftExport implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $filters;
    protected $counter;

    public function __construct($startDate, $endDate, $filters = [])
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filters = $filters;
        $this->counter = 1;
    }

    public static function buildUserQuery(array $filters = [])
    {
        $query = User::query()
            ->with([
                'data_karyawans',
                'data_karyawans.unit_kerjas',
                'data_karyawans.jabatans',
                'data_karyawans.status_karyawans',
                'data_karyawans.kategori_agamas',
                'data_karyawans.kategori_pendidikans',
                'data_karyawans.kompetensis',
                'cutis.tipe_cutis',
            ])
            ->where('nama', '!=', 'Super Admin')
            ->whereHas('data_karyawans.unit_kerjas', function ($query) {
                $query->where('jenis_karyawan', 0);
            });

        self::applyEmployeeFilters($query, $filters);

        return $query
            ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
            ->orderBy('data_karyawans.nik', 'asc')
            ->select('users.*');
    }

    public function collection()
    {
        $dateRange = $this->generateDateRange($this->startDate, $this->endDate);

        $users = self::buildUserQuery($this->filters)->get();

        if ($users->isEmpty()) {
            return new Collection();
        }

        $hariLibur = HariLibur::whereIn('tanggal', $dateRange)->get()->keyBy('tanggal');
        $nonShifts = NonShift::all()->keyBy('nama');

        $schedules = collect();

        foreach ($users as $user) {
            $userSchedules = $this->buildUserScheduleRows($user, $dateRange, $nonShifts, $hariLibur);
            $schedules = $schedules->merge($userSchedules);
        }

        return new Collection($schedules->values()->all());
    }

    private function buildUserScheduleRows($user, array $dateRange, $nonShifts, $hariLibur): array
    {
        $rows = [];

        if (! $user->data_karyawans || ! $user->data_karyawans->unit_kerjas) {
            return $rows;
        }

        $cutiDates = $this->extractApprovedCutiDates($user);

        foreach ($dateRange as $date) {
            $row = $this->buildDailyScheduleRow($user, $date, $cutiDates, $nonShifts, $hariLibur);

            if (! empty($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function buildDailyScheduleRow($user, string $date, array $cutiDates, $nonShifts, $hariLibur): ?array
    {
        $dayOfWeek = Carbon::createFromFormat('Y-m-d', $date, 'Asia/Jakarta')
            ->locale('id')
            ->dayName;

        $nonShift = $nonShifts->get($dayOfWeek);

        $namaShift = null;
        $jamMulai = 'N/A';
        $jamSelesai = 'N/A';

        if (isset($cutiDates[$date])) {
            $namaShift = $cutiDates[$date];
        } elseif ($dayOfWeek === 'Minggu') {
            $namaShift = 'Libur Hari Minggu';
        } elseif (isset($hariLibur[$date])) {
            $namaShift = $hariLibur[$date]->nama;
        } elseif ($nonShift) {
            $namaShift = $nonShift->nama;
            $jamMulai = $nonShift->jam_from ?? 'N/A';
            $jamSelesai = $nonShift->jam_to ?? 'N/A';
        }

        if (empty($namaShift)) {
            return null;
        }

        return [
            $this->counter++,
            $user->nama,
            optional($user->data_karyawans)->nik ?? 'N/A',
            'Non-Shift',
            optional(optional($user->data_karyawans)->unit_kerjas)->nama_unit ?? 'N/A',
            $namaShift,
            Carbon::parse($date)->format('d-m-Y'),
            Carbon::parse($date)->format('d-m-Y'),
            $jamMulai,
            $jamSelesai,
        ];
    }

    private function extractApprovedCutiDates($user): array
    {
        $cutiDates = [];

        $cutis = $user->cutis->filter(function ($cuti) {
            return (int) $cuti->status_cuti_id === 4;
        });

        foreach ($cutis as $cuti) {
            try {
                $from = Carbon::createFromFormat('d-m-Y', $cuti->tgl_from, 'Asia/Jakarta')->startOfDay();
                $to = Carbon::createFromFormat('d-m-Y', $cuti->tgl_to, 'Asia/Jakarta')->startOfDay();

                while ($from->lte($to)) {
                    $cutiDates[$from->format('Y-m-d')] = optional($cuti->tipe_cutis)->nama ?? 'Cuti';
                    $from->addDay();
                }
            } catch (\Throwable $e) {
                // skip data cuti invalid
            }
        }

        return $cutiDates;
    }

    private static function applyEmployeeFilters($query, array $filters): void
    {
        self::applyUserFilter($query, $filters);
        self::applyUnitKerjaFilter($query, $filters);
        self::applyJabatanFilter($query, $filters);
        self::applyStatusKaryawanFilter($query, $filters);
        self::applyStatusAktifFilter($query, $filters);
        self::applyJenisKelaminFilter($query, $filters);
        self::applyAgamaFilter($query, $filters);
        self::applyPendidikanTerakhirFilter($query, $filters);
        self::applyJenisKompetensiFilter($query, $filters);
        self::applyTanggalMasukFilter($query, $filters);
        self::applyMasaKerjaFilter($query, $filters);
    }

    private static function applyUserFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'user_id')) {
            return;
        }

        $userId = $filters['user_id'];

        if (is_array($userId)) {
            $query->whereIn('users.id', $userId);
        } else {
            $query->where('users.id', $userId);
        }
    }

    private static function applyUnitKerjaFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'unit_kerja')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'data_karyawans.unit_kerjas',
            'id',
            $filters['unit_kerja']
        );
    }

    private static function applyJabatanFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'jabatan')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'data_karyawans.jabatans',
            'id',
            $filters['jabatan']
        );
    }

    private static function applyStatusKaryawanFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'status_karyawan')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'data_karyawans.status_karyawans',
            'id',
            $filters['status_karyawan']
        );
    }

    private static function applyStatusAktifFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'status_aktif')) {
            return;
        }

        if (is_array($filters['status_aktif'])) {
            $query->whereIn('status_aktif', $filters['status_aktif']);
        } else {
            $query->where('status_aktif', $filters['status_aktif']);
        }
    }

    private static function applyJenisKelaminFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'jenis_kelamin')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'data_karyawans',
            'jenis_kelamin',
            $filters['jenis_kelamin']
        );
    }

    private static function applyAgamaFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'agama')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'data_karyawans.kategori_agamas',
            'id',
            $filters['agama']
        );
    }

    private static function applyPendidikanTerakhirFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'pendidikan_terakhir')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'data_karyawans.kategori_pendidikans',
            'id',
            $filters['pendidikan_terakhir']
        );
    }

    private static function applyJenisKompetensiFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'jenis_kompetensi')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'data_karyawans.kompetensis',
            'jenis_kompetensi',
            $filters['jenis_kompetensi']
        );
    }

    private static function applyTanggalMasukFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'tgl_masuk')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'data_karyawans',
            'tgl_masuk',
            $filters['tgl_masuk']
        );
    }

    private static function applyMasaKerjaFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'masa_kerja')) {
            return;
        }

        $masaKerja = $filters['masa_kerja'];
        $currentDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $query->whereHas('data_karyawans', function ($query) use ($masaKerja, $currentDate) {
            if (is_array($masaKerja)) {
                $query->where(function ($subQuery) use ($masaKerja, $currentDate) {
                    foreach ($masaKerja as $masa) {
                        $bulan = (int) $masa * 12;
                        $subQuery->orWhereRaw(
                            "TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?",
                            [$currentDate, $bulan]
                        );
                    }
                });
            } else {
                $bulan = (int) $masaKerja * 12;
                $query->whereRaw(
                    "TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?",
                    [$currentDate, $bulan]
                );
            }
        });
    }

    private static function applyWhereHasInOrEqual($query, string $relation, string $column, $value): void
    {
        $query->whereHas($relation, function ($query) use ($column, $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        });
    }

    private static function hasFilter(array $filters, string $key): bool
    {
        if (! array_key_exists($key, $filters)) {
            return false;
        }

        $value = $filters[$key];

        if (is_array($value)) {
            $filtered = array_filter($value, function ($item) {
                return $item !== null && $item !== '';
            });

            return count($filtered) > 0;
        }

        return $value !== null && $value !== '';
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
        ];
    }

    public function title(): string
    {
        return 'Jadwal Non Shift';
    }

    private function generateDateRange($startDate, $endDate): array
    {
        $dates = [];

        for ($date = $startDate->copy()->startOfDay(); $date->lte($endDate->copy()->startOfDay()); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }
}
