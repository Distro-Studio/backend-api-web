<?php

namespace App\Exports\Karyawan;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class AkunKaryawanExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return User::with('data_karyawans')->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'nik',
            'email',
            'username',
            'status_karyawan',
            'created_at',
            'updated_at',
        ];
    }

    public function map($akun): array
    {
        return [
            $akun->nama,
            $akun->data_karyawans->nik ?? 'N/A',
            $akun->data_karyawans->email ?? 'N/A',
            $akun->username,
            $akun->data_karyawans->status_karyawan ?? 'N/A',
            Carbon::parse($akun->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($akun->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
