<?php

namespace App\Imports\Karyawan;

use App\Models\User;
use App\Models\TrackRecord;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RekamJejakImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $User;
    public function __construct()
    {
        $this->User = User::select('id', 'nama')->get();
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:225|exists:users,nama',
            'tgl_masuk' => 'required|date',
            'tgl_keluar' => 'nullable|date',
            'promosi' => 'nullable|string',
            'mutasi' => 'nullable|string',
            'penghargaan' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama karyawan melebihi batas maksimum panjang karakter.',
            'nama.exists' => 'Maaf nama tersebut tidak tersedia.',
            'tgl_masuk.required' => 'Tanggal masuk tidak diperbolehkan kosong.',
            'tgl_masuk.date' => 'Format tanggal masuk tidak sesuai.',
            'tgl_keluar.date' => 'Format tanggal keluar tidak sesuai.',
            'promosi.string' => 'Deskripsi promosi tidak diperbolehkan mengandung angka.',
            'mutasi.string' => 'Deskripsi mutasi tidak diperbolehkan mengandung angka.',
            'penghargaan.string' => 'Deskripsi penghargaan tidak diperbolehkan mengandung angka.',
        ];
    }
    public function model(array $row)
    {
        $user_id = $this->User->where('nama', $row['nama'])->first();

        return new TrackRecord([
            'user_id' => $user_id->id,
            'tgl_masuk' => $row['tgl_masuk'],
            'tgl_keluar' => $row['tgl_keluar'],
            'promosi' => $row['promosi'],
            'mutasi' => $row['mutasi'],
            'penghargaan' => $row['penghargaan'],
        ]);
    }
}
