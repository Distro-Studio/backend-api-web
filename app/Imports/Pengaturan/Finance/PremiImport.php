<?php

namespace App\Imports\Pengaturan\Finance;

use App\Models\Premi;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PremiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama_premi' => 'required|string|unique:premis,nama_premi',
            'jenis_premi' => 'required',
            'besaran_premi' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_premi.required' => 'Nama Premi tidak diperbolehkan kosong.',
            'nama_premi.string' => 'Nama Premi tidak diperbolehkan mengandung angka.',
            'nama_premi.unique' => 'Nama Premi pada tabel excel atau database sudah pernah dibuat atau terduplikat.',
            'jenis_premi.required' => 'Silahkan pilih jenis premi terlebih dahulu.',
            'besaran_premi.required' => 'Jumlah Premi tidak diperbolehkan kosong.',
            'besaran_premi.numeric' => 'Jumlah Premi tidak diperbolehkan mengandung huruf.',
        ];
    }

    public function model(array $row)
    {
        return new Premi([
            'nama_premi' => $row['nama_premi'],
            'jenis_premi' => $row['jenis_premi'],
            'besaran_premi' => $row['besaran_premi'],
        ]);
    }
}
