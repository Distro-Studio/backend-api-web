<?php

namespace App\Imports\Jadwal;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\UnitKerja;
use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class JadwalImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $DataKaryawan;
    private $UnitKerja;
    private $Shift;
    public function __construct()
    {
        $this->DataKaryawan = DataKaryawan::select('id', 'nik', 'user_id')->get();
        $this->UnitKerja = UnitKerja::select('id', 'nama_unit')->get();
        $this->Shift = Shift::select('id', 'unit_kerja_id', 'nama')->get();
    }

    public function rules(): array
    {
        return [
            'nomor_induk_karyawan' => 'required',
            'unit_kerja' => 'required',
            'tanggal_mulai' => 'required|string',
            'tanggal_selesai' => 'string',
            'shift' => 'required'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nomor_induk_karyawan.required' => 'Silahkan masukkan nomor induk karyawan karyawan terlebih dahulu.',
            'unit_kerja.required' => 'Silahkan masukkan unit kerja karyawan terlebih dahulu.',
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

        $unitKerja = $this->UnitKerja->where('nama_unit', $row['unit_kerja'])->first();
        if (!$unitKerja) {
            throw new \Exception("Unit kerja '" . $row['unit_kerja'] . "' tidak ditemukan.");
        }

        $shifts = $this->Shift
            ->where('nama', $row['shift'])
            ->where('unit_kerja_id', $unitKerja->id)
            ->first();
        if (!$shifts) {
            throw new \Exception("Shift '" . $row['shift'] . "' untuk unit kerja '" . $row['unit_kerja'] . "' tidak ditemukan.");
        }

        $tgl_mulai = Carbon::createFromFormat('d-m-Y', $row['tanggal_mulai'])->format('Y-m-d');
        $tgl_selesai = Carbon::createFromFormat('d-m-Y', $row['tanggal_selesai'])->format('Y-m-d');

        return new Jadwal([
            'user_id' => $dataKaryawan->user_id,
            'tgl_mulai' => $tgl_mulai,
            'tgl_selesai' => $tgl_selesai,
            'shift_id' => $shifts->id,
        ]);
    }
}
