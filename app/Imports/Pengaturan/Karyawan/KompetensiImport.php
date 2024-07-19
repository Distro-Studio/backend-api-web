<?php

namespace App\Imports\Pengaturan\Karyawan;

use App\Models\Kompetensi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KompetensiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama_kompetensi' => 'required|string|unique:kompetensis,nama_kompetensi',
            'jenis_kompetensi' => 'required|string',
            'total_tunjangan' => 'required|numeric',
            'total_bor' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_kompetensi.required' => 'Nama kompetensi tidak diperbolehkan kosong.',
            'nama_kompetensi.string' => 'Nama kompetensi tidak diperbolehkan mengandung angka.',
            'nama_kompetensi.unique' => 'Nama kompetensi pada tabel excel atau database sudah pernah dibuat atau terduplikat.',
            'jenis_kompetensi.required' => 'Jenis kompetensi tidak diperbolehkan kosong.',
            'jenis_kompetensi.string' => 'Jenis kompetensi tidak diperbolehkan mengandung angka.',
            'total_tunjangan.required' => 'Jumlah tunjangan tidak diperbolehkan kosong.',
            'total_tunjangan.numeric' => 'Jumlah tunjangan tidak diperbolehkan mengandung huruf.',
            'total_bor.required' => 'Jumlah BOR tidak diperbolehkan kosong.',
            'total_bor.numeric' => 'Jumlah BOR tidak diperbolehkan mengandung huruf.',
        ];
    }

    public function model(array $row)
    {
        return new Kompetensi([
            'nama_kompetensi' => $row['nama_kompetensi'],
            'jenis_kompetensi' => $row['jenis_kompetensi'],
            'total_tunjangan' => $row['total_tunjangan'],
        ]);
    }
}
