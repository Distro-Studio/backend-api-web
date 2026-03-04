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
        $karyawanQuery = DataKaryawan::with([
            'users',
            'unit_kerjas',
        ])->where('id', '!=', 1)
            ->orderBy('nik', 'asc');

        $karyawans = $karyawanQuery->get();

        // Determine date range filters
        if (isset($this->filters['tgl_mulai']) && isset($this->filters['tgl_selesai'])) {
            $tglMulai = Carbon::parse($this->filters['tgl_mulai'])->startOfDay();
            $tglSelesai = Carbon::parse($this->filters['tgl_selesai'])->endOfDay();
        } else {
            $tglMulai = Carbon::now('Asia/Jakarta')->startOfYear();
            $tglSelesai = Carbon::now('Asia/Jakarta')->endOfYear();
        }

        // Fetch and calculate durations for each employee in the collection
        $karyawans = $karyawans->map(function ($k) use ($tglMulai, $tglSelesai) {
            $pesertaDiklats = PesertaDiklat::with('diklats')
                ->where('peserta', $k->user_id)
                ->whereHas('diklats', function ($q) use ($tglMulai, $tglSelesai) {
                    $q->whereBetween('tgl_mulai', [$tglMulai, $tglSelesai]);
                })
                ->get()
                ->filter(fn($p) => $p->diklats !== null);

            $k->calculated_peserta_diklats = $pesertaDiklats;
            $k->calculated_total_durasi = $pesertaDiklats->sum(fn($p) => $p->diklats->durasi ?? 0);

            return $k;
        });

        // Apply filters based on the CALCULATED duration
        if (isset($this->filters['less_than']) || isset($this->filters['more_than'])) {
            $karyawans = $karyawans->filter(function ($k) {
                $match = true;
                
                if (isset($this->filters['less_than']) && $this->filters['less_than'] !== '') {
                    $match = $match && ($k->calculated_total_durasi <= $this->filters['less_than']);
                }
                
                if (isset($this->filters['more_than']) && $this->filters['more_than'] !== '') {
                    $match = $match && ($k->calculated_total_durasi >= $this->filters['more_than']);
                }
                
                return $match;
            });
        }

        return $karyawans;
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

        // Use the pre-calculated data from the collection() method
        $pesertaDiklats = $karyawan->calculated_peserta_diklats;

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

        return [
            self::$number,
            $karyawan->nik,
            $karyawan->users->nama ?? 'N/A',
            optional($karyawan->unit_kerjas)->nama_unit ?? 'N/A',
            $namaPelatihan ?: 'N/A',
            $tanggalPelatihan ?: 'N/A',
            $this->formatDuration($karyawan->calculated_total_durasi),
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%d jam %d menit', $hours, $minutes);
    }
}
