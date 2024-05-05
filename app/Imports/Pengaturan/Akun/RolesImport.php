<?php

namespace App\Imports\Pengaturan\Akun;

use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RolesImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:225|unique:roles,name',
            'description' => 'string|max:225|nullable',
        ];
    }

    public function model(array $row)
    {
        return new Role([
            'name' => $row['name'],
            'description' => $row['description'],
        ]);
    }
}
