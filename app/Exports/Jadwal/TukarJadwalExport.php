<?php

namespace App\Exports\Jadwal;

use Carbon\Carbon;
use App\Models\TukarJadwal;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TukarJadwalExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return TukarJadwal::with([
            'user_pengajuans',
            'user_ditukars',
            'jadwal_pengajuans',
            'jadwal_ditukars'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'karyawan_pengajuan',
            'unit_kerja',
            'tanggal_pengajuan',
            'karyawan_ditukar',
            'jadwal_pengajuan',
            'jadwal_ditukar',
            'status_penukaran',
            'created_at',
            'updated_at',
        ];
    }

    public function map($tukar_jadwal): array
    {
        return [
            $tukar_jadwal->user_pengajuans->nama,
            $tukar_jadwal->user_pengajuans->data_karyawans->unit_kerjas->nama_unit,
            Carbon::parse($tukar_jadwal->created_at)->format('d-m-Y H:i:s'),
            $tukar_jadwal->user_ditukars->nama,
            $tukar_jadwal->jadwal_pengajuans->shifts->nama,
            $tukar_jadwal->jadwal_ditukars->shifts->nama,
            $tukar_jadwal->status_penukaran ? 'Disetujui' : 'Tidak Disetujui', // 1 = Disetujui, 0 = Tidak Disetujui
            Carbon::parse($tukar_jadwal->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($tukar_jadwal->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
