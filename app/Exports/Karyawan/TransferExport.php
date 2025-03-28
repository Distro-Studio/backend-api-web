<?php

namespace App\Exports\Karyawan;

use Carbon\Carbon;
use App\Models\TransferKaryawan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransferExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private static $number = 0;

    public function collection()
    {
        return TransferKaryawan::with(['unit_kerja_asals', 'unit_kerja_tujuans', 'jabatan_asals', 'jabatan_tujuans'])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'tanggal_pengajuan',
            'tanggal_mulai',
            'unit_kerja_asal',
            'unit_kerja_tujuan',
            'jabatan_asal',
            'jabatan_tujuan',
            'kelompok_gaji_asal',
            'kelompok_gaji_tujuan',
            'role_asal',
            'role_tujuan',
            'kategori_transfer',
            'alasan'
        ];
    }

    public function map($transfer): array
    {
        self::$number++;
        return [
            self::$number,
            $transfer->users->nama,
            Carbon::parse($transfer->created_at)->format('d-m-Y'),
            $transfer->tgl_mulai,
            $transfer->unit_kerja_asals->nama_unit,
            $transfer->unit_kerja_tujuans->nama_unit ?? 'N/A',
            $transfer->jabatan_asals->nama_jabatan,
            $transfer->jabatan_tujuans->nama_jabatan ?? 'N/A',
            $transfer->kelompok_gaji_asals->nama_kelompok,
            $transfer->kelompok_gaji_tujuans->nama_kelompok ?? 'N/A',
            $transfer->role_asals->name,
            $transfer->role_tujuans->name ?? 'N/A',
            $transfer->kategori_transfer_karyawans->label,
            $transfer->alasan ?? 'N/A'
        ];
    }
}
