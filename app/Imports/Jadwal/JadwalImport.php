<?php

namespace App\Imports\Jadwal;

use App\Models\Jadwal;
use App\Models\User;
use App\Models\Shift;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class JadwalImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $User;
    private $Shift;
    public function __construct()
    {
        $this->User = User::select('id', 'nama')->get();
        $this->Shift = Shift::select('id', 'nama')->get();
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|exists:users,nama',
            'tanggal_mulai' => 'required|string',
            'tanggal_selesai' => 'string',
            'shift' => 'required|exists:shifts,nama'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Silahkan masukkan nama karyawan terlebih dahulu.',
            'nama.exists' => 'Nama karyawan tersebut tidak valid.',
            'tanggal_mulai.required' => 'Tanggal mulai jadwal karyawan tidak diperbolehkan kosong.',
            'tanggal_mulai.string' => 'Tanggal mulai jadwal karyawan wajib berisi tanggal.',
            'tanggal_selesai.string' => 'Tanggal selesai jadwal karyawan wajib berisi tanggal.',
            'shift.required' => 'Silahkan masukkan shift jadwal karyawan terlebih dahulu.',
            'shift.exists' => 'Shift jadwal karyawan tersebut tidak valid.',
        ];
    }

    public function model(array $row)
    {
        $users = $this->User->where('nama', $row['nama'])->first();
        $shifts = $this->Shift->where('nama', $row['shift'])->first();

        return new Jadwal([
            'user_id' => $users->id,
            'tgl_mulai' => $row['tanggal_mulai'],
            'tgl_selesai' => $row['tanggal_selesai'],
            'shift_id' => $shifts->id,
        ]);
    }
}
