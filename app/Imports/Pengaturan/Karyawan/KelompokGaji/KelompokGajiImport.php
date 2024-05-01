<?php

namespace App\Imports\Pengaturan\Karyawan\KelompokGaji;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class KelompokGajiImport implements Importable, SkipsOnFailure, WithValidation
{
    use Importable;
    
    public function rules(): array
    {
        return [
            'nama_kelompok' => 'required|string|max:10|unique:kelompok_gajis,nama_kelompok',
            'besaran_gaji' => 'required|numeric',
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
