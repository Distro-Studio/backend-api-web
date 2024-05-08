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
            'nama_jabatan' => 'required|string|unique:jabatans,nama_jabatan',
            'is_struktural' => 'nullable',
            'tunjangan' => 'required|numeric'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_jabatan.required' => 'Nama Jabatan tidak diperbolehkan kosong.',
            'nama_jabatan.string' => 'Nama Jabatan tidak diperbolehkan mengandung angka.',
            'nama_jabatan.unique' => 'Nama Jabatan pada tabel excel atau database sudah pernah dibuat atau terduplikat.',
            'tunjangan.required' => 'Jumlah Tunjangan tidak diperbolehkan kosong.',
            'tunjangan.numeric' => 'Tunjangan hanya diperbolehkan berisi angka.',
        ];
    }

    public function model(array $row)
    {
        $is_struktural = isset($row['is_struktural']) ? (
            $row['is_struktural'] === 'Ya' ? 1 : (
                $row['is_struktural'] === 'Tidak' ? 0 : 0 // Default to 0 even for invalid values
            )
        ) : 0;

        return new Jabatan([
            'nama_jabatan' => $row['nama_jabatan'],
            'is_struktural' => $is_struktural,
            'tunjangan' => $row['tunjangan']
        ]);
    }
}
