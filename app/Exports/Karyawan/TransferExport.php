<?php

namespace App\Exports\Karyawan;

use App\Models\TransferKaryawan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransferExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    protected $ids;

    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        if (!empty($this->ids)) {
            return TransferKaryawan::whereIn('id', $this->ids)->get();
        }
        return TransferKaryawan::with(['unit_kerja_froms', 'unit_kerja_tos', 'jabatan_froms', 'jabatan_tos'])->get();
    }

    public function headings(): array
    {
        return [
            'user_id',
            'tanggal',
            'unit_kerja_from',
            'unit_kerja_to',
            'jabatan_from',
            'jabatan_to',
            'tipe',
            'alasan',
            // 'dokumen',
            'created_at',
            'updated_at',
        ];
    }

    public function map($transfer): array
    {
        return [
            $transfer->users->nama,
            $transfer->tanggal,
            $transfer->unit_kerja_froms->nama_unit,
            $transfer->unit_kerja_tos->nama_unit,
            $transfer->jabatan_froms->nama_jabatan,
            $transfer->jabatan_tos->nama_jabatan,
            $transfer->tipe ?? 'Data tidak tersedia',
            $transfer->alasan ?? 'Data tidak tersedia',
            // $transfer->dokumen ?? 'Data tidak tersedia',
            $transfer->created_at,
            $transfer->updated_at,
        ];
    }
}
