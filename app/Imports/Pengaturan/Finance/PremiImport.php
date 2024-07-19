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
            'sumber_potongan' => 'required|string',
            'jenis_premi' => 'required',
            'besaran_premi' => 'required|numeric',
            'minimal_rate' => 'nullable|integer',
            'maksimal_rate' => 'nullable|integer',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_premi.required' => 'Nama premi tidak diperbolehkan kosong.',
            'nama_premi.string' => 'Nama premi tidak diperbolehkan mengandung angka.',
            'nama_premi.unique' => 'Nama premi pada tabel excel atau database sudah pernah dibuat atau terduplikat.',
            'sumber_potongan.required' => 'Sumber potongan premi tidak diperbolehkan kosong.',
            'sumber_potongan.string' => 'Sumber potongan premi tidak diperbolehkan mengandung angka.',
            'jenis_premi.required' => 'Silahkan pilih jenis premi terlebih dahulu.',
            'besaran_premi.required' => 'Jumlah premi tidak diperbolehkan kosong.',
            'besaran_premi.numeric' => 'Jumlah premi tidak diperbolehkan mengandung huruf.',
            'minimal_rate.integer' => 'Minimal rate tidak diperbolehkan mengandung huruf.',
            'maksimal_rate.integer' => 'Maksimal rate tidak diperbolehkan mengandung huruf.',
        ];
    }

    public function model(array $row)
    {
        return new Premi([
            'nama_premi' => $row['nama_premi'],
            'sumber_potongan' => $row['sumber_potongan'],
            'jenis_premi' => $row['jenis_premi'],
            'besaran_premi' => $row['besaran_premi'],
            'minimal_rate' => $row['minimal_rate'],
            'maksimal_rate' => $row['maksimal_rate'],
        ]);
    }
}
