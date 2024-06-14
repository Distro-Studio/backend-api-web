<?php

namespace App\Exports\Pengaturan\Akun;

use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RolesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Role::all();
    }

    public function headings(): array
    {
        return [
            'nama',
            'deskripsi',
            'created_at',
            'updated_at',
        ];
    }

    public function map($roles): array
    {
        return [
            $roles->name,
            $roles->description,
            Carbon::parse($roles->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($roles->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
