<?php

namespace App\Imports\Pengaturan\Managemen_Waktu;

use App\Models\TipeCuti;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CutiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'kuota' => 'required|integer',
            'is_need_requirement' => 'required|boolean',
            'keterangan' => 'required|string|max:255',
            'cuti_administratif' => 'required|boolean',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama cuti tidak diperbolehkan kosong.',
            'nama.string' => 'Nama cuti tidak diperbolehkan mengandung karakter selain huruf.',
            'nama.max' => 'Nama cuti melebihi batas maksimum panjang karakter.',
            'kuota.required' => 'Kuota cuti tidak diperbolehkan kosong.',
            'kuota.integer' => 'Kuota cuti tidak diperbolehkan mengandung karakter selain angka.',
            'is_need_requirement.required' => 'Persyaratan cuti tidak diperbolehkan kosong.',
            'is_need_requirement.boolean' => 'Persyaratan cuti harus berupa boolean.',
            'keterangan.required' => 'Keterangan cuti tidak diperbolehkan kosong.',
            'keterangan.string' => 'Keterangan cuti diperbolehkan mengandung karakter selain huruf.',
            'keterangan.max' => 'Keterangan melebihi batas maksimum panjang karakter.',
            'cuti_administratif.required' => 'Cuti absensi tidak boleh kosong.',
            'cuti_administratif.boolean' => 'Cuti absensi harus berupa boolean.',
        ];
    }

    public function model(array $row)
    {
        return new TipeCuti([
            'nama' => $row['nama'],
            'kuota' => $row['kuota'],
            'is_need_requirement' => $this->convertToBoolean($row['is_need_requirement']),
            'keterangan' => $row['keterangan'],
            'cuti_administratif' => $this->convertToBoolean($row['cuti_administratif']),
        ]);
    }

    private function convertToBoolean($value): bool
    {
        return strtolower($value) === 'ya';
    }
}
