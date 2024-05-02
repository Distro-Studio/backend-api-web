<?php

namespace App\Imports\Pengaturan\Akun\Roles;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class RolesImport implements Importable, SkipsOnFailure, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:225|unique:roles,name',
            'description' => 'string|max:225|nullable',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        // Handle import failures (optional)
        foreach ($failures as $failure) {
            // Log or report the error details
        }
    }
}
