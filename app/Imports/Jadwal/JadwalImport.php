<?php

namespace App\Imports\Jadwal;

use App\Models\Jadwal;
use App\Models\User;
use App\Models\Shift;
use Carbon\Carbon;
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
            'nama' => 'required',
            'tanggal_mulai' => 'required|string',
            'tanggal_selesai' => 'string',
            'shift' => 'required'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Silahkan masukkan nama karyawan terlebih dahulu.',
            'tanggal_mulai.required' => 'Tanggal mulai jadwal karyawan tidak diperbolehkan kosong.',
            'tanggal_mulai.string' => 'Tanggal mulai jadwal karyawan wajib berisi tanggal.',
            'tanggal_selesai.string' => 'Tanggal selesai jadwal karyawan wajib berisi tanggal.',
            'shift.required' => 'Silahkan masukkan shift jadwal karyawan terlebih dahulu.'
        ];
    }

    public function model(array $row)
    {
        $users = $this->User->where('nama', $row['nama'])->first();
        if (!$users) {
            throw new \Exception("Karyawan '" . $row['nama'] . "' tidak ditemukan.");
        }

        $shifts = $this->Shift->where('nama', $row['shift'])->first();
        if (!$shifts) {
            throw new \Exception("Data shift '" . $row['shift'] . "' tidak ditemukan.");
        }

        $tgl_mulai = Carbon::createFromFormat('d-m-Y', $row['tanggal_mulai']);
        $tgl_selesai = Carbon::createFromFormat('d-m-Y', $row['tanggal_selesai']);
        $tgl_mulai_formatted = $tgl_mulai->format('Y-m-d');
        $tgl_selesai_formatted = $tgl_selesai->format('Y-m-d');

        return new Jadwal([
            'user_id' => $users->id,
            'tgl_mulai' => $tgl_mulai_formatted,
            'tgl_selesai' => $tgl_selesai_formatted,
            'shift_id' => $shifts->id,
        ]);
    }
}
