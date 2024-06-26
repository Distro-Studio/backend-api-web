<?php

namespace App\Imports\Pengaturan\Finance;

use App\Models\Ter;
use App\Models\Ptkp;
use App\Models\KategoriTer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TER21Import implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    // Relationship
    private $kategoriTER;
    private $PTKP;

    public function __construct()
    {
        $this->kategoriTER = KategoriTer::select('id', 'nama_kategori_ter')->get();
        $this->PTKP = Ptkp::select('id', 'kode_ptkp')->get();
    }

    public function rules(): array
    {
        return [
            'nama_kategori_ter' => 'required',
            'kode_ptkp' => 'required',
            'from_ter' => 'required|numeric',
            'to_ter' => 'required|numeric',
            'percentage_ter' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_kategori_ter.required' => 'Silahkan pilih kategori TER terlebih dahulu.',
            'kode_ptkp.required' => 'Silahkan pilih PTKP terlebih dahulu.',
            'from_ter.required' => 'Batas penghasilan awal tidak diperbolehkan kosong.',
            'from_ter.numeric' => 'Batas penghasilan awal tidak diperbolehkan mengandung huruf.',
            'to_ter.required' => 'Batas penghasilan akhir tidak diperbolehkan kosong.',
            'to_ter.numeric' => 'Batas penghasilan akhir tidak diperbolehkan mengandung huruf.',
            'percentage_ter.required' => 'Persentase TER tidak diperbolehkan kosong.',
            'percentage_ter.numeric' => 'Persentase TER tidak diperbolehkan mengandung huruf.',
        ];
    }

    public function model(array $row)
    {
        $kategoriTER = $this->kategoriTER->where('nama_kategori_ter', $row['nama_kategori_ter'])->first();
        $PTKP = $this->PTKP->where('kode_ptkp', $row['kode_ptkp'])->first();

        return new Ter([
            'kategori_ter_id' => $kategoriTER->id ?? NULL,
            'ptkp_id' => $PTKP->id ?? NULL,
            'from_ter' => $row['from_ter'],
            'to_ter' => $row['to_ter'],
            'percentage_ter' => $row['percentage_ter'],
        ]);
    }
}
