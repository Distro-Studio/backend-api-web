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
            'no',
            'tanggal_pengajuan',
            'unit_kerja',
            'kategori_penukaran',
            'status_penukaran',
            'karyawan_pengajuan',
            'jadwal_karyawan_pengajuan',
            'karyawan_ditukar',
            'jadwal_karyawan_ditukar',
            'created_at',
            'updated_at',
        ];
    }

    public function map($tukar_jadwal): array
    {
        static $no = 1;
        return [
            $no++,
            Carbon::parse($tukar_jadwal->created_at)->format('d-m-Y'),
            $tukar_jadwal->user_pengajuans->data_karyawans->unit_kerjas->nama_unit,
            $this->getKategoriName($tukar_jadwal->kategori_penukaran_id),
            $this->getStatusName($tukar_jadwal->status_penukaran_id),
            $tukar_jadwal->user_pengajuans->nama,
            $tukar_jadwal->jadwal_pengajuans->shifts ? $tukar_jadwal->jadwal_pengajuans->shifts->nama : 'Libur',
            $tukar_jadwal->user_ditukars->nama,
            $tukar_jadwal->jadwal_ditukars->shifts ? $tukar_jadwal->jadwal_ditukars->shifts->nama : 'Libur',
            Carbon::parse($tukar_jadwal->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($tukar_jadwal->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    private function getStatusName($statusId)
    {
        $statuses = [
            1 => 'Menunggu',
            2 => 'Disetujui',
            3 => 'Tidak Disetujui',
        ];

        return $statuses[$statusId] ?? 'Unknown';
    }

    private function getKategoriName($kategoriId)
    {
        $categories = [
            1 => 'Tukar Shift',
            2 => 'Tukar Libur',
        ];

        return $categories[$kategoriId] ?? 'Unknown';
    }
}
