<?php

namespace App\Imports\Pengaturan\Karyawan\UnitKerja;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class UnitKerjaImport implements Importable, SkipsOnFailure, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama_unit' => 'required|string|max:225|unique:unit_kerjas,nama_unit',
            // 'jenis_karyawan' => 'string|max:225',
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
