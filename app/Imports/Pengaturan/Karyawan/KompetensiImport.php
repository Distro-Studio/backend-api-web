<?php

namespace App\Imports\Pengaturan\Karyawan;

use App\Models\Kompetensi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KompetensiImport implements ToModel, WithHeadingRow, WithValidation
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

    public function model(array $row)
    {
        return new Kompetensi([
            'nama_kompetensi' => $row['nama_kompetensi'],
            'jenis_kompetensi' => $row['jenis_kompetensi'],
            'total_tunjangan' => $row['total_tunjangan'],
        ]);
    }
}
