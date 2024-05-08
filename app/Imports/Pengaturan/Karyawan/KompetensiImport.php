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
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_kompetensi.required' => 'Nama Kompetensi tidak diperbolehkan kosong.',
            'nama_kompetensi.string' => 'Nama Kompetensi tidak diperbolehkan mengandung angka.',
            'nama_kompetensi.unique' => 'Nama Kompetensi pada tabel excel atau database sudah pernah dibuat atau terduplikat.',
            'jenis_kompetensi.required' => 'Jenis Kompetensi tidak diperbolehkan kosong.',
            'jenis_kompetensi.string' => 'Jenis Kompetensi tidak diperbolehkan mengandung angka.',
            'total_tunjangan.required' => 'Jumlah Tunjangan tidak diperbolehkan kosong.',
            'total_tunjangan.numeric' => 'Jumlah Tunjangan tidak diperbolehkan mengandung huruf.',
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
