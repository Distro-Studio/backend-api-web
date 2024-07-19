<?php

namespace App\Imports\Pengaturan\Karyawan;

use App\Models\Jabatan;
use App\Models\Pertanyaan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PertanyaanImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $Jabatan;

    public function __construct()
    {
        $this->Jabatan = Jabatan::select('id', 'nama_jabatan')->get();
    }

    public function rules(): array
    {
        return [
            'pertanyaan' => 'required|string',
            'jabatan' => 'required|string|exists:jabatans,nama_jabatan',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'pertanyaan.required' => 'Pertanyaan yang diajukan tidak diperbolehkan kosong.',
            'pertanyaan.string' => 'Pertanyaan yang diajukan tidak diperbolehkan mengandung angka.',
            'jabatan.required' => 'Jenis Kompetensi tidak diperbolehkan kosong.',
            'jabatan.string' => 'Jenis Kompetensi tidak diperbolehkan mengandung angka.',
            'jabatan.exists' => 'Jabatan yang dipilih tidak ditemukan dan tidak valid.',
        ];
    }

    public function model(array $row)
    {
        $jabatan = $this->Jabatan->where('nama_jabatan', $row['jabatan'])->first();
        return new Pertanyaan([
            'pertanyaan' => $row['pertanyaan'],
            'jabatan_id' => $jabatan
        ]);
    }
}
