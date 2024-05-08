<?php

namespace App\Imports\Pengaturan\Managemen_Waktu;

use App\Models\HariLibur;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HariLiburImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama' => 'required|string',
            'tanggal' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama hari libur tidak diperbolehkan kosong.',
            'nama.string' => 'Nama hari libur tidak diperbolehkan mengandung angka.',
            'tanggal.required' => 'Tanggal hari libur tidak diperbolehkan kosong.',
        ];
    }

    public function model(array $row)
    {
        return new HariLibur([
            'nama' => $row['nama'],
            'tanggal' => $row['tanggal'],
        ]);
    }
}
