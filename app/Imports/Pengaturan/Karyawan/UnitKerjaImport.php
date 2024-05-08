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
            'nama_unit' => 'required|string|unique:unit_kerjas,nama_unit',
            'jenis_karyawan' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_unit.required' => 'Nama Unit Kerja tidak diperbolehkan kosong.',
            'nama_unit.unique' => 'Nama Unit pada tabel excel atau database sudah pernah dibuat atau terduplikat.',
            'nama_unit.string' => 'Nama Unit Kerja pada tabel excel/database anda sudah pernah dibuat/terduplikat.',
            'jenis_karyawan.required' => 'Jenis Karyawan tidak diperbolehkan kosong.',
        ];
    }

    public function model(array $row)
    {
        $jenisKaryawan = isset($row['jenis_karyawan']) ? (
            $row['jenis_karyawan'] === 'Shift' ? 1 : (
                $row['jenis_karyawan'] === 'Non-Shift' ? 0 : 0 // Default to 0 even for invalid values
            )
        ) : 0;

        return new UnitKerja([
            'nama_unit' => $row['nama_unit'],
            'jenis_karyawan' => $jenisKaryawan,
        ]);
    }
}
