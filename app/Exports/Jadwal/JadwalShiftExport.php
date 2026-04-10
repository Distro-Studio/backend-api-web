<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\Jadwal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

// class JadwalShiftExport implements FromCollection, WithHeadings
// {
//     use Exportable;

//     private $startDate;
//     private $endDate;

//     public function __construct($startDate, $endDate)
//     {
//         $this->startDate = $startDate;
//         $this->endDate = $endDate;
//     }

//     public function collection()
//     {
//         $jadwals = Jadwal::with(['users', 'shifts', 'users.data_karyawans.unit_kerjas'])
//             ->whereHas('users.data_karyawans.unit_kerjas', function ($query) {
//                 $query->where('jenis_karyawan', 1);
//             })
//             ->whereBetween('tgl_mulai', [$this->startDate, $this->endDate])
//             ->orWhereBetween('tgl_selesai', [$this->startDate, $this->endDate])
//             ->join('data_karyawans', 'jadwals.user_id', '=', 'data_karyawans.user_id') // Join dengan data_karyawans
//             ->orderBy('data_karyawans.nik', 'asc') // Mengurutkan berdasarkan nik
//             ->get();

//         $schedules = $jadwals->map(function ($schedule, $index) {
//             // Jika shift_id = 0, karyawan libur
//             if ($schedule->shift_id == 0) {
//                 $namaShift = 'Libur';
//                 $jam_from = 'N/A';
//                 $jam_to = 'N/A';
//             } else {
//                 $namaShift = $schedule->shifts->nama ?? 'N/A';
//                 $jam_from = $schedule->shifts->jam_from
//                     ? Carbon::parse($schedule->shifts->jam_from)->format('H:i:s')
//                     : 'N/A';
//                 $jam_to = $schedule->shifts->jam_to
//                     ? Carbon::parse($schedule->shifts->jam_to)->format('H:i:s')
//                     : 'N/A';
//             }

//             $tgl_mulai = $schedule->tgl_mulai
//                 ? Carbon::parse($schedule->tgl_mulai)->format('d-m-Y')
//                 : 'N/A';
//             $tgl_selesai = $schedule->tgl_selesai
//                 ? Carbon::parse($schedule->tgl_selesai)->format('d-m-Y')
//                 : 'N/A';

//             $unitKerjas = $schedule->users->data_karyawans->unit_kerjas ?? 'N/A';
//             $jenisKaryawan = $unitKerjas ? ($unitKerjas->jenis_karyawan ? 'Shift' : 'Non-Shift') : 'N/A';
//             $namaUnit = $unitKerjas ? $unitKerjas->nama_unit : 'N/A';

//             return [
//                 'no' => $index + 1,
//                 'user' => $schedule->users->nama,
//                 'nik' => $schedule->users->data_karyawans->nik,
//                 'jenis_karyawan' => $jenisKaryawan,
//                 'nama_unit' => $namaUnit,
//                 'nama_shift' => $namaShift,
//                 $tgl_mulai,
//                 $tgl_selesai,
//                 $jam_from,
//                 $jam_to,
//                 'ex_libur' => $schedule->ex_libur ? 'Ya' : 'Tidak',
//                 'created_at' => Carbon::parse($schedule->created_at)->format('d-m-Y H:i:s'),
//                 'updated_at' => Carbon::parse($schedule->updated_at)->format('d-m-Y H:i:s'),
//             ];
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
//             'jam_selesai',
//             'extra_libur',
//             'created_at',
//             'updated_at',
//         ];
//     }
// }

class JadwalShiftExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private string $startDate;
    private string $endDate;
    private array $filters;
    private int $number = 0;

    public function __construct(string $startDate, string $endDate, array $filters = [])
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filters = $filters;
    }

    public static function buildQuery(string $startDate, string $endDate, array $filters = [])
    {
        $query = Jadwal::query()
            ->with([
                'users',
                'users.data_karyawans',
                'users.data_karyawans.unit_kerjas',
                'users.data_karyawans.jabatans',
                'users.data_karyawans.status_karyawans',
                'shifts',
            ])
            ->whereHas('users.data_karyawans.unit_kerjas', function ($query) {
                // Karena ini export jadwal shift, default tetap hanya jenis_karyawan = 1
                $query->where('jenis_karyawan', 1);
            });

        self::applyDateRangeFilter($query, $startDate, $endDate);
        self::applyEmployeeFilters($query, $filters);

        return $query
            ->join('data_karyawans', 'jadwals.user_id', '=', 'data_karyawans.user_id')
            ->orderBy('data_karyawans.nik', 'asc')
            ->select('jadwals.*');
    }

    public function collection()
    {
        return self::buildQuery($this->startDate, $this->endDate, $this->filters)->get();
    }

    private static function applyDateRangeFilter($query, string $startDate, string $endDate): void
    {
        $query->where(function ($query) use ($startDate, $endDate) {
            // Jadwal rentang, misal tgl_mulai dan tgl_selesai ada nilainya
            $query->where(function ($subQuery) use ($startDate, $endDate) {
                $subQuery->whereNotNull('tgl_selesai')
                    ->where('tgl_selesai', '!=', '')
                    ->where('tgl_mulai', '<=', $endDate)
                    ->where('tgl_selesai', '>=', $startDate);
            })
                // Jadwal satu hari, biasanya tgl_selesai null / kosong
                ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                    $subQuery->where(function ($q) {
                        $q->whereNull('tgl_selesai')
                            ->orWhere('tgl_selesai', '');
                    })->whereBetween('tgl_mulai', [$startDate, $endDate]);
                });
        });
    }

    private static function applyEmployeeFilters($query, array $filters): void
    {
        self::applyUserFilter($query, $filters);
        self::applyUnitKerjaFilter($query, $filters);
        self::applyJabatanFilter($query, $filters);
        self::applyStatusKaryawanFilter($query, $filters);
        self::applyStatusAktifFilter($query, $filters);
        self::applyJenisKelaminFilter($query, $filters);
    }

    private static function applyUserFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'user_id')) {
            return;
        }

        $userId = $filters['user_id'];

        if (is_array($userId)) {
            $query->whereIn('jadwals.user_id', $userId);
        } else {
            $query->where('jadwals.user_id', $userId);
        }
    }

    private static function applyUnitKerjaFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'unit_kerja')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'users.data_karyawans.unit_kerjas',
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
            'users.data_karyawans.jabatans',
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
            'users.data_karyawans.status_karyawans',
            'id',
            $filters['status_karyawan']
        );
    }

    private static function applyStatusAktifFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'status_aktif')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'users',
            'status_aktif',
            $filters['status_aktif']
        );
    }

    private static function applyJenisKelaminFilter($query, array $filters): void
    {
        if (! self::hasFilter($filters, 'jenis_kelamin')) {
            return;
        }

        self::applyWhereHasInOrEqual(
            $query,
            'users.data_karyawans',
            'jenis_kelamin',
            $filters['jenis_kelamin']
        );
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
            'extra_libur',
            'created_at',
            'updated_at',
        ];
    }

    public function map($schedule): array
    {
        $this->number++;

        $user = $schedule->users;
        $dataKaryawan = optional($user)->data_karyawans;
        $unitKerja = optional($dataKaryawan)->unit_kerjas;
        $shift = $schedule->shifts;

        if ((int) $schedule->shift_id === 0) {
            $namaShift = 'Libur';
            $jamFrom = 'N/A';
            $jamTo = 'N/A';
        } else {
            $namaShift = $shift->nama ?? 'N/A';
            $jamFrom = !empty($shift?->jam_from)
                ? Carbon::parse($shift->jam_from)->format('H:i:s')
                : 'N/A';
            $jamTo = !empty($shift?->jam_to)
                ? Carbon::parse($shift->jam_to)->format('H:i:s')
                : 'N/A';
        }

        return [
            $this->number,
            optional($user)->nama ?? 'N/A',
            optional($dataKaryawan)->nik ?? 'N/A',
            'Shift',
            optional($unitKerja)->nama_unit ?? 'N/A',
            $namaShift,
            !empty($schedule->tgl_mulai) ? Carbon::parse($schedule->tgl_mulai)->format('d-m-Y') : 'N/A',
            !empty($schedule->tgl_selesai) ? Carbon::parse($schedule->tgl_selesai)->format('d-m-Y') : 'N/A',
            $jamFrom,
            $jamTo,
            $schedule->ex_libur ? 'Ya' : 'Tidak',
            !empty($schedule->created_at) ? Carbon::parse($schedule->created_at)->format('d-m-Y H:i:s') : 'N/A',
            !empty($schedule->updated_at) ? Carbon::parse($schedule->updated_at)->format('d-m-Y H:i:s') : 'N/A',
        ];
    }
}
