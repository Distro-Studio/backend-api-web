<?php

namespace App\Exports\Perusahaan;

use App\Models\PesertaDiklat;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

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
        $query = PesertaDiklat::with([
            'diklats',
            'users.data_karyawans.unit_kerjas',
        ])->whereHas('diklats', function ($q) {
            // Jika filter tgl_mulai dan tgl_selesai dikirim oleh frontend, gunakan date range tersebut
            if (isset($this->filters['tgl_mulai']) && isset($this->filters['tgl_selesai'])) {
                $q->whereDate('tgl_mulai', '>=', $this->filters['tgl_mulai'])
                    ->whereDate('tgl_selesai', '<=', $this->filters['tgl_selesai']);
            } else {
                // Default: ambil diklat tahun ini
                $q->whereYear('tgl_mulai', Carbon::now('Asia/Jakarta')->year);
            }
        });

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nik',
            'nama',
            'unit_kerja',
            'nama_pelatihan',
            'tanggal_pelatihan',
            'durasi',
        ];
    }

    public function map($pesertaDiklat): array
    {
        self::$number++;

        $user = $pesertaDiklat->users;
        $dataKaryawan = $user ? $user->data_karyawans : null;
        $diklat = $pesertaDiklat->diklats;

        $nik = $dataKaryawan ? $dataKaryawan->nik : 'N/A';
        $nama = $user ? $user->nama : 'N/A';
        $unitKerja = $dataKaryawan && $dataKaryawan->unit_kerjas
            ? $dataKaryawan->unit_kerjas->nama_unit
            : 'N/A';
        $namaPelatihan = $diklat ? $diklat->nama : 'N/A';

        // Format tanggal pelatihan sebagai range tgl_mulai - tgl_selesai
        $tanggalPelatihan = 'N/A';
        if ($diklat) {
            $mulai = Carbon::parse($diklat->tgl_mulai)->format('d-m-Y');
            $selesai = Carbon::parse($diklat->tgl_selesai)->format('d-m-Y');
            $tanggalPelatihan = $mulai === $selesai ? $mulai : "{$mulai} s/d {$selesai}";
        }

        // Durasi dalam jam dan menit (field durasi dalam detik)
        $durasi = $diklat ? $this->formatDuration($diklat->durasi) : 'N/A';

        return [
            self::$number,
            $nik,
            $nama,
            $unitKerja,
            $namaPelatihan,
            $tanggalPelatihan,
            $durasi,
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%d jam %d menit', $hours, $minutes);
    }
}
