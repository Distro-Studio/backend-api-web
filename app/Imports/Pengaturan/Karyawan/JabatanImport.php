<?php

namespace App\Imports\Pengaturan\Karyawan;

use App\Models\Jabatan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class JabatanImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;
    
    public function rules(): array
    {
        return [
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan',
            'is_struktural' => 'boolean',
            'tunjangan' => 'nullable|numeric'
        ];
    }

    public function model(array $row)
    {
        return new Jabatan([
            'nama_jabatan' => $row['nama_jabatan'],
            'is_struktural' => Boolean::false($row['is_struktural']),
            'tunjangan' => $row['tunjangan']
        ]);
    }
}
