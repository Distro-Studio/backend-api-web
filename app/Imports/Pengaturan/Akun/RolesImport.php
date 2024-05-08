<?php

namespace App\Imports\Pengaturan\Akun;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Http\Exceptions\HttpResponseException;

class RolesImport implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $data = [
                'name' => $row['name'],
                'description' => $row['description'],
                'guard_name' => 'web'
            ];

            Role::create($data);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:roles,name',
            'description' => 'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Nama Role tidak diperbolehkan kosong.',
            'name.unique' => 'Nama Role pada tabel excel atau database sudah pernah dibuat atau terduplikat.'
        ];
    }
}
