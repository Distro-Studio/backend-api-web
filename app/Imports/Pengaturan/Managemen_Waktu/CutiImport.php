<?php

namespace App\Imports\Pengaturan\Managemen_Waktu;

use App\Models\TipeCuti;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CutiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama' => 'required|max:255',
            'durasi' => 'required',
            'waktu' => 'nullable'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama Cuti tidak diperbolehkan kosong.',
            'nama.max' => 'Nama Cuti melebihi batas maksimum panjang karakter.',
            'durasi.required' => 'Durasi Cuti tidak diperbolehkan kosong.',
        ];
    }

    public function model(array $row)
    {
        return new TipeCuti([
            'nama' => $row['nama'],
            'durasi' => $row['durasi'],
            'waktu' => isset($row['waktu']) ? $row['waktu'] : null,
        ]);
    }
}
