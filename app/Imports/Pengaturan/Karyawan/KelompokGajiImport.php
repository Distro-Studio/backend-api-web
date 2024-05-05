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
            'nama_kelompok' => 'required|string|max:10|unique:kelompok_gajis,nama_kelompok',
            'besaran_gaji' => 'required|numeric',
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
