<?php

namespace App\Imports\Jadwal;

use App\Models\DataKaryawan;
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

    private $DataKaryawan;
    private $Shift;
    public function __construct()
    {
        $this->DataKaryawan = DataKaryawan::select('id', 'nik', 'user_id')->get();
        $this->Shift = Shift::select('id', 'nama')->get();
    }

    public function rules(): array
    {
        return [
            'nomor_induk_karyawan' => 'required',
            'tanggal_mulai' => 'required|string',
            'tanggal_selesai' => 'string',
            'shift' => 'required'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nomor_induk_karyawan.required' => 'Silahkan masukkan nomor induk karyawan karyawan terlebih dahulu.',
            'tanggal_mulai.required' => 'Tanggal mulai jadwal karyawan tidak diperbolehkan kosong.',
            'tanggal_mulai.string' => 'Tanggal mulai jadwal karyawan wajib berisi tanggal.',
            'tanggal_selesai.string' => 'Tanggal selesai jadwal karyawan wajib berisi tanggal.',
            'shift.required' => 'Silahkan masukkan shift jadwal karyawan terlebih dahulu.'
        ];
    }

    public function model(array $row)
    {
        $dataKaryawan = $this->DataKaryawan->where('nik', $row['nomor_induk_karyawan'])->first();
        if (!$dataKaryawan) {
            throw new \Exception("Karyawan dengan NIK '" . $row['nomor_induk_karyawan'] . "' tidak ditemukan.");
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
            'user_id' => $dataKaryawan->user_id,
            'tgl_mulai' => $tgl_mulai_formatted,
            'tgl_selesai' => $tgl_selesai_formatted,
            'shift_id' => $shifts->id,
        ]);
    }
}
