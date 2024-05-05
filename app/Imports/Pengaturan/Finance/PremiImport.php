<?php

namespace App\Imports\Pengaturan\Finance;

use App\Models\Premi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PremiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama_premi' => 'required|string|max:225|unique:premis,nama_premi',
            'jenis_premi' => 'required',
            'besaran_premi' => 'required|numeric',
        ];
    }

    public function model(array $row)
    {
        return new Premi([
            'nama_premi' => $row['nama_premi'],
            'jenis_premi' => $row['jenis_premi'],
            'besaran_premi' => $row['besaran_premi'],
        ]);
    }
}
