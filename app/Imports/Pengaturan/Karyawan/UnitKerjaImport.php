<?php

namespace App\Imports\Pengaturan\Karyawan;

use App\Models\UnitKerja;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UnitKerjaImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama_unit' => 'required|string|max:225|unique:unit_kerjas,nama_unit',
            'jenis_karyawan' => 'string|max:225',
        ];
    }

    public function model(array $row)
    {
        return new UnitKerja([
            'nama_unit' => $row['nama_unit'],
            'jenis_karyawan' => $row['jenis_karyawan'],
        ]);
    }
}
