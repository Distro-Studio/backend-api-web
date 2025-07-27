<?php

namespace App\Exports\Karyawan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use App\Models\PesertaDiklat;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class KaryawanMedisExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private $filters;
    private static $number = 0;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $karyawan = DataKaryawan::with([
            'users',
            'users.roles',
            'status_karyawans',
            'kompetensis',
        ])->where('id', '!=', 1)
            ->whereHas('kompetensis', function ($query) {
                $query->where('jenis_kompetensi', 1);
            })
            ->whereHas('users', function ($query) {
                $query->where('status_aktif', 2);
            })
            ->orderBy('nik', 'asc');

        if (isset($this->filters['masa_sip']) || isset($this->filters['masa_str'])) {
            $karyawan->where(function ($query) {
                if (isset($this->filters['masa_sip']) && is_numeric($this->filters['masa_sip'])) {
                    $targetDateSip = now('Asia/Jakarta')->addMonths((int) $this->filters['masa_sip'])->startOfDay();
                    $query->orWhereRaw("STR_TO_DATE(masa_berlaku_sip, '%d-%m-%Y') <= ?", [$targetDateSip->format('Y-m-d')]);
                }

                if (isset($this->filters['masa_str']) && is_numeric($this->filters['masa_str'])) {
                    $targetDateStr = now('Asia/Jakarta')->addMonths((int) $this->filters['masa_str'])->startOfDay();
                    $query->orWhereRaw("STR_TO_DATE(masa_berlaku_str, '%d-%m-%Y') <= ?", [$targetDateStr->format('Y-m-d')]);
                }
            });
        }

        return $karyawan->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nik',
            'nama',
            // 'username',
            // 'status_aktif',
            // 'role',
            // 'email',
            'nik_ktp',
            'status_karyawan',
            // 'tempat_lahir',
            // 'tgl_lahir',
            // 'no_kk',
            // 'alamat',
            'gelar_depan',
            'gelar_belakang',
            'no_hp',
            // 'masa_kerja',
            // 'jenis_kelamin',
            'no_str',
            'tgl_dibuat_str',
            'tgl_kadaluarsa_str',
            'no_sip',
            'tgl_dibuat_sip',
            'tgl_kadaluarsa_sip',
            'kompetensi_profesi',
            'created_at',
            'updated_at',
        ];
    }

    public function map($karyawan): array
    {
        self::$number++;

        // $roles = $karyawan->users->roles->map(function ($role) {
        //     return $role->name;
        // })->toArray();

        $formatDate = fn($date) => $date ? Carbon::parse($date)->format('d-m-Y') : 'N/A';

        // $masaKerja = $this->calculateMasaKerja($karyawan->tgl_masuk, $karyawan->tgl_keluar);

        return [
            self::$number,
            $karyawan->nik,
            $karyawan->users->nama,
            // $karyawan->users->username,
            // $karyawan->users->user_status_aktif->label,
            // implode(', ', $roles),
            // $karyawan->email ?? 'N/A',
            $karyawan->nik_ktp ?? 'N/A',
            optional($karyawan->status_karyawans)->label,
            // $karyawan->tempat_lahir ?? 'N/A',
            // $formatDate($karyawan->tgl_lahir),
            // $karyawan->no_kk ?? 'N/A',
            // $karyawan->alamat ?? 'N/A',
            $karyawan->gelar_depan ?? 'N/A',
            $karyawan->gelar_belakang ?? 'N/A',
            $karyawan->no_hp ?? 'N/A',
            // $masaKerja,
            // $karyawan->jenis_kelamin ? 'Laki-laki' : 'Perempuan',
            $karyawan->no_str ?? 'N/A',
            $formatDate($karyawan->created_str),
            $formatDate($karyawan->masa_berlaku_str),
            $karyawan->no_sip ?? 'N/A',
            $formatDate($karyawan->created_sip),
            $formatDate($karyawan->masa_berlaku_sip),
            $karyawan->kompetensis->nama_kompetensi ?? 'N/A',
            Carbon::parse($karyawan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($karyawan->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d jam %d menit", $hours, $minutes);
    }

    private function calculateMasaKerja($tgl_masuk, $tgl_keluar = null)
    {
        $start = Carbon::parse($tgl_masuk);
        $end = $tgl_keluar ? Carbon::parse($tgl_keluar) : Carbon::now('Asia/Jakarta');

        $difference = $start->diff($end);

        return sprintf('%d Tahun %d Bulan %d Hari', $difference->y, $difference->m, $difference->d);
    }
}
