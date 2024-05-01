<?php

namespace App\Imports\Pengaturan\Karyawan\Kompetensi;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class KompetensiImport implements Importable, SkipsOnFailure, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama_kompetensi' => 'required|string|max:225|unique:kompetensis,nama_kompetensi',
            'jenis_kompetensi' => 'required|string|max:225',
            'total_tunjangan' => 'required|numeric',
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
