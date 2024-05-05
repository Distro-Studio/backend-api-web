<?php

namespace App\Exports\Pengaturan\Akun;

use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;

class RolesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Role::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Role',
            'Deskripsi',
        ];
    }

    public function map($roles): array
    {
        return [
            $roles->id,
            $roles->name,
            $roles->description,
        ];
    }
}
