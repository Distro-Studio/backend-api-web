<?php

namespace App\Exports\Perusahaan;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use App\Models\PesertaDiklat;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class MasaDiklatExport implements FromCollection, WithHeadings, WithMapping
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
        ])->where('id', '!=', 1)
            ->orderBy('nik', 'asc');

        if (isset($this->filters['less_than'])) {
            $karyawan->where('masa_diklat', '<=', $this->filters['less_than']);
        }

        if (isset($this->filters['more_than'])) {
            $karyawan->where('masa_diklat', '>=', $this->filters['more_than']);
        }

        return $karyawan->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nik',
            'nama',
            'username',
            'status_aktif',
            'role',
            'email',
            'nik_ktp',
            'status_karyawan',
            'tempat_lahir',
            'tgl_lahir',
            'no_kk',
            'alamat',
            'gelar_depan',
            'gelar_belakang',
            'no_hp',
            'masa_kerja',
            'jenis_kelamin',
            'masa_diklat',
            'total_diklat',
            'created_at',
            'updated_at',
        ];
    }

    public function map($karyawan): array
    {
        self::$number++;

        $roles = $karyawan->users->roles->map(function ($role) {
            return $role->name;
        })->toArray();

        $formatDate = fn($date) => $date ? Carbon::parse($date)->format('d-m-Y') : 'N/A';

        $masaKerja = $this->calculateMasaKerja($karyawan->tgl_masuk, $karyawan->tgl_keluar);

        $joinedDiklat = PesertaDiklat::with('diklats')
            ->where('peserta', $karyawan->user_id)
            ->get();

        return [
            self::$number,
            $karyawan->nik,
            $karyawan->users->nama,
            $karyawan->users->username,
            $karyawan->users->user_status_aktif->label,
            implode(', ', $roles),
            $karyawan->email ?? 'N/A',
            $karyawan->nik_ktp ?? 'N/A',
            optional($karyawan->status_karyawans)->label,
            $karyawan->tempat_lahir ?? 'N/A',
            $formatDate($karyawan->tgl_lahir),
            $karyawan->no_kk ?? 'N/A',
            $karyawan->alamat ?? 'N/A',
            $karyawan->gelar_depan ?? 'N/A',
            $karyawan->gelar_belakang ?? 'N/A',
            $karyawan->no_hp ?? 'N/A',
            $masaKerja,
            $karyawan->jenis_kelamin ? 'Laki-laki' : 'Perempuan',
            $this->formatDuration($karyawan->masa_diklat),
            $joinedDiklat->count(),
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
