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
        return TransferKaryawan::with(['unit_kerja_asals', 'unit_kerja_tujuans', 'jabatan_asals', 'jabatan_tujuans'])->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'tanggal',
            'unit_kerja_asal',
            'unit_kerja_tujuan',
            'jabatan_asal',
            'jabatan_tujuan',
            'tipe',
            'alasan',
            'created_at',
            'updated_at',
        ];
    }

    public function map($transfer): array
    {
        return [
            $transfer->users->nama,
            $transfer->tanggal,
            $transfer->unit_kerja_asals->nama_unit,
            $transfer->unit_kerja_tujuans->nama_unit,
            $transfer->jabatan_asals->nama_jabatan,
            $transfer->jabatan_tujuans->nama_jabatan,
            $transfer->tipe ?? 'Data tidak tersedia',
            $transfer->alasan ?? 'Data tidak tersedia',
            Carbon::parse($transfer->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($transfer->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
