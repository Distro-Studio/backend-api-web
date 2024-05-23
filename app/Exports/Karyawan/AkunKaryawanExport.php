<?php

namespace App\Exports\Karyawan;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class AkunKaryawanExport implements FromCollection, WithHeadings, WithMapping
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
            return User::whereIn('id', $this->ids)->get();
        }
        return User::all();
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
            $akun->data_karyawans->nik ?? 'Data tidak tersedia',
            $akun->data_karyawans->email ?? 'Data tidak tersedia',
            $akun->username,
            $akun->data_karyawans->status_karyawan ?? 'Data tidak tersedia',
            $akun->created_at,
            $akun->updated_at,
        ];
    }
}
