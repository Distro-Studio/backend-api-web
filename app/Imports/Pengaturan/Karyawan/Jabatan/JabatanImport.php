<?php

namespace App\Imports\Pengaturan\Karyawan\Jabatan;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class JabatanImport implements Importable, SkipsOnFailure, WithValidation
{
    use Importable;
    
    public function rules(): array
    {
        return [
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan',
            'is_struktural' => 'boolean',
            'tunjangan' => 'nullable|numeric',
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
