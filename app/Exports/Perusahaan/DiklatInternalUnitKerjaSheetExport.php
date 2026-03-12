<?php

namespace App\Exports\Perusahaan;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DiklatInternalUnitKerjaSheetExport implements FromCollection, WithHeadings, WithTitle
{
    protected Collection $rows;

    protected string $title;

    public function __construct(Collection $rows, string $title)
    {
        $this->rows = $rows;
        $this->title = $title;
    }

    public function collection()
    {
        return $this->rows->map(function ($row) {
            return [
                'nama' => $row['nama'] ?? '-',
                'nip' => $row['nip'] ?? '-',
                'unit_kerja' => $row['unit_kerja'] ?? '-',
                'nama_diklat' => $row['nama_diklat'] ?? '-',
                'kategori_diklat' => $row['kategori_diklat'] ?? '-',
                'kuota' => $row['kuota'] ?? '-',
                'tanggal_mulai' => $this->formatDate($row['tanggal_mulai'] ?? null),
                'tanggal_selesai' => $this->formatDate($row['tanggal_selesai'] ?? null),
                'jam_mulai' => $this->formatTime($row['jam_mulai'] ?? null),
                'jam_selesai' => $this->formatTime($row['jam_selesai'] ?? null),
                'durasi' => $this->formatDuration($row['durasi'] ?? null),
                'lokasi' => $row['lokasi'] ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Unit Kerja',
            'Nama Diklat',
            'Kategori Diklat',
            'Kuota',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi',
            'Lokasi',
        ];
    }

    public function title(): string
    {
        return $this->title;
    }

    private function formatDate($value): string
    {
        if (empty($value)) {
            return '-';
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d', 'd-m-Y H:i:s', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, (string) $value);
                if ($date !== false) {
                    return $date->format('d-m-Y');
                }
            } catch (\Throwable $th) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $th) {
            return (string) $value;
        }
    }

    private function formatTime($value): string
    {
        if (empty($value)) {
            return '-';
        }

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                $time = Carbon::createFromFormat($format, (string) $value);
                if ($time !== false) {
                    return $time->format('H:i');
                }
            } catch (\Throwable $th) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable $th) {
            return (string) $value;
        }
    }

    private function formatDuration($seconds): string
    {
        if ($seconds === null || $seconds === '') {
            return '-';
        }

        $seconds = (int) $seconds;
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours === 0 && $minutes === 0) {
            return '0 menit';
        }

        if ($hours === 0) {
            return $minutes . ' menit';
        }

        if ($minutes === 0) {
            return $hours . ' jam';
        }

        return $hours . ' jam ' . $minutes . ' menit';
    }
}