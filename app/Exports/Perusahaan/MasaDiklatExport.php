<?php

namespace App\Exports\Perusahaan;

use App\Models\DataKaryawan;
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
        $karyawan = DataKaryawan::with([
            'users',
            'unit_kerjas',
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
            'unit_kerja',
            'nama_pelatihan',
            'tanggal_pelatihan',
            'durasi',
        ];
    }

    public function map($karyawan): array
    {
        self::$number++;

        // Ambil diklat yang diikuti karyawan
        $pesertaDiklats = PesertaDiklat::with('diklats')
            ->where('peserta', $karyawan->user_id)
            ->get()
            ->filter(function ($peserta) {
                return $peserta->diklats !== null;
            });

        // Filter diklat berdasarkan date range atau tahun ini
        if (isset($this->filters['tgl_mulai']) && isset($this->filters['tgl_selesai'])) {
            $tglMulai = Carbon::parse($this->filters['tgl_mulai'])->startOfDay();
            $tglSelesai = Carbon::parse($this->filters['tgl_selesai'])->endOfDay();

            $pesertaDiklats = $pesertaDiklats->filter(function ($peserta) use ($tglMulai, $tglSelesai) {
                $diklatMulai = Carbon::parse($peserta->diklats->tgl_mulai);

                return $diklatMulai->between($tglMulai, $tglSelesai);
            });
        } else {
            // Default: hanya diklat tahun ini
            $currentYear = Carbon::now('Asia/Jakarta')->year;
            $pesertaDiklats = $pesertaDiklats->filter(function ($peserta) use ($currentYear) {
                return Carbon::parse($peserta->diklats->tgl_mulai)->year === $currentYear;
            });
        }

        // Nama pelatihan (gabung semua nama diklat)
        $namaPelatihan = $pesertaDiklats->map(function ($peserta) {
            return $peserta->diklats->nama;
        })->implode(', ');

        // Tanggal pelatihan (gabung semua tanggal diklat)
        $tanggalPelatihan = $pesertaDiklats->map(function ($peserta) {
            $mulai = Carbon::parse($peserta->diklats->tgl_mulai)->format('d-m-Y');
            $selesai = Carbon::parse($peserta->diklats->tgl_selesai)->format('d-m-Y');

            return $mulai === $selesai ? $mulai : "{$mulai} s/d {$selesai}";
        })->implode(', ');

        // Durasi = jumlah total durasi semua diklat yang diikuti (dalam detik)
        $totalDurasi = $pesertaDiklats->sum(function ($peserta) {
            return $peserta->diklats->durasi ?? 0;
        });

        $moreThan = $this->filters['more_than'] ?? null;
        $lessThan = $this->filters['less_than'] ?? null;

        if ($moreThan !== null && $lessThan === null) {
            // Jika hanya more_than yang diisi
            if (! ($totalDurasi > $moreThan)) {
                return [];
            }
        } elseif ($moreThan !== null && $lessThan !== null) {
            // Jika keduanya diisi (Range)
            if (! ($totalDurasi >= $moreThan && $totalDurasi <= $lessThan)) {
                return [];
            }
        }
        // Tambahan: Jika hanya less_than yang diisi (opsional)
        elseif ($moreThan === null && $lessThan !== null) {
            if (! ($totalDurasi < $lessThan)) {
                return [];
            }
        }
        // ---------------------------------

        // Proses string nama dan tanggal pelatihan
        $namaPelatihan = $pesertaDiklats->map(fn ($p) => $p->diklats->nama)->implode(', ');
        $tanggalPelatihan = $pesertaDiklats->map(function ($peserta) {
            $mulai = Carbon::parse($peserta->diklats->tgl_mulai)->format('d-m-Y');
            $selesai = Carbon::parse($peserta->diklats->tgl_selesai)->format('d-m-Y');

            return $mulai === $selesai ? $mulai : "{$mulai} s/d {$selesai}";
        })->implode(', ');

        return [
            self::$number,
            $karyawan->nik,
            $karyawan->users->nama ?? 'N/A',
            optional($karyawan->unit_kerjas)->nama_unit ?? 'N/A',
            $namaPelatihan ?: 'N/A',
            $tanggalPelatihan ?: 'N/A',
            $this->formatDuration($totalDurasi),
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%d jam %d menit', $hours, $minutes);
    }
}
