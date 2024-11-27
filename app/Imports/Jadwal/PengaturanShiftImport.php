<?php

namespace App\Imports\Jadwal;

use App\Models\Shift;
use App\Models\UnitKerja;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PengaturanShiftImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $UnitKerja;

    public function __construct()
    {
        $this->UnitKerja = UnitKerja::select('id', 'nama_unit')->get();
    }

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'unit_kerja' => 'required',
            'jam_from' => 'required',
            'jam_to' => 'required',
        ];
    }

    public function model(array $row)
    {
        $unitKerja = $this->UnitKerja->where('id', $row['unit_kerja'])->first();
        if (!$unitKerja) {
            throw new \Exception("Unit kerja '" . $row['unit_kerja'] . "' tidak ditemukan.");
        }

        return new Shift([
            'nama' => $row['nama'],
            'unit_kerja_id' => $unitKerja->id,
            'jam_from' => $row['jam_from'],
            'jam_to' => $row['jam_to'],
        ]);
    }
}
