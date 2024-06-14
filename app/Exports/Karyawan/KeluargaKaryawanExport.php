<?php

namespace App\Exports\Karyawan;

use Carbon\Carbon;
use App\Models\DataKeluarga;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class KeluargaKaryawanExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return DataKeluarga::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'hubungan',
            'nama_keluarga',
            'pendidikan_terakhir',
            'status_hidup',
            'pekerjaan',
            'no_hp',
            'email',
            'created_at',
            'updated_at',
        ];
    }

    public function map($keluarga): array
    {
        return [
            $keluarga->data_karyawans->users->nama,
            $keluarga->hubungan,
            $keluarga->nama_keluarga,
            $keluarga->pendidikan_terakhir,
            $keluarga->status_hidup ? 'Hidup' : 'Meninggal', // 1 = Hidup, 0 = Meninggal
            $keluarga->pekerjaan ?? 'N/A',
            $keluarga->no_hp ?? 'N/A',
            $keluarga->email ?? 'N/A',
            Carbon::parse($keluarga->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($keluarga->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
