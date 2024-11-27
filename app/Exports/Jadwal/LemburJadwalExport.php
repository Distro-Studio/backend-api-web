<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\Lembur;
use App\Helpers\RandomHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class LemburJadwalExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Lembur::with([
            'users',
            'users.data_karyawans.unit_kerjas',
            'jadwals',
            'status_lemburs',
            'kategori_kompensasis'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'jenis_karyawan',
            'shift',
            'tanggal_pengajuan',
            'tanggal_mulai',
            'tanggal_selesai',
            'durasi',
            'catatan',
            'created_at'
        ];
    }

    public function map($lembur): array
    {
        static $no = 1;

        $jenisKaryawan = $lembur->users->data_karyawans->unit_kerjas->jenis_karyawan ?? 'N/A';

        if ($jenisKaryawan == 1) { // Shift
            $tgl_mulai = $lembur->jadwals
                ? Carbon::parse($lembur->jadwals->tgl_mulai)->format('d-m-Y')
                : 'N/A';
            $tgl_selesai = $lembur->jadwals
                ? Carbon::parse($lembur->jadwals->tgl_selesai)->format('d-m-Y')
                : 'N/A';
            $shift = $lembur->jadwals->shifts->nama ?? 'N/A';
        } else { // Non-shift
            $tgl_mulai = Carbon::parse($lembur->tgl_pengajuan)->format('d-m-Y') ?? 'N/A';
            $tgl_selesai = $lembur->tgl_selesai
                ? Carbon::parse($lembur->tgl_selesai)->format('d-m-Y')
                : 'N/A';
            $shift = 'N/A';
        }

        return [
            $no++,
            $lembur->users->nama,
            $jenisKaryawan == 1 ? 'Shift' : 'Non-Shift',
            $shift,
            $lembur->tgl_pengajuan ?? 'N/A',
            $tgl_mulai,
            $tgl_selesai,
            $this->formatDuration($lembur->durasi),
            $lembur->catatan,
            Carbon::parse($lembur->created_at)->format('d-m-Y H:i:s')
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d jam %d menit", $hours, $minutes);
    }
}
