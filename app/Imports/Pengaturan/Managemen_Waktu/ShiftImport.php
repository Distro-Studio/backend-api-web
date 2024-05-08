<?php

namespace App\Imports\Pengaturan\Managemen_Waktu;

use App\Models\Shift;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ShiftImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama' => 'required|string',
            'jam_from' => 'required',
            'jam_to' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama shift tidak diperbolehkan kosong.',
            'nama.string' => 'Nama shift tidak diperbolehkan mengandung angka.',
            'jam_from.required' => 'Jam kerja mulai shift tidak diperbolehkan kosong.',
            'jam_to.required' => 'Jam kerja selesai shift tidak diperbolehkan kosong.',
        ];
    }
    
    public function model(array $row)
    {
        return new Shift([
            'nama' => $row['nama'],
            'jam_from' => $row['jam_from'],
            'jam_to' => $row['jam_to'],
        ]);
    }
}
