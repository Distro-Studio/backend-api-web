<?php

namespace App\Imports\Pengaturan\Karyawan;

use App\Models\KelompokGaji;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class KelompokGajiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama_kelompok' => 'required|string|unique:kelompok_gajis,nama_kelompok',
            'besaran_gaji' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_kelompok.required' => 'Kode Kelompok Gaji tidak diperbolehkan kosong.',
            'nama_kelompok.string' => 'Kode Kelompok Gaji tidak diperbolehkan mengandung angka.',
            'nama_kelompok.unique' => 'Kode Kelompok Gaji pada tabel excel atau database sudah pernah dibuat atau terduplikat.',
            'besaran_gaji.required' => 'Jumlah Gaji tidak diperbolehkan kosong.',
            'besaran_gaji.numeric' => 'Jumlah Gaji tidak diperbolehkan mengandung huruf.',
        ];
    }

    public function model(array $row)
    {
        return new KelompokGaji([
            'nama_kelompok' => $row['nama_kelompok'],
            'besaran_gaji' => $row['besaran_gaji'],
        ]);
    }
}
